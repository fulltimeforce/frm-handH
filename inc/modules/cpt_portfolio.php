<?php

// require_once get_template_directory() . '/inc/modules/cpt_portfolio.php';

// Crear la función para el post type Portfolio
function custom_post_portfolio()
{
    register_post_type(
        'portfolio',
        array(
            'labels' => array(
                'name' => __('Portfolio', 'edward-dev'),
                'singular_name' => __('Project', 'edward-dev'),
                'all_items' => __('All Projects', 'edward-dev'),
                'add_new' => __('Add New', 'edward-dev'),
                'add_new_item' => __('Add New Project', 'edward-dev'),
                'edit' => __('Edit', 'edward-dev'),
                'edit_item' => __('Edit Project', 'edward-dev'),
                'new_item' => __('New Project', 'edward-dev'),
                'view_item' => __('View Project', 'edward-dev'),
                'search_items' => __('Search Projects', 'edward-dev'),
                'not_found' => __('No projects found', 'edward-dev'),
                'not_found_in_trash' => __('Nothing found in the trash', 'edward-dev'),
            ),
            'description' => __('A custom post type for portfolio projects', 'edward-dev'),
            'public' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => false,
            'show_ui' => true,
            'query_var' => true,
            'menu_position' => 10,
            'menu_icon' => 'dashicons-portfolio',
            'rewrite' => array('slug' => 'portfolio', 'with_front' => false),
            'has_archive' => 'portfolio',
            'capability_type' => 'post',
            'hierarchical' => false,
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions')
        )
    );
}
add_action('init', 'custom_post_portfolio');

// Registrar soporte para categorías y tags estándar si lo necesitas
register_taxonomy_for_object_type('category', 'portfolio');
register_taxonomy_for_object_type('post_tag', 'portfolio');