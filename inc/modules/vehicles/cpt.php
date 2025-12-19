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
            'rewrite'            => array('slug' => 'vehicles'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-car',
            // 'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
            'supports'           => array('title', 'thumbnail', 'excerpt', 'custom-fields'),
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
            'rewrite'           => array('slug' => 'vehicle-category'),
            'show_in_rest'      => true,
        ));
    }
}
add_action('init', 'vehicles_register_tax_cat', 1);

// ===== Vehicle Brands (non-hierarchical) =====
if (! function_exists('vehicles_register_tax_brand')) {
    function vehicles_register_tax_brand()
    {
        $labels = array(
            'name'                       => 'Makes',
            'singular_name'              => 'Makes',
            'search_items'               => 'Search Makes',
            'popular_items'              => 'Popular Makes',
            'all_items'                  => 'All Makes',
            'edit_item'                  => 'Edit Vehicle Brand',
            'update_item'                => 'Update Vehicle Brand',
            'add_new_item'               => 'Add New Vehicle Brand',
            'new_item_name'              => 'New Vehicle Brand Name',
            'separate_items_with_commas' => 'Separate brands with commas',
            'add_or_remove_items'        => 'Add or remove brands',
            'choose_from_most_used'      => 'Choose from the most used brands',
            'menu_name'                  => 'Makes',
        );

        register_taxonomy('vehicle_brand', array('vehicles'), array(
            'hierarchical'          => false,
            'labels'                => $labels,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var'             => true,
            'rewrite'               => array('slug' => 'vehicle-brand'),
            'show_in_rest'          => true,
        ));
    }
}
add_action('init', 'vehicles_register_tax_brand', 1);











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
    return $columns;
});
add_action('pre_get_posts', function ($query) {
    if (!is_admin() || !$query->is_main_query()) return;
    if ($query->get('orderby') === 'vehicle_status') {
        // El campo ACF se guarda como meta_key 'status'
        $query->set('meta_key', 'status');
        $query->set('orderby', 'meta_value'); // usa meta_value_num si es numérico
    }
});

// 4) (Opcional) Ajustar ancho en admin
add_action('admin_head', function () {
    echo '<style>.column-vehicle_status{width:240px}</style>';
});


/**
 * Filtro por Status en el listado de Vehicles (CPT)
 */
add_action('restrict_manage_posts', function ($post_type) {
    if ($post_type !== 'vehicles') {
        return;
    }

    // Opciones de status
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

    $selected = isset($_GET['vehicle_status_filter']) ? $_GET['vehicle_status_filter'] : '';

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

/**
 * Filtrar la query principal según el Status elegido
 */
add_action('pre_get_posts', function ($query) {
    global $pagenow;

    if (
        !is_admin() ||
        $pagenow !== 'edit.php' ||
        !$query->is_main_query() ||
        $query->get('post_type') !== 'vehicles'
    ) {
        return;
    }

    if (!empty($_GET['vehicle_status_filter'])) {
        $status = sanitize_text_field($_GET['vehicle_status_filter']);
        $meta_query = [
            [
                'key'     => 'status',
                'value'   => $status,
                'compare' => '=',
            ],
        ];
        $query->set('meta_query', $meta_query);
    }
});






/**
 * Filtro: lot_number_latest con/ sin 'p' o 'P'
 */
/*add_action('restrict_manage_posts', function ($post_type) {
    if ($post_type !== 'vehicles') return;

    $current = isset($_GET['lot_letters']) ? sanitize_text_field($_GET['lot_letters']) : '';

    echo '<select name="lot_letters">';
    echo '<option value=""' . selected($current, '', false) . '>' . esc_html__('All lot numbers', 'text-domain') . '</option>';
    echo '<option value="with"' . selected($current, 'with', false) . '>' . esc_html__("Lot with 'p'/'P'", 'text-domain') . '</option>';
    echo '<option value="without"' . selected($current, 'without', false) . '>' . esc_html__("Lot without 'p'/'P' (or empty)", 'text-domain') . '</option>';
    echo '</select>';
});*/

/**
 * Aplica el filtro a la query principal del admin
 */
/*add_action('pre_get_posts', function ($query) {
    global $pagenow;

    if (!is_admin() || $pagenow !== 'edit.php' || !$query->is_main_query() || $query->get('post_type') !== 'vehicles') {
        return;
    }

    // Conserva meta_query existente (por ejemplo, el de Status)
    $meta_query = (array) $query->get('meta_query');

    if (!empty($_GET['vehicle_status_filter'])) {
        $status = sanitize_text_field($_GET['vehicle_status_filter']);
        $meta_query[] = [
            'key'     => 'status',
            'value'   => $status,
            'compare' => '=',
        ];
    }

    if (!empty($_GET['lot_letters'])) {
        $choice = sanitize_text_field($_GET['lot_letters']);

        if ($choice === 'with') {
            // Contiene 'p' o 'P'
            $meta_query[] = [
                'key'     => 'lot_number_latest',
                'value'   => '[pP]',
                'compare' => 'REGEXP',
            ];
        } elseif ($choice === 'without') {
            // No contiene 'p' ni 'P' o está vacío / no existe
            $meta_query[] = [
                'relation' => 'OR',
                [
                    'key'     => 'lot_number_latest',
                    'value'   => '[pP]',
                    'compare' => 'NOT REGEXP',
                ],
                [
                    'key'     => 'lot_number_latest',
                    'value'   => '',
                    'compare' => '=',
                ],
                [
                    'key'     => 'lot_number_latest',
                    'compare' => 'NOT EXISTS',
                ],
            ];
        }
    }

    if (!empty($meta_query)) {
        $query->set('meta_query', $meta_query);
    }
});*/




// Ocultar el filtro "All dates" en Vehicles y Auctions
add_filter('disable_months_dropdown', function ($disable, $post_type) {
    if (in_array($post_type, ['vehicles', 'auction'], true)) {
        return true;
    }
    return $disable;
}, 10, 2);