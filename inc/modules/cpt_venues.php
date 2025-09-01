<?php

// Register Custom Post Type: Venues
function register_venues_cpt()
{
    $labels = array(
        'name'                  => 'Venues',
        'singular_name'         => 'Venue',
        'menu_name'             => 'Venues',
        'name_admin_bar'        => 'Venue',
        'add_new'               => 'Add New',
        'add_new_item'          => 'Add New Venue',
        'new_item'              => 'New Venue',
        'edit_item'             => 'Edit Venue',
        'view_item'             => 'View Venue',
        'all_items'             => 'All Venues',
        'search_items'          => 'Search Venues',
        'not_found'             => 'No venues found.',
        'not_found_in_trash'    => 'No venues found in Trash.',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'venues'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-location-alt',
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
        'show_in_rest'       => true,
    );

    register_post_type('venue', $args);
}
add_action('init', 'register_venues_cpt');

// Register Custom Taxonomy: Venue Categories
function register_venue_categories_taxonomy()
{
    $labels = array(
        'name'              => 'Venue Categories',
        'singular_name'     => 'Venue Category',
        'search_items'      => 'Search Venue Categories',
        'all_items'         => 'All Venue Categories',
        'parent_item'       => 'Parent Category',
        'parent_item_colon' => 'Parent Category:',
        'edit_item'         => 'Edit Venue Category',
        'update_item'       => 'Update Venue Category',
        'add_new_item'      => 'Add New Venue Category',
        'new_item_name'     => 'New Venue Category Name',
        'menu_name'         => 'Venue Categories',
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'venue-category'),
        'show_in_rest'      => true,
    );

    register_taxonomy('venue_category', array('venue'), $args);
}
add_action('init', 'register_venue_categories_taxonomy');