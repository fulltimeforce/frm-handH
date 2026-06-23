<?php
/**
 * vehicles-search-sync.php
 *
 * Sincroniza wp_{prefix}vehicles_search cuando se crea/edita/elimina un CPT vehicles.
 * - Upsert por vehicle_id (RECOMENDADO: unique index en vehicle_id)
 * - Soft delete al mover a la papelera (is_deleted=1)
 * - Hard delete al borrado definitivo
 *
 * FIX IMPORTANTE:
 * - Al crear un post, WP primero lo guarda como auto-draft => NO sincronizamos ahí.
 * - Cuando cambia a draft/publish, usamos transition_post_status para forzar el sync.
 */

if (!defined('ABSPATH')) exit;

final class Vehicles_Search_Sync
{
    private const CPT = 'vehicles';

    /** Evita doble sync en el mismo request */
    private static array $synced = [];

    public static function init(): void
    {
        // 1) Guardado normal (título, quick edit, etc.)
        add_action('save_post', [__CLASS__, 'on_save_post'], 100, 3);

        // 2) Guardado ACF (meta ya está lista aquí)
        if (function_exists('get_field')) {
            add_action('acf/save_post', [__CLASS__, 'on_acf_save_post'], 30);
        }

        // 3) Cambios de estado (CLAVE para create/publish)
        add_action('transition_post_status', [__CLASS__, 'on_transition_post_status'], 10, 3);

        // 4) Trash / Untrash / Delete definitivo
        add_action('wp_trash_post', [__CLASS__, 'on_trash_post'], 10, 1);
        add_action('untrash_post', [__CLASS__, 'on_untrash_post'], 10, 1);
        add_action('before_delete_post', [__CLASS__, 'on_before_delete_post'], 10, 1);

        // 5) Rebuild manual (opcional): /wp-admin/edit.php?post_type=vehicles&vs_rebuild=1&_wpnonce=...
        add_action('admin_init', [__CLASS__, 'maybe_rebuild_all']);
    }

    // =========================================================
    // Hooks
    // =========================================================

    public static function on_save_post(int $post_id, \WP_Post $post, bool $update): void
    {
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) return;
        if ($post->post_type !== self::CPT) return;

        // IMPORTANTÍSIMO: no sync en auto-draft (fase inicial al crear)
        if ($post->post_status === 'auto-draft') return;

        // Si está en trash, marcamos borrado
        if ($post->post_status === 'trash') {
            self::soft_delete($post_id, 1);
            return;
        }

