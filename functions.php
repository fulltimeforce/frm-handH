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

    if (is_page('frequently-asked-questions') || is_page('faq') || is_page('careers') || is_page('get-a-valuation') || is_singular('vehicles')) {
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

// -------------------------------------------------------------------------------------

add_filter('gform_submit_button', function ($button_html, $form) {

    if (in_array((int) $form['id'], [2, 3, 4], true)) {
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
        $svg = '<img src="' . IMG . '/arrow.png">';

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

add_filter('gform_field_content_3', function ($content, $field, $value, $entry_id, $form_id) {
    if ((int) $field->id === 8 && $field->type === 'fileupload') {
        return '<div class="my-filewrap">'
            . $content .
            '<img src="' . IMG . '/upload.png">
            <p>Drag and drop files here to upload, or click to select.</p>
            <span class="browse_file">Browse File</span>
        </div>';
    }
    return $content;
}, 10, 5);

add_filter('gform_field_content_4', function ($content, $field, $value, $entry_id, $form_id) {
    if ((int) $field->id === 8 && $field->type === 'fileupload') {
        return '<div class="my-filewrap">'
            . $content .
            '<img src="' . IMG . '/upload.png">
            <p>Drag and drop files here to upload, or click to select.</p>
            <span class="browse_file">Browse File</span>
        </div>';
    }
    return $content;
}, 10, 5);

// -------------------------------------------------------------------------------------

require_once get_template_directory() . '/inc/modules/cpt_auctions.php';
require_once get_template_directory() . '/inc/modules/cpt_vehicles.php';
require_once get_template_directory() . '/inc/hooks.php';

// -------------------------------------------------------------------------------------

function editing_navigation_account($items)
{
    // Agregar un elemento personalizado al menú de 'Mi cuenta'
    $items['wishlist'] = __('Wishlist', 'text-domain');

    // Eliminar un elemento existente del menú de 'Mi cuenta'
    unset($items['downloads']);
    unset($items['customer-logout']);
    
    // Cambiando los labels
    $items['dashboard'] = 'Panel';
    $items['edit-account'] = '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 36 36" fill="none">
        <path d="M18.3333 19.6667C22.9357 19.6667 26.6667 15.9357 26.6667 11.3333C26.6667 6.73096 22.9357 3 18.3333 3C13.731 3 10 6.73096 10 11.3333C10 15.9357 13.731 19.6667 18.3333 19.6667ZM18.3333 19.6667C21.8696 19.6667 25.2609 21.0714 27.7614 23.5719C30.2619 26.0724 31.6667 29.4638 31.6667 33M18.3333 19.6667C14.7971 19.6667 11.4057 21.0714 8.90524 23.5719C6.40476 26.0724 5 29.4638 5 33" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg> Profile';

    return $items;
}
add_filter('woocommerce_account_menu_items', 'editing_navigation_account');
