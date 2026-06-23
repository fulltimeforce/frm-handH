<?php
/**
 * Admin Vehicles Prev/Next (theme include)
 */

if (!defined('ABSPATH')) exit;

final class HNH_Vehicles_Admin_Nav
{
    private const CPT = 'vehicles';
    private const USER_META_KEY = '_hnh_vehicles_admin_list_url';
    private const CTX_PARAM = 'vehicles_ctx';

    public static function boot(): void
    {
        // Captura contexto del listado (seguro, siempre corre)
        add_action('load-edit.php', [__CLASS__, 'capture_from_list']);

        // Captura contexto cuando navegas con botones
        add_action('load-post.php', [__CLASS__, 'capture_from_ctx_param']);

        // Render (compatible con Classic + Block editor)
        add_action('admin_notices', [__CLASS__, 'render_bar']);

        // CSS
        add_action('admin_head', [__CLASS__, 'css']);
    }

    public static function capture_from_list(): void
    {
        if (!is_user_logged_in()) return;

        $post_type = isset($_GET['post_type']) ? sanitize_key($_GET['post_type']) : '';
        if ($post_type !== self::CPT) return;

        $url = self::current_url();
        if ($url) {
            update_user_meta(get_current_user_id(), self::USER_META_KEY, esc_url_raw($url));
        }
    }

    public static function capture_from_ctx_param(): void
    {
        if (!is_user_logged_in()) return;

        if (empty($_GET[self::CTX_PARAM])) return;

        $decoded = base64_decode((string) sanitize_text_field($_GET[self::CTX_PARAM]), true);
        if (!$decoded) return;

        if (self::is_vehicles_list_url($decoded)) {
            update_user_meta(get_current_user_id(), self::USER_META_KEY, esc_url_raw($decoded));
        }
    }

    private static function get_ids_from_list_url(string $list_url): array
    {
        $q = [];
        parse_str((string) wp_parse_url($list_url, PHP_URL_QUERY), $q);

        $args = [
            'post_type'      => self::CPT,
            'fields'         => 'ids',
            'posts_per_page' => -1,
            'no_found_rows'  => true,
            'post_status'    => (!empty($q['post_status']) && $q['post_status'] !== 'all') ? sanitize_key($q['post_status']) : 'any',
        ];

        if (!empty($q['s'])) {
            $args['s'] = sanitize_text_field(wp_unslash($q['s']));
        }

        if (!empty($q['orderby'])) {
            $args['orderby'] = sanitize_key($q['orderby']);
        }
        if (!empty($q['order'])) {
            $order = strtoupper(sanitize_key($q['order']));
            $args['order'] = in_array($order, ['ASC','DESC'], true) ? $order : 'DESC';
        }

        // Tus filtros típicos (ajusta keys si cambian)
        $meta_query = [];

        if (!empty($q['contact_rep_filter']) && is_numeric($q['contact_rep_filter'])) {
            $meta_query[] = [
                'key'     => 'contact_rep',
                'value'   => (int) $q['contact_rep_filter'],
                'compare' => '=',
                'type'    => 'NUMERIC',
            ];
        }

        if (!empty($q['auction_sale_filter'])) {
            $val = is_numeric($q['auction_sale_filter']) ? (int)$q['auction_sale_filter'] : sanitize_text_field(wp_unslash($q['auction_sale_filter']));
            $meta_query[] = [
                'key'     => 'auction_number_latest',
                'value'   => $val,
                'compare' => '=',
            ];
        }

        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }

        $args = apply_filters('hnh_vehicles_admin_nav_query_args', $args, $q, $list_url);

