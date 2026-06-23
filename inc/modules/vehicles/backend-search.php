<?php
/**
 * backend-search.php
 *
 * Reemplaza el buscador del admin (listado) del CPT "vehicles"
 * para que use la tabla wp_vehicles_search (SQL) en vez del search nativo de WP.
 *
 * Busca en:
 *  - wp_vehicles_search.post_title
 *  - wp_vehicles_search.post_content
 *  - wp_vehicles_search.lot_number
 *
 * Requisitos:
 *  - wp_vehicles_search(vehicle_id, post_title, post_content, lot_number, is_deleted, ...)
 *
 * Cómo usar:
 *  - Inclúyelo desde functions.php o un mu-plugin:
 *      require_once get_template_directory() . '/backend-search.php';
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('pre_get_posts', function (WP_Query $q) {

    // Solo admin, query principal, pantalla "All Vehicles"
    if (!is_admin() || !$q->is_main_query()) {
        return;
    }

    // En listado del CPT vehicles: /wp-admin/edit.php?post_type=vehicles
    $post_type = $q->get('post_type');
    if ($post_type !== 'vehicles') {
        return;
    }

    // Solo cuando se está usando el buscador (param s)
    $s = $q->get('s');
    $s = is_string($s) ? trim($s) : '';
    if ($s === '') {
        return;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'vehicles_search';

    // LIKE seguro
    $like = '%' . $wpdb->esc_like($s) . '%';

    /**
     * Importante:
     * - NO limitamos aquí por página, porque WP necesita el total para paginar.
     * - En búsquedas normales el set de IDs suele ser manejable.
     * - Si te preocupa que alguien busque "a" y salgan miles:
     *   puedes activar un límite duro (ver bloque opcional más abajo).
     */
    $sql = "
        SELECT s.vehicle_id
        FROM {$table} s
        WHERE s.is_deleted = 0
          AND (
                s.post_title   LIKE %s
             OR s.post_content LIKE %s
             OR s.lot_number   LIKE %s
          )
    ";

    $ids = $wpdb->get_col($wpdb->prepare($sql, $like, $like, $like));
    $ids = array_values(array_unique(array_map('intval', (array) $ids)));

    // Si no hay resultados, fuerza vacío.
    if (empty($ids)) {
        $q->set('post__in', [0]);
        $q->set('s', '');
        add_filter('posts_search', '__return_empty_string', 20);
        return;
    }

    /**
     * (Opcional) Límite duro para evitar IN gigantes si alguien busca algo muy genérico.
     * Descomenta si lo necesitas.
     */
    /*
    $max_ids = 3000; // ajusta a tu gusto
    if (count($ids) > $max_ids) {
        $ids = array_slice($ids, 0, $max_ids);
    }
    */

    // Inyectamos el set de IDs y anulamos el search nativo de WP
    $q->set('post__in', $ids);
    $q->set('orderby', 'post__in'); // preserva el orden (si luego quieres otro, lo cambiamos)
    $q->set('s', '');               // desactiva el search default

    // Evita que WP agregue su "AND (posts.post_title LIKE ...)" etc.
    add_filter('posts_search', '__return_empty_string', 20);

}, 20);