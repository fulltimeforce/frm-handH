<?php
// ==== REGISTRAR CUSTOM POST TYPE: Model ====
function register_model_cpt()
{

    $labels = array(
        'name'                  => _x('Models', 'Post Type General Name', 'textdomain'),
        'singular_name'         => _x('Model', 'Post Type Singular Name', 'textdomain'),
        'menu_name'             => __('Models', 'textdomain'),
        'name_admin_bar'        => __('Model', 'textdomain'),
        'add_new'               => __('Add New', 'textdomain'),
        'add_new_item'          => __('Add New Model', 'textdomain'),
        'edit_item'             => __('Edit Model', 'textdomain'),
        'new_item'              => __('New Model', 'textdomain'),
        'view_item'             => __('View Model', 'textdomain'),
        'view_items'            => __('View Models', 'textdomain'),
        'search_items'          => __('Search Models', 'textdomain'),
        'not_found'             => __('No models found', 'textdomain'),
        'not_found_in_trash'    => __('No models found in Trash', 'textdomain'),
        'all_items'             => __('All Models', 'textdomain'),
        'archives'              => __('Model Archives', 'textdomain'),
        'attributes'            => __('Model Attributes', 'textdomain'),
        'insert_into_item'      => __('Insert into model', 'textdomain'),
        'uploaded_to_this_item' => __('Uploaded to this model', 'textdomain'),
        'featured_image'        => __('Featured Image', 'textdomain'),
        'set_featured_image'    => __('Set featured image', 'textdomain'),
        'remove_featured_image' => __('Remove featured image', 'textdomain'),
        'use_featured_image'    => __('Use as featured image', 'textdomain'),
    );

    $args = array(
        'label'                 => __('Models', 'textdomain'),
        'labels'                => $labels,
        'public'                => true,
        'has_archive'           => true,
        'rewrite'               => array('slug' => 'models'),
        'menu_icon'             => 'dashicons-layout', // puedes cambiar el ícono
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'show_in_rest'          => true, // habilitar en Gutenberg/REST API
    );

    register_post_type('model', $args);
}
add_action('init', 'register_model_cpt');
