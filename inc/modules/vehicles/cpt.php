<?php

/**
 * Vehicles CPT + taxonomies
 */

// ===== Custom Post Type: Vehicles =====
if (! function_exists('cpt_vehicles_init')) {
    function cpt_vehicles_init()
    {
        $labels = array(
            'name'                  => 'Vehicles',
            'singular_name'         => 'Vehicle',
            'menu_name'             => 'Vehicles',
            'name_admin_bar'        => 'Vehicle',
            'add_new'               => 'Add New',
            'add_new_item'          => 'Add New Vehicle',
            'new_item'              => 'New Vehicle',
            'edit_item'             => 'Edit Vehicle',
            'view_item'             => 'View Vehicle',
            'all_items'             => 'All Vehicles',
            'search_items'          => 'Search Vehicles',
            'parent_item_colon'     => 'Parent Vehicles:',
            'not_found'             => 'No vehicles found.',
            'not_found_in_trash'    => 'No vehicles found in Trash.',
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'vehicles', 'with_front' => false),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-car',
            // 'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
            'supports'           => array('title', 'thumbnail', 'excerpt', 'custom-fields', 'page-attributes'),
            'show_in_rest'       => true,
            'show_in_export'     => false,
            'taxonomies'         => array('vehicle_category', 'vehicle_brand'),
        );

        register_post_type('vehicles', $args);
    }
}
add_action('init', 'cpt_vehicles_init', 0);

// ===== Vehicle Categories (hierarchical) =====
if (! function_exists('vehicles_register_tax_cat')) {
    function vehicles_register_tax_cat()
    {
        $labels = array(
            'name'              => 'Vehicle Categories',
            'singular_name'     => 'Vehicle Category',
            'search_items'      => 'Search Vehicle Categories',
            'all_items'         => 'All Vehicle Categories',
            'parent_item'       => 'Parent Category',
            'parent_item_colon' => 'Parent Category:',
            'edit_item'         => 'Edit Vehicle Category',
            'update_item'       => 'Update Vehicle Category',
            'add_new_item'      => 'Add New Vehicle Category',
            'new_item_name'     => 'New Vehicle Category Name',
            'menu_name'         => 'Vehicle Categories',
        );

        register_taxonomy('vehicle_category', array('vehicles'), array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'vehicle-category', 'with_front' => false),
            'show_in_rest'      => true,
        ));
    }
}
add_action('init', 'vehicles_register_tax_cat', 1);

// ------------------------------------------------------------

/**
 * Columna "Status" para CPT vehicles (ACF: status)
 */

// 1) Añadir columna y colocarla después del título
add_filter('manage_edit-vehicles_columns', function ($columns) {
    $new = [];
    foreach ($columns as $key => $label) {
        $new[$key] = $label;
        if ($key === 'title') {
            $new['vehicle_status'] = __('Status', 'text-domain');
        }
    }
    // Si por algo no existiera 'title', asegura la columna
    if (!isset($new['vehicle_status'])) {
        $new['vehicle_status'] = __('Status', 'text-domain');
    }
    return $new;
});

// 2) Render del contenido de la columna
add_action('manage_vehicles_posts_custom_column', function ($column, $post_id) {
    if ($column === 'vehicle_status') {
        $status = get_field('status', $post_id); // ACF
        if (is_array($status)) {
            // por si es campo select/checkbox múltiple
            $status = implode(', ', array_filter($status));
        }
        echo esc_html($status ?: '—');
    }
}, 10, 2);

// 3) (Opcional) Hacer la columna ordenable
add_filter('manage_edit-vehicles_sortable_columns', function ($columns) {
    $columns['vehicle_status'] = 'vehicle_status';
    $columns['lot_number']     = 'lot_number';
    return $columns;
});

add_action('pre_get_posts', function ($query) {
    if (!is_admin() || !$query->is_main_query()) return;
    if ($query->get('orderby') === 'vehicle_status') {
        // El campo ACF se guarda como meta_key 'status'
        $query->set('meta_key', 'status');
        $query->set('orderby', 'meta_value'); // usa meta_value_num si es numérico
    }
    if ($query->get('orderby') === 'lot_number') {
        $query->set('meta_key', 'lot_number_latest');
        $query->set('orderby', 'meta_value_num');
    }
});

// 4) (Opcional) Ajustar ancho en admin
add_action('admin_head', function () {
    echo '<style>.column-vehicle_status{width:240px}</style>';
});

