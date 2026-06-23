<?php
if (!defined('ABSPATH')) exit;

// ==== REGISTER CPT: Model ====
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
    );

    $args = array(
        'label'                 => __('Models', 'textdomain'),
        'labels'                => $labels,
        'public'                => true,
        'has_archive'           => true,
        'show_in_export'        => false,

        // Keep fallback base (optional). Main URL will be nested.
        'rewrite'               => array('slug' => 'model', 'with_front' => false),

        'menu_icon'             => 'dashicons-layout',
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'show_in_rest'          => true,
    );

    register_post_type('model', $args);
}
add_action('init', 'register_model_cpt');


// =====================================================
// CUSTOM URL STRUCTURE: /makes-and-models/{make}/{model}/
// ACF field on model: brand (Post Object -> Make CPT, return ID)
// =====================================================

// 1) Change permalink of Model posts
add_filter('post_type_link', function ($permalink, $post) {

    if ($post->post_type !== 'model') {
        return $permalink;
    }

    // Your ACF field name is "brand" (not "make")
    $make_id = get_field('brand', $post->ID);

    // If ACF returns a post object instead of ID, normalize it
    if (is_object($make_id) && isset($make_id->ID)) {
        $make_id = $make_id->ID;
    }

    // Fallback (if no Make selected)
    if (!$make_id) {
        return home_url('/model/' . $post->post_name . '/');
    }

    $make_slug = get_post_field('post_name', $make_id);

    return home_url('/makes-and-models/' . $make_slug . '/' . $post->post_name . '/');

}, 10, 2);


// 2) Rewrite rule to resolve /makes-and-models/{make}/{model}/
// NOTE: This must be "top" priority so it wins over the Make single URL.
add_action('init', function () {

    add_rewrite_rule(
        '^makes-and-models/([^/]+)/([^/]+)/?$',
        'index.php?post_type=model&name=$matches[2]&make_slug=$matches[1]',
        'top'
    );

});


// 3) Register custom query var
add_filter('query_vars', function ($vars) {
    $vars[] = 'make_slug';
    return $vars;
});


// 4) Validate that the model belongs to the make (correctness + SEO)
add_filter('the_posts', function ($posts, $query) {

    if (is_admin() || (defined('REST_REQUEST') && REST_REQUEST)) {
        return $posts;
    }

    $make_slug = $query->get('make_slug');

    if (!$make_slug || empty($posts)) {
        return $posts;
    }

    $post = $posts[0];

    if ($post->post_type !== 'model') {
        return $posts;
    }

    $make_id = get_field('brand', $post->ID);

    if (is_object($make_id) && isset($make_id->ID)) {
        $make_id = $make_id->ID;
    }

    if (!$make_id) {
        return [];
    }

    $real_make_slug = get_post_field('post_name', $make_id);

    // Wrong make in URL => 404
    if ($real_make_slug !== $make_slug) {
        return [];
    }

    return $posts;

}, 10, 2);