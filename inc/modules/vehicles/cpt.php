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
            'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
            'show_in_rest'       => true,
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
            'name'                       => 'Vehicle Brands',
            'singular_name'              => 'Vehicle Brand',
            'search_items'               => 'Search Vehicle Brands',
            'popular_items'              => 'Popular Vehicle Brands',
            'all_items'                  => 'All Vehicle Brands',
            'edit_item'                  => 'Edit Vehicle Brand',
            'update_item'                => 'Update Vehicle Brand',
            'add_new_item'               => 'Add New Vehicle Brand',
            'new_item_name'              => 'New Vehicle Brand Name',
            'separate_items_with_commas' => 'Separate brands with commas',
            'add_or_remove_items'        => 'Add or remove brands',
            'choose_from_most_used'      => 'Choose from the most used brands',
            'menu_name'                  => 'Vehicle Brands',
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