// ------------------------------------------------------------

/**
 * Tabs / views usando SQL (wp_vehicles_search)
 */
add_filter('views_edit-vehicles', function ($views) {

    if (isset($views['mine'])) unset($views['mine']);
    if (!class_exists('VehiclesSearchRepository')) return $views;

    global $wpdb;
    $repo  = new VehiclesSearchRepository($wpdb);
    $vs    = $repo->table();
    $posts = $wpdb->posts;

    $allowed_statuses = ['publish', 'draft', 'pending', 'future', 'private', 'acf-disabled'];
    $ph = implode(',', array_fill(0, count($allowed_statuses), '%s'));

    $make_view_sql = function (string $key, string $label, string $extraWhereSql, array $extraParams = []) use ($wpdb, $vs, $posts, $allowed_statuses, $ph) {

        // ✅ Preservar filtros (selects) actuales
        $args = [
            'post_type' => 'vehicles',
        ];

        if (!empty($_GET['vehicle_status_filter'])) {
            $args['vehicle_status_filter'] = sanitize_text_field((string) $_GET['vehicle_status_filter']);
        }
        if (!empty($_GET['contact_rep_filter'])) {
            $args['contact_rep_filter'] = (int) $_GET['contact_rep_filter'];
        }
        if (!empty($_GET['auction_sale_filter'])) {
            $args['auction_sale_filter'] = (int) $_GET['auction_sale_filter'];
        }

        // ✅ Entre tabs solo puede haber 1: limpiamos los otros y dejamos solo el actual
        unset($args['private_sales'], $args['single_zero_photos'], $args['auctionless']);
        $args[$key] = 1;

        $sql = "
            SELECT COUNT(DISTINCT p.ID)
            FROM {$posts} p
            INNER JOIN {$vs} s ON s.vehicle_id = p.ID
            WHERE p.post_type = 'vehicles'
              AND p.post_status IN ($ph)
			  AND s.is_deleted = 0
              $extraWhereSql
        ";

        $params = array_merge($allowed_statuses, $extraParams);
        $count  = (int) $wpdb->get_var($wpdb->prepare($sql, $params));

        $url   = add_query_arg($args, admin_url('edit.php'));
        $class = !empty($_GET[$key]) ? 'class="current" aria-current="page"' : '';

        return sprintf(
            '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
            esc_url($url),
            $class,
            esc_html($label),
            $count
        );
    };

    $views['private_sales']       = $make_view_sql('private_sales', 'Private Sales', " AND s.vehicle_type = %s ", ['private-sale']);
   	$views['single_zero_photos']  = $make_view_sql('single_zero_photos', 'Single/Zero Photos', " AND s.zero_photos = %d ", [1]);
	$views['auctionless']         = $make_view_sql('auctionless', 'Auctionless', " AND s.auctionless = %d ", [1]);

    return $views;
});

/**
 * Para que tus tabs incluyan privados como antes (si quieres).
 * OJO: esto afecta todo el listado de vehicles en admin.
 */
add_action('pre_get_posts', function (WP_Query $query) {
    if (!is_admin() || !$query->is_main_query()) return;
    if ($query->get('post_type') !== 'vehicles') return;

    if (!hnh_vs_active_tab()) return;

    $query->set('post_status', ['publish', 'draft', 'pending', 'future', 'private', 'acf-disabled']);
});

function hnh_vs_active_tab(): ?string
{
    if (!empty($_GET['private_sales'])) return 'private_sales';
    if (!empty($_GET['single_zero_photos'])) return 'single_zero_photos';
    if (!empty($_GET['auctionless'])) return 'auctionless';
    return null;
}

// ------------------------------------------------------------

add_action('restrict_manage_posts', function ($post_type) {
    if ($post_type !== 'vehicles') return;

    // si hay tab activo, lo preservamos en el submit del form (Filter)
    $active_tab = hnh_vs_active_tab();
    if (!$active_tab) return;

    printf(
        '<input type="hidden" name="%s" value="1" />',
        esc_attr($active_tab)
    );
});