        self::sync_vehicle($post_id, 'save_post');
    }

    public static function on_acf_save_post($post_id): void
    {
        if (!is_numeric($post_id)) return;

        $post_id = (int) $post_id;
        $post = get_post($post_id);
        if (!$post || $post->post_type !== self::CPT) return;

        // Si aún está en auto-draft, no hay nada que sincronizar
        if ($post->post_status === 'auto-draft') return;

        self::sync_vehicle($post_id, 'acf/save_post');
    }

    public static function on_transition_post_status(string $new_status, string $old_status, \WP_Post $post): void
    {
        if ($post->post_type !== self::CPT) return;

        // Cuando creas, normalmente pasa: auto-draft -> draft/publish
        // Aquí forzamos sync cuando sale de auto-draft o cuando cambia de estado.
        if ($new_status === $old_status) return;

        // Si entra a trash, lo manejamos con wp_trash_post también,
        // pero igual dejamos esto por seguridad.
        if ($new_status === 'trash') {
            self::soft_delete((int)$post->ID, 1);
            return;
        }

        // Si sale de trash
        if ($old_status === 'trash' && $new_status !== 'trash') {
            self::soft_delete((int)$post->ID, 0);
        }

        // Evitar que el flag de "synced" bloquee el sync del publish inicial
        unset(self::$synced[(int)$post->ID]);

        // Si sale de auto-draft o cambia a un estado "real", sincronizamos
        if ($new_status !== 'auto-draft') {
            self::sync_vehicle((int)$post->ID, 'transition_post_status');
        }
    }

    public static function on_trash_post(int $post_id): void
    {
        $post = get_post($post_id);
        if (!$post || $post->post_type !== self::CPT) return;

        self::soft_delete($post_id, 1);
    }

    public static function on_untrash_post(int $post_id): void
    {
        $post = get_post($post_id);
        if (!$post || $post->post_type !== self::CPT) return;

        self::soft_delete($post_id, 0);
        unset(self::$synced[$post_id]);
        self::sync_vehicle($post_id, 'untrash_post');
    }

    public static function on_before_delete_post(int $post_id): void
    {
        $post = get_post($post_id);
        if (!$post || $post->post_type !== self::CPT) return;

        self::hard_delete($post_id);
    }

    /**
     * Fuerza re-sincronización (p. ej. tras import CSV donde ACF se guarda después de save_post).
     */
    public static function force_sync(int $vehicle_id, string $source = 'force'): void
    {
        unset(self::$synced[$vehicle_id]);
        self::sync_vehicle($vehicle_id, $source);
    }

    // =========================================================
    // Core sync
    // =========================================================

    private static function sync_vehicle(int $vehicle_id, string $source = ''): void
    {
        if (isset(self::$synced[$vehicle_id])) {
            return;
        }
        self::$synced[$vehicle_id] = true;

        global $wpdb;

        $post = get_post($vehicle_id);
        if (!$post || $post->post_type !== self::CPT) return;

        // No sync en auto-draft
        if ($post->post_status === 'auto-draft') return;

        // Repo
        if (!class_exists('VehiclesSearchRepository')) {
            $repoPath = get_template_directory() . '/VehiclesSearchRepository.php';
            if (file_exists($repoPath)) require_once $repoPath;
        }

        $table = $wpdb->prefix . 'vehicles_search';
        if (class_exists('VehiclesSearchRepository')) {
            try {
                $repo  = new VehiclesSearchRepository($wpdb);
                $table = $repo->table();
            } catch (\Throwable $e) {}
        }

        // ===== Valores =====
        $post_title = (string) get_the_title($vehicle_id);

        // post_content => ACF description
        $description  = self::get_acf_value('description', $vehicle_id);
        $post_content = is_string($description) ? $description : '';

        // status (ACF)
        $status = self::get_acf_value('status', $vehicle_id);
        $status = is_string($status) ? $status : (is_numeric($status) ? (string)$status : '');
		
		// price = ACF estimate_low
		$estimate_low = self::get_acf_value('estimate_low', $vehicle_id);

		// Puede venir como string "12345" o float/int. Lo guardamos como string para varchar.
		$price = '';
		if (is_numeric($estimate_low)) {
    		$price = (string) $estimate_low;
		} elseif (is_string($estimate_low)) {
    		$price = trim($estimate_low);
		}

        // specialist = assigned_to primero, si no contact_rep
        $assigned_to = self::get_acf_value_raw('assigned_to', $vehicle_id);
        $contact_rep = self::get_acf_value_raw('contact_rep', $vehicle_id);

        $specialist = self::normalize_user_id($assigned_to);
        if (!$specialist) $specialist = self::normalize_user_id($contact_rep);
		
		// make_id = artist_maker_brand (Post Object)
		$make_raw = self::get_acf_value_raw('artist_maker_brand', $vehicle_id);
		$make_id  = self::normalize_post_id($make_raw);

        // model_id = artist_maker_brand (Post Object)
		$model_raw = self::get_acf_value_raw('model_vehicle', $vehicle_id);
		$model_id  = self::normalize_post_id($model_raw);

        // auction_id = Post Object => ID del auction
        $auction_id_raw = self::get_acf_value_raw('auction_number_latest', $vehicle_id);
        $auction_id = self::normalize_post_id($auction_id_raw);

        // lot_number_latest
        $lot_number = self::get_acf_value('lot_number_latest', $vehicle_id);
        $lot_number = is_string($lot_number) ? $lot_number : (is_numeric($lot_number) ? (string)$lot_number : '');

        // auction_date = get_field('auction_date') del Auction
        $auction_date = '';
        if ($auction_id) {
            $ad = self::get_acf_value('auction_date', $auction_id);
            $auction_date = is_string($ad) ? $ad : '';
        }

        // vehicle_type (default 'auction' si ACF no tiene valor)
        $vehicle_type = self::get_acf_value('type_of_vehicle', $vehicle_id);
        $vehicle_type = is_string($vehicle_type) ? trim($vehicle_type) : '';
        if ($vehicle_type === '') {
            $vehicle_type = 'auction';
        }

        // zero_photos = (gallery_vehicle + featured) <= 1 => true
        $gallery = self::get_acf_value_raw('gallery_vehicle', $vehicle_id);
        $gallery_count = is_array($gallery) ? count($gallery) : 0;

        $has_featured = (int) get_post_thumbnail_id($vehicle_id) > 0 ? 1 : 0;
        $total_images = $gallery_count + $has_featured;

        $zero_photos = ($total_images <= 1) ? 1 : 0;

        // auctionless
        $auctionless = ($auction_id ? 0 : 1);

        // is_deleted
        $is_deleted = ($post->post_status === 'trash') ? 1 : 0;

        $now = current_time('mysql');

        // ===== Upsert =====
        $exists = (int) $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE vehicle_id = %d", $vehicle_id)
        ) > 0;

        $data = [
    		'vehicle_id'    => $vehicle_id,
    		'post_title'    => $post_title,
    		'post_content'  => $post_content,

    		'price'         => $price !== '' ? $price : null,
    		'status'        => $status !== '' ? $status : null,
    		'specialist_id' => $specialist ? (int)$specialist : null,
    		'model_id'      => $model_id ? (int)$model_id : null,
    		'make_id'       => $make_id ? (int)$make_id : null,

    		'auction_id'    => $auction_id ? (int)$auction_id : null,
    		'lot_number'    => $lot_number !== '' ? $lot_number : null,
    		'auction_date'  => $auction_date !== '' ? $auction_date : null,
    		'vehicle_type'  => $vehicle_type,

    		'zero_photos'   => $zero_photos,
    		'auctionless'   => $auctionless,
    		'is_deleted'    => $is_deleted,
    		'updated_at'    => $now,
		];

        if (!$exists) {
            $data['created_at'] = $now;
        }

        // UPDATE (si existe)
        if ($exists) {
            $ok = $wpdb->update(
                $table,
                $data,
                ['vehicle_id' => $vehicle_id]
            );

            if ($ok === false) {
                error_log('[vehicles_search] UPDATE failed: ' . $wpdb->last_error . " | source={$source} | vehicle_id={$vehicle_id}");
            } else {
                self::sync_categories($vehicle_id);
            }
            return;
        }

        // INSERT (si no existe)
        $ok = $wpdb->insert($table, $data, null);
        if ($ok === false) {
            error_log('[vehicles_search] INSERT failed: ' . $wpdb->last_error . " | source={$source} | vehicle_id={$vehicle_id}");
        } else {
            self::sync_categories($vehicle_id);
        }
    }
	
	    /**
     * Sincroniza categorías del vehicle en la tabla wp_{prefix}vehicles_category
     * Estructura esperada:
     *  - vehicle_id (int)
     *  - category_id (int)  // term_id de vehicle_category
     */
    private static function sync_categories(int $vehicle_id): void
    {
        global $wpdb;

        $tableCat = $wpdb->prefix . 'vehicles_category';

        // Traer term_ids actuales del vehicle (taxonomy vehicle_category)
        $term_ids = wp_get_object_terms($vehicle_id, 'vehicle_category', [
            'fields' => 'ids',
        ]);

        if (is_wp_error($term_ids)) {
            error_log('[vehicles_category] wp_get_object_terms error: ' . $term_ids->get_error_message() . " | vehicle_id={$vehicle_id}");
            return;
        }

        $term_ids = array_values(array_unique(array_map('intval', (array) $term_ids)));

        // 1) Borrar lo anterior
        $del = $wpdb->delete($tableCat, ['vehicle_id' => $vehicle_id], ['%d']);
        if ($del === false) {
            error_log('[vehicles_category] DELETE failed: ' . $wpdb->last_error . " | vehicle_id={$vehicle_id}");
            // seguimos igual, para intentar insertar
        }

        // 2) Insertar lo nuevo (si hay)
        if (empty($term_ids)) {
            return;
        }

        // Insert masivo
        $values = [];
        $placeholders = [];
        foreach ($term_ids as $tid) {
            $placeholders[] = "(%d,%d)";
            $values[] = $vehicle_id;
            $values[] = $tid;
        }

        $sql = "INSERT INTO {$tableCat} (vehicle_id, category_id) VALUES " . implode(',', $placeholders);
        $ok = $wpdb->query($wpdb->prepare($sql, $values));

        if ($ok === false) {
            error_log('[vehicles_category] INSERT failed: ' . $wpdb->last_error . " | vehicle_id={$vehicle_id}");
        }
    }

    /**
     * Limpia categorías (útil para hard delete o si quieres al trash).
     */
    private static function delete_categories(int $vehicle_id): void
    {
        global $wpdb;

        $tableCat = $wpdb->prefix . 'vehicles_category';
        $ok = $wpdb->delete($tableCat, ['vehicle_id' => $vehicle_id], ['%d']);

        if ($ok === false) {
            error_log('[vehicles_category] DELETE failed: ' . $wpdb->last_error . " | vehicle_id={$vehicle_id}");
        }
    }

    private static function soft_delete(int $vehicle_id, int $is_deleted): void
    {
        global $wpdb;

        $table = $wpdb->prefix . 'vehicles_search';

        if (!class_exists('VehiclesSearchRepository')) {
            $repoPath = get_template_directory() . '/VehiclesSearchRepository.php';
            if (file_exists($repoPath)) require_once $repoPath;
        }
        if (class_exists('VehiclesSearchRepository')) {
            try {
                $repo  = new VehiclesSearchRepository($wpdb);
                $table = $repo->table();
            } catch (\Throwable $e) {}
        }

        $now = current_time('mysql');

        $ok = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$table} SET is_deleted = %d, updated_at = %s WHERE vehicle_id = %d",
                $is_deleted,
                $now,
                $vehicle_id
            )
        );

        if ($ok === false) {
            error_log('[vehicles_search] SOFT DELETE failed: ' . $wpdb->last_error . " | vehicle_id={$vehicle_id}");
        }
    }

    private static function hard_delete(int $vehicle_id): void
    {
        global $wpdb;

        $table = $wpdb->prefix . 'vehicles_search';

        if (!class_exists('VehiclesSearchRepository')) {
            $repoPath = get_template_directory() . '/VehiclesSearchRepository.php';
            if (file_exists($repoPath)) require_once $repoPath;
        }
        if (class_exists('VehiclesSearchRepository')) {
            try {
                $repo  = new VehiclesSearchRepository($wpdb);
                $table = $repo->table();
            } catch (\Throwable $e) {}
        }

        $ok = $wpdb->query(
            $wpdb->prepare("DELETE FROM {$table} WHERE vehicle_id = %d", $vehicle_id)
        );

        if ($ok === false) {
            error_log('[vehicles_search] HARD DELETE failed: ' . $wpdb->last_error . " | vehicle_id={$vehicle_id}");
        }
		
		self::delete_categories($vehicle_id);
    }

    // =========================================================
    // Helpers ACF
    // =========================================================

    private static function get_acf_value(string $field, int $post_id)
    {
        if (!function_exists('get_field')) return '';
        return get_field($field, $post_id);
    }

    private static function get_acf_value_raw(string $field, int $post_id)
    {
        if (!function_exists('get_field')) return null;
        return get_field($field, $post_id, false);
    }

    private static function normalize_user_id($value): int
    {
        if (empty($value)) return 0;

        if (is_object($value) && isset($value->ID)) return (int) $value->ID;

        if (is_array($value)) {
            if (isset($value['ID'])) return (int) $value['ID'];
            if (isset($value['id'])) return (int) $value['id'];
        }

        if (is_numeric($value)) return (int) $value;

        return 0;
    }

    private static function normalize_post_id($value): int
    {
        if (empty($value)) return 0;

        if (is_object($value) && isset($value->ID)) return (int) $value->ID;

        if (is_array($value)) {
            if (isset($value['ID'])) return (int) $value['ID'];
            if (isset($value['id'])) return (int) $value['id'];
        }

        if (is_numeric($value)) return (int) $value;

        return 0;
    }

    // =========================================================
    // Rebuild (opcional)
    // =========================================================

    public static function maybe_rebuild_all(): void
    {
        if (!is_admin()) return;
        if (!current_user_can('manage_options')) return;

        if (empty($_GET['post_type']) || $_GET['post_type'] !== self::CPT) return;
        if (empty($_GET['vs_rebuild'])) return;

        check_admin_referer('vs_rebuild');

        $ids = get_posts([
            'post_type'      => self::CPT,
            'post_status'    => ['publish','draft','pending','future','private','acf-disabled','trash'],
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ]);

        foreach ($ids as $id) {
            unset(self::$synced[(int)$id]);
            self::sync_vehicle((int)$id, 'rebuild');
        }

        wp_safe_redirect(remove_query_arg(['vs_rebuild','_wpnonce']));
        exit;
    }
}

Vehicles_Search_Sync::init();