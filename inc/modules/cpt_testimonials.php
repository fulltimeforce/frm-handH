<?php
// Register Custom Post Type: Testimonials
function create_testimonials_cpt()
{

    $labels = array(
        'name'                  => _x('Testimonials', 'Post Type General Name', 'textdomain'),
        'singular_name'         => _x('Testimonial', 'Post Type Singular Name', 'textdomain'),
        'menu_name'             => __('Testimonials', 'textdomain'),
        'name_admin_bar'        => __('Testimonial', 'textdomain'),
        'add_new'               => __('Add New', 'textdomain'),
        'add_new_item'          => __('Add New Testimonial', 'textdomain'),
        'edit_item'             => __('Edit Testimonial', 'textdomain'),
        'new_item'              => __('New Testimonial', 'textdomain'),
        'view_item'             => __('View Testimonial', 'textdomain'),
        'view_items'            => __('View Testimonials', 'textdomain'),
        'search_items'          => __('Search Testimonials', 'textdomain'),
        'not_found'             => __('No testimonials found', 'textdomain'),
        'not_found_in_trash'    => __('No testimonials found in Trash', 'textdomain'),
        'all_items'             => __('All Testimonials', 'textdomain'),
    );

    $args = array(
        'label'                 => __('Testimonial', 'textdomain'),
        'labels'                => $labels,
        'public'                => false, // 👈 Esto lo hace no accesible públicamente
        'show_ui'               => true,  // 👈 Pero aún visible en el admin
        'show_in_menu'          => true,
        'menu_position'         => 20,
        'menu_icon'             => 'dashicons-format-quote',
        'query_var'             => false,
        'rewrite'               => false, // 👈 No genera URLs
        'capability_type'       => 'post',
        'has_archive'           => false, // 👈 No archivo
        'hierarchical'          => false,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt'),
        'show_in_rest'          => true, // Para usar en Gutenberg/ACF/REST API
    );

    register_post_type('testimonials', $args);
}
add_action('init', 'create_testimonials_cpt', 0);