add_action('restrict_manage_posts', function ($post_type) {
    if ($post_type !== 'vehicles') {
        return;
    }

    $options = [
        ''               => __('All Statuses', 'text-domain'),
        'Allocated'      => 'Allocated',
        'Back to vendor' => 'Back to vendor',
        'Back to vendor (awaiting collection)' => 'Back to vendor (awaiting collection)',
        'Sold'           => 'Sold',
        'Appraisal'      => 'Appraisal',
        'Available'      => 'Available',
        'Merged'         => 'Merged',
        'Split'          => 'Split',
    ];

    $selected = isset($_GET['vehicle_status_filter']) ? (string) $_GET['vehicle_status_filter'] : '';

    echo '<select name="vehicle_status_filter">';
    foreach ($options as $value => $label) {
        printf(
            '<option value="%s"%s>%s</option>',
            esc_attr($value),
            selected($selected, $value, false),
            esc_html($label)
        );
    }
    echo '</select>';
});

add_action('restrict_manage_posts', function ($post_type) {
    if ($post_type !== 'vehicles') return;

    $selected = isset($_GET['contact_rep_filter']) ? (int) $_GET['contact_rep_filter'] : 0;

    $users_q = new WP_User_Query([
        'number'     => -1,
        'orderby'    => 'display_name',
        'order'      => 'ASC',
        'fields'     => ['ID', 'display_name'],
        'meta_query' => [
            [
                'key'     => 'show_in_meet_the_team_page',
                'value'   => 1,
                'compare' => '=',
                'type'    => 'NUMERIC',
            ],
        ],
    ]);

    $users = $users_q->get_results();

    echo '<select name="contact_rep_filter">';
    echo '<option value="0">' . esc_html__('All Specialists', 'text-domain') . '</option>';

    foreach ($users as $u) {
        printf(
            '<option value="%d"%s>%s</option>',
            (int) $u->ID,
            selected($selected, (int) $u->ID, false),
            esc_html($u->display_name)
        );
    }
    echo '</select>';
});

add_action('restrict_manage_posts', function ($post_type) {
    if ($post_type !== 'vehicles') return;

    $selected = isset($_GET['auction_sale_filter']) ? sanitize_text_field($_GET['auction_sale_filter']) : '';

    // Traer auctions (CPT: auction) con sale_number existente
    $auctions = get_posts([
        'post_type'      => 'auction',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'meta_value',
        'meta_type'      => 'DATETIME',
        'meta_key'       => 'auction_date',
        'order'          => 'DESC',
        'fields'         => 'ids',
        'meta_query'     => [
            [
                'key'     => 'sale_number',
                'compare' => 'EXISTS',
            ],
        ],
    ]);

    echo '<select name="auction_sale_filter">';
    echo '<option value="">' . esc_html__('All Auctions', 'text-domain') . '</option>';

    foreach ($auctions as $auction_id) {
        $sale_number  = get_field('sale_number', $auction_id);
        if ($auction_id === null || $auction_id === '') continue;

        $auction_date = get_field('auction_date', $auction_id); // puede venir como string
        $title        = get_the_title($auction_id);

        // Formatear fecha si viene tipo "2026-11-22 12:00:00" o "2026-11-22"
        $date_label = '';
        if (!empty($auction_date)) {
            $ts = strtotime($auction_date);
            $date_label = $ts ? date_i18n('Y-m-d H:i', $ts) : (string) $auction_date;
        }

        $label = trim(sprintf('%s %s (Sale #%s)', $title, $date_label ? '| ' . $date_label : '', $sale_number));

        printf(
            '<option value="%s"%s>%s</option>',
            esc_attr((string) $auction_id),
            selected($selected, (string) $auction_id, false),
            esc_html($label)
        );
    }

    echo '</select>';
});

add_filter('posts_join', function ($join, WP_Query $query) {
    global $pagenow, $wpdb;

    if (!is_admin() || $pagenow !== 'edit.php' || !$query->is_main_query()) return $join;
    if ($query->get('post_type') !== 'vehicles') return $join;

    $has_any_filter =
        hnh_vs_active_tab() !== null ||
        !empty($_GET['vehicle_status_filter']) ||
        !empty($_GET['contact_rep_filter']) ||
        !empty($_GET['auction_sale_filter']);

    if (!$has_any_filter) return $join;

    if (!class_exists('VehiclesSearchRepository')) {
        require_once get_template_directory() . '/VehiclesSearchRepository.php';
    }

    $repo = new VehiclesSearchRepository($wpdb);
    $vs   = $repo->table();

    // evitar doble join
    if (strpos($join, 'vs_search') !== false) return $join;

    $join .= " INNER JOIN {$vs} vs_search ON vs_search.vehicle_id = {$wpdb->posts}.ID ";

    return $join;
}, 10, 2);


