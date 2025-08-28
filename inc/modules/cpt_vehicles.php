<?php 
// Register Custom Post Type: Vehicles
function cpt_vehicles_init() {

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
        'not_found_in_trash'    => 'No vehicles found in Trash.'
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
    );

    register_post_type('vehicles', $args);
}
add_action('init', 'cpt_vehicles_init');