        $wpq = new WP_Query($args);
        return array_map('intval', (array) $wpq->posts);
    }

    private static function is_vehicles_list_url(string $url): bool
    {
        return (bool) (str_contains($url, 'edit.php') && str_contains($url, 'post_type=' . self::CPT));
    }

    private static function current_url(): string
    {
        $scheme = is_ssl() ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $uri  = $_SERVER['REQUEST_URI'] ?? '';
        return ($host && $uri) ? $scheme . $host . $uri : '';
    }

    public static function render_bar(): void
{
    if (!is_admin()) return;

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen) return;

    // Solo en editor de vehicles
    if ($screen->base !== 'post') return;
    if ($screen->post_type !== self::CPT) return;

    $post_id = isset($_GET['post']) ? (int) $_GET['post'] : 0;
    if ($post_id <= 0) return;

    $list_url = (string) get_user_meta(get_current_user_id(), self::USER_META_KEY, true);

    // Wrapper normal (NO notice)
    echo '<div class="hnh-vehicles-admin-nav">';

    if (!$list_url || !self::is_vehicles_list_url($list_url)) {
        echo '<div class="hnh-vehicles-admin-nav__left">';
        //echo '<span class="hnh-vehicles-admin-nav__hint">Entra primero a <code>Vehicles → All Vehicles</code> y abre un vehicle desde ahí.</span>';
        echo '</div>';
        echo '</div>';
        return;
    }

    $ids = self::get_ids_from_list_url($list_url);

    if (empty($ids)) {
        echo '<div class="hnh-vehicles-admin-nav__left">';
        //echo '<span class="hnh-vehicles-admin-nav__hint">No pude obtener IDs desde el contexto (query vacía o filtros).</span>';
        echo '</div>';
        echo '</div>';
        return;
    }

    $index = array_search($post_id, $ids, true);

    if ($index === false) {
        echo '<div class="hnh-vehicles-admin-nav__left">';
        //echo '<span class="hnh-vehicles-admin-nav__hint">Este vehicle no está dentro del listado filtrado actual.</span>';
        echo '</div>';

        echo '</div>';
        return;
    }

    $prev_id = $ids[$index - 1] ?? 0;
    $next_id = $ids[$index + 1] ?? 0;

    $ctx = base64_encode($list_url);

    $prev_link = $prev_id
        ? admin_url('post.php?post=' . (int)$prev_id . '&action=edit&' . self::CTX_PARAM . '=' . rawurlencode($ctx))
        : '';

    $next_link = $next_id
        ? admin_url('post.php?post=' . (int)$next_id . '&action=edit&' . self::CTX_PARAM . '=' . rawurlencode($ctx))
        : '';

    echo '<div class="hnh-vehicles-admin-nav__left">';

    // ❌ Texto de posición (comentado por pedido)
    // echo '<span class="hnh-vehicles-admin-nav__pos">Vehicles Nav: Posición <strong>' . ($index + 1) . '</strong> de <strong>' . count($ids) . '</strong></span>';

    if ($prev_link) {
        echo '<a class="button" href="' . esc_url($prev_link) . '">← Previous</a>';
    } else {
        echo '<span class="button disabled" aria-disabled="true">← Previous</span>';
    }

    if ($next_link) {
        echo '<a class="button button-primary" href="' . esc_url($next_link) . '">Next →</a>';
    } else {
        echo '<span class="button button-primary disabled" aria-disabled="true">Next →</span>';
    }

    echo '</div>'; // left

    echo '</div>'; // wrapper
}

public static function css(): void
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->base !== 'post' || $screen->post_type !== self::CPT) return;

    echo '<style>
        .hnh-vehicles-admin-nav{
			position:fixed;
			bottom:0;
			left: 50%;
			z-index:10000;
			transform: translateX(-50%);
            display:flex;
            align-items:center;
            justify-content:center;
            gap:10px;
            padding:16px;
            margin:0;
            background:#fff;
            flex-wrap:wrap;
        }
        .hnh-vehicles-admin-nav__left{
            display:flex;
            align-items:center;
            gap:8px;
            flex-wrap:wrap;
        }
        .hnh-vehicles-admin-nav__hint{
            opacity:.85;
        }
        .hnh-vehicles-admin-nav .button.disabled{
            pointer-events:none;
            opacity:.45;
        }
    </style>';
}
}

HNH_Vehicles_Admin_Nav::boot();