add_filter('posts_where', function ($where, WP_Query $query) {
    global $pagenow, $wpdb;

    if (!is_admin() || $pagenow !== 'edit.php' || !$query->is_main_query()) return $where;
    if ($query->get('post_type') !== 'vehicles') return $where;

    $active_tab = hnh_vs_active_tab();

    $has_any_filter =
        $active_tab !== null ||
        !empty($_GET['vehicle_status_filter']) ||
        !empty($_GET['contact_rep_filter']) ||
        !empty($_GET['auction_sale_filter']);

    if (!$has_any_filter) return $where;

    if (!class_exists('VehiclesSearchRepository')) {
        require_once get_template_directory() . '/VehiclesSearchRepository.php';
    }

	$repo  = new VehiclesSearchRepository($wpdb);
    $alias = 'vs_search';
	
	$where .= " AND {$alias}.is_deleted = 0 ";

    // ✅ TAB (solo 1)
	if ($active_tab === 'private_sales') {
    	$where .= $wpdb->prepare(" AND {$alias}.vehicle_type = %s ", 'private-sale');
	} elseif ($active_tab === 'single_zero_photos') {
    	$where .= $wpdb->prepare(" AND {$alias}.zero_photos = %d ", 1);
	} elseif ($active_tab === 'auctionless') {
    	$where .= $wpdb->prepare(" AND {$alias}.auctionless = %d ", 1);
	}

    // ✅ STATUS select
    if (!empty($_GET['vehicle_status_filter'])) {
        [$sql, $params] = $repo->whereStatus($alias, sanitize_text_field((string) $_GET['vehicle_status_filter']));
        $where .= $wpdb->prepare(" AND {$sql} ", ...$params);
    }

    // ✅ SPECIALIST select
    if (!empty($_GET['contact_rep_filter'])) {
        $userId = (int) $_GET['contact_rep_filter'];
        if ($userId > 0) {
            [$sql, $params] = $repo->whereSpecialist($alias, $userId);
            $where .= $wpdb->prepare(" AND {$sql} ", ...$params);
        }
    }

    // ✅ AUCTION select
    if (!empty($_GET['auction_sale_filter'])) {
        $auctionId = (int) $_GET['auction_sale_filter'];
        if ($auctionId > 0) {
            [$sql, $params] = $repo->whereAuctionId($alias, $auctionId);
            $where .= $wpdb->prepare(" AND {$sql} ", ...$params);
        }
    }

    return $where;
}, 10, 2);

// ------------------------------------------------------------

// Ocultar el filtro "All dates" en Vehicles y Auctions
add_filter('disable_months_dropdown', function ($disable, $post_type) {
    if (in_array($post_type, ['vehicles', 'auction'], true)) {
        return true;
    }
    return $disable;
}, 10, 2);

// ------------------------------------------------------------

/**
 * Cargar Select2 para el filtro de Auctions (reutiliza assets de ACF)
 */
add_action('admin_enqueue_scripts', function ($hook) {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;

    if (!$screen || $screen->id !== 'edit-vehicles') {
        return;
    }

    // Reutilizar Select2 de ACF (versión 4.0.13)
    $acf_url = plugins_url('advanced-custom-fields-pro/assets/inc/select2/4/');

    wp_enqueue_style('select2', $acf_url . 'select2.min.css', [], '4.0.13');
    wp_enqueue_script('select2', $acf_url . 'select2.full.min.js', ['jquery'], '4.0.13', true);
});

/**
 * Inicializar Select2 en el dropdown de Auctions
 */
add_action('admin_footer', function () {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;

    if (!$screen || $screen->id !== 'edit-vehicles') {
        return;
    }
?>
    <script>
        jQuery(document).ready(function($) {
            // Aplicar Select2 al filtro de auctions
            $('select[name="auction_sale_filter"]').select2({
                placeholder: 'All Auctions',
                allowClear: true,
                width: '300px',
                dropdownAutoWidth: true,
                matcher: function(params, data) {
                    // Si no hay búsqueda, mostrar todo
                    if ($.trim(params.term) === '') {
                        return data;
                    }

                    // Búsqueda case-insensitive en el texto completo
                    var term = params.term.toLowerCase();
                    var text = data.text.toLowerCase();

                    if (text.indexOf(term) > -1) {
                        return data;
                    }

                    return null;
                }
            });

            // Estilo similar a ACF
            $('select[name="auction_sale_filter"]').next('.select2-container').addClass('-acf');
        });
    </script>
    <style>
        /* Estilos para que se vea como ACF */
        .select2-container.-acf {
            font-size: 13px;
            float: left;
            margin-right: 6px;
        }

        .select2-container.-acf .select2-selection--single {
            height: 30px;
            line-height: 28px;
            border-color: #8c8f94;
        }

        .select2-container.-acf .select2-selection__rendered {
            line-height: 28px;
            padding-left: 8px;
        }

        .select2-container.-acf .select2-selection__arrow {
            height: 28px;
            top: 1px;
        }
    </style>
<?php
});

