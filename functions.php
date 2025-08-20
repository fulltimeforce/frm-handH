<?php

/**
 * HandH Theme Functions
 * @package WordPress
 * @subpackage HandH
 * @since 1.0.0
 * @version 1.0.0
 */

// === Constants ===
define('URL', get_stylesheet_directory_uri());
define('IMG', URL . '/images');
define('JS', URL . '/libraries/js');
define('CSS', URL . '/libraries/css');

// === Enqueue Styles and Scripts ===
function general_scripts()
{
    // CSS
    wp_enqueue_style('style', get_stylesheet_uri(), [], '1.0.0', 'all');
    wp_enqueue_style('main-css', get_template_directory_uri() . '/public/css/app.min.css', [], '1.0.0', 'all');

    // JavaScript
    wp_enqueue_script('main-js', get_template_directory_uri() . '/public/js/main.min.js', [], '1.0.0', true);

    if(is_page('frequently-asked-questions') || is_page('faq') || is_page('careers')){
        wp_enqueue_style('accordioncss', CSS . '/accordion.css', [], '1.0.0', 'all');
        wp_enqueue_script('jquerycustom', JS . '/jquery.min.js', [], '1.0.0', true);
        wp_enqueue_script('accordionjs', JS . '/accordion.min.js', ['jquerycustom'], '1.0.0', true);
    }
}
add_action('wp_enqueue_scripts', 'general_scripts');

// === Theme Support ===
function client_theme_support()
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption']);
}
add_action('after_setup_theme', 'client_theme_support');

remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

function remove_block_css()
{
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
}
add_action('wp_enqueue_scripts', 'remove_block_css', 100);

// === Excerpt Length ===
function client_excerpt_length($length)
{
    return 30;
}
add_filter('excerpt_length', 'client_excerpt_length');

// === Register Navigation Menus ===
function client_register_menus()
{
    register_nav_menus([
        'header-menu' => __('Header Menu', 'client'),
        'footer-menu' => __('Footer Menu', 'client'),
    ]);
}
add_action('init', 'client_register_menus');

function get_banner($breadcrumb = '', $image_url = '', $title = '')
{
    if (empty($title)) {
        $title = get_the_title();
    }
    if (empty($image_url)) {
        $image_url = IMG . '/banner.png';
    }

    echo '<section class="banner">
        <div class="banner__bg">
            <img src="' . $image_url . '">
        </div>
        <div class="container">
            <div class="breadcrumb">
                <p>' . $breadcrumb . '</p>
            </div>
            <h1>' . $title . '</h1>
        </div>
    </section>';
}


function get_card_product($product_id)
{
    if (! $product_id) {
        return;
    }

    $product = wc_get_product($product_id);

    if (! $product) {
        return;
    }

    $image_src = IMG . '/placeholder.png';

    if (has_post_thumbnail($product_id)) {
        $thumb_id  = get_post_thumbnail_id($product_id);
        $thumb_url = wp_get_attachment_image_src($thumb_id, 'woocommerce_thumbnail');
        $image_src = $thumb_url[0];
    }
?>
    <a href="<?php echo esc_url(get_permalink($product_id)); ?>" class="shop_product" data-id="<?php echo esc_attr($product_id); ?>">
        <div class="shop_product-image">
            <img src="<?php echo esc_url($image_src); ?>" alt="<?php echo esc_html(get_the_title($product_id)); ?>">
        </div>
        <div class="shop_product-info">
            <h3><?php echo esc_html(get_the_title($product_id)); ?></h3>
            <?php
            $excerpt = get_the_excerpt($product_id);
            if ($excerpt) :
            ?>
                <div class="shop_product-description">
                    <p><?php echo esc_html($excerpt); ?></p>
                </div>
            <?php endif; ?>
            <?php echo wp_kses_post($product->get_price_html()); ?>
        </div>
    </a>
<?php
}

<<<<<<< HEAD




add_filter('gform_submit_button', function ($button_html, $form) {

    if (in_array((int) $form['id'], [2, 7], true)) {
        // Extrae attrs del input original
        preg_match('/id="([^"]+)"/', $button_html, $mId);
        preg_match('/class="([^"]+)"/', $button_html, $mClass);
        preg_match('/onclick="([^"]+)"/', $button_html, $mOnclick);
        preg_match('/value="([^"]+)"/', $button_html, $mValue);

        $id      = $mId[1]      ?? '';
        $class   = $mClass[1]   ?? 'gform_button button';
        $onclick = isset($mOnclick[1]) ? ' onclick="' . esc_attr($mOnclick[1]) . '"' : '';
        $label   = $mValue[1]   ?? __('Submit', 'gravityforms');

        // SVG (hereda color del texto)
        $svg = '<img src="'.IMG.'/arrow.png">';

        return sprintf(
            '<button type="submit" id="%s" class="%s custom-submit"%s>
                %s %s
            </button>',
            esc_attr($id),
            esc_attr($class . ' has-icon'),
            $onclick,
            esc_html($label),
            $svg
        );
    }

    // <- IMPORTANTÍSIMO: devolver el HTML original si no aplica
    return $button_html;

}, 10, 2);
=======
// Register Custom Post Type: Auctions
function register_auctions_cpt() {
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
function register_auction_categories_taxonomy() {
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
>>>>>>> homepage
