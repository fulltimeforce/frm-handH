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