// ------------------------------------------------------------

/**
 * Hide Private Option in Edit Vehicle Page
 */
add_action('admin_head', function () {
    $screen = get_current_screen();

    if (!$screen || $screen->post_type !== 'vehicles') {
        return;
    }
?>
    <style>
        /* Oculta la opción "Private" en Visibility */
        #visibility-radio-private {
            display: none !important;
        }

        /* Oculta el label por si acaso */
        label[for="visibility-radio-private"] {
            display: none !important;
        }
    </style>
<?php
});

add_action('admin_head', function () {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;

    if (!$screen || $screen->id !== 'edit-vehicles') {
        return;
    }
?>
    <style>
        body.post-type-vehicles ul.subsubsub li.pillar_content {
            display: none !important;
        }
    </style>
<?php
});

// -------------------------------------------------------------
// -------------------------------------------------------------

add_filter('manage_vehicles_posts_columns', function ($columns) {

    $new = [];

    // Checkbox primero
    if (isset($columns['cb'])) {
        $new['cb'] = $columns['cb'];
    }

    // Columnas custom que van AL INICIO
    $new['vehicle_thumb'] = 'Image';
    $new['lot_number']    = 'Lot Number';

    foreach ($columns as $key => $label) {
        if ($key === 'cb') continue;
        $new[$key] = $label;
    }

    $new['notes'] = 'Internal Notes';

    return $new;
});

add_action('manage_vehicles_posts_custom_column', function ($column, $post_id) {

    if ($column === 'vehicle_thumb') {

        $thumb_id = get_post_thumbnail_id($post_id);

        if ($thumb_id) {
            echo wp_get_attachment_image($thumb_id, [60, 60], false, [
                'style' => 'width:60px;height:60px;object-fit:cover;border-radius:6px;',
            ]);
        } else {

            // Fallback: primera imagen del ACF Gallery
            $gallery = get_field('gallery_vehicle', $post_id);

            if (!empty($gallery) && is_array($gallery)) {

                $first_image = $gallery[0];

                if (!empty($first_image['ID'])) {
                    echo wp_get_attachment_image($first_image['ID'], [60, 60], false, [
                        'style' => 'width:60px;height:60px;object-fit:cover;border-radius:6px;',
                    ]);
                } else {
                    echo '<span style="opacity:.4;">—</span>';
                }
            } else {
                echo '<span style="opacity:.4;">—</span>';
            }
        }
    }

    if ($column === 'lot_number') {
        $lot = get_field('lot_number_latest', $post_id);

        echo $lot !== '' && $lot !== null
            ? esc_html($lot)
            : '<span style="opacity:.4;">—</span>';
    }

    if ($column === 'notes') {
        $notes = function_exists('get_field') ? get_field('notes', $post_id) : get_post_meta($post_id, 'notes', true);
        $notes = is_string($notes) ? $notes : '';

        echo '<span class="vehicle-notes-full" style="display:none" data-notes="' . esc_attr($notes) . '"></span>';

        if ($notes) {
            // Cortamos para que no rompa la tabla
            $short = wp_trim_words(wp_strip_all_tags($notes), 12, '…');
            echo esc_html($short);
        } else {
            echo '<span style="opacity:.4;"></span>';
        }
    }
}, 10, 2);

// New Columns Style
add_action('admin_head', function () {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->id !== 'edit-vehicles') return;
?>
    <style>
        .column-vehicle_thumb {
            width: 60px;
        }

        .column-lot_number,
        #lot_number {
            width: 124px;
            min-width: 124px;
            text-align: center;
            white-space: nowrap;
        }

        #lot_number a,
        .column-lot_number a {
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
<?php
});
