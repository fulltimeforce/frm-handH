<?php

// Register Custom Post Type: Auctions
function register_auctions_cpt()
{
    $labels = array(
        'name'                  => 'Auctions',
        'singular_name'         => 'Auction',
        'menu_name'             => 'Auctions',
        'name_admin_bar'        => 'Auction',
        'add_new'               => 'Add New',
        'add_new_item'          => 'Add New Auction',
        'new_item'              => 'New Auction',
        'edit_item'             => 'Edit Auction',
        'view_item'             => 'View Auction',
        'all_items'             => 'All Auctions',
        'search_items'          => 'Search Auctions',
        'not_found'             => 'No auctions found.',
        'not_found_in_trash'    => 'No auctions found in Trash.',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'auctions'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-tag',
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
        'show_in_rest'       => true,
    );

    register_post_type('auction', $args);
}
add_action('init', 'register_auctions_cpt');

// Register Custom Taxonomy: Auction Categories
function register_auction_categories_taxonomy()
{
    $labels = array(
        'name'              => 'Auction Categories',
        'singular_name'     => 'Auction Category',
        'search_items'      => 'Search Auction Categories',
        'all_items'         => 'All Auction Categories',
        'parent_item'       => 'Parent Category',
        'parent_item_colon' => 'Parent Category:',
        'edit_item'         => 'Edit Auction Category',
        'update_item'       => 'Update Auction Category',
        'add_new_item'      => 'Add New Auction Category',
        'new_item_name'     => 'New Auction Category Name',
        'menu_name'         => 'Auction Categories',
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'auction-category'),
        'show_in_rest'      => true,
    );

    register_taxonomy('auction_category', array('auction'), $args);
}
add_action('init', 'register_auction_categories_taxonomy');
