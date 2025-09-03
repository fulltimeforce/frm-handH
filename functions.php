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
define('NOT_APPEAR', false);

// === Enqueue Styles and Scripts ===
function general_scripts()
{
    // CSS
    wp_enqueue_style('style', get_stylesheet_uri(), [], '1.0.0', 'all');
    wp_enqueue_style('main-css', get_template_directory_uri() . '/public/css/app.min.css', [], '1.0.0', 'all');

    // JavaScript
    wp_enqueue_script('main-js', get_template_directory_uri() . '/public/js/main.min.js', [], '1.0.0', true);

    if (is_page('our-services') || is_page('frequently-asked-questions') || is_page('faq') || is_page('careers') || is_page('insurance') || is_page('get-a-valuation') || is_page('ways-to-bid') || is_page('selling-at-auction') || is_singular('vehicles') || is_singular('auction')) {
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

function get_centered_banner($image_url = '', $title = '', $size = 'default')
{
    if (empty($title)) {
        $title = get_the_title();
    }
    if (empty($image_url)) {
        $image_url = IMG . '/banner.png';
    }
    $size_class = '';
    if ($size === 'small') {
        $size_class = 'small-banner';
    }

    echo '<section class="banner centered ' . esc_attr($size_class) . '">
        <div class="banner__bg">
            <img src="' . $image_url . '">
        </div>
        <div class="container">
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

    if (in_array((int) $form['id'], [2, 3, 4, 5], true)) {
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

    if (in_array((int) $form['id'], [1], true)) {
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
        $svg = '<img src="' . IMG . '/arrow-brown.png">';

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

require_once get_template_directory() . '/inc/modules/auctions/cpt.php';
require_once get_template_directory() . '/inc/modules/auctions/import.php';

require_once get_template_directory() . '/inc/modules/vehicles/cpt.php';
require_once get_template_directory() . '/inc/modules/vehicles/import.php';

require_once get_template_directory() . '/inc/modules/cpt_venues.php';
require_once get_template_directory() . '/inc/modules/cpt_testimonials.php';

require_once get_template_directory() . '/inc/hooks.php';

// -------------------------------------------------------------------------------------

function editing_navigation_account($items)
{
    // Agregar un elemento personalizado al menú de 'Mi cuenta'
    $items['wishlist'] = __('Wishlist', 'text-domain');
    $items['appointments'] = __('Appointments', 'text-domain');
    $items['lot-alerts'] = __('Lot Alerts', 'text-domain');
    $items['sale-remimder'] = __('Sale Remimder', 'text-domain');
    $items['identity-documents'] = __('Identity Documents', 'text-domain');
    $items['current-bids'] = __('Current Bids', 'text-domain');
    $items['past-bids'] = __('Past Bids', 'text-domain');
    $items['live-bidding-registrations'] = __('Live Bidding Registrations', 'text-domain');

    // Eliminar un elemento existente del menú de 'Mi cuenta'
    unset($items['downloads']);
    unset($items['dashboard']);
    unset($items['edit-address']);
    unset($items['customer-logout']);

    // Cambiando los labels
    $items['edit-account'] = '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 36 36" fill="none">
        <path d="M18.3333 19.6667C22.9357 19.6667 26.6667 15.9357 26.6667 11.3333C26.6667 6.73096 22.9357 3 18.3333 3C13.731 3 10 6.73096 10 11.3333C10 15.9357 13.731 19.6667 18.3333 19.6667ZM18.3333 19.6667C21.8696 19.6667 25.2609 21.0714 27.7614 23.5719C30.2619 26.0724 31.6667 29.4638 31.6667 33M18.3333 19.6667C14.7971 19.6667 11.4057 21.0714 8.90524 23.5719C6.40476 26.0724 5 29.4638 5 33" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg> Profile';

    $items['orders'] = '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 36 36" fill="none">
        <path d="M21 3V9C21 9.79565 21.3161 10.5587 21.8787 11.1213C22.4413 11.6839 23.2044 12 24 12H30M15 13.5H12M24 19.5H12M24 25.5H12M22.5 3H9C8.20435 3 7.44129 3.31607 6.87868 3.87868C6.31607 4.44129 6 5.20435 6 6V30C6 30.7956 6.31607 31.5587 6.87868 32.1213C7.44129 32.6839 8.20435 33 9 33H27C27.7956 33 28.5587 32.6839 29.1213 32.1213C29.6839 31.5587 30 30.7956 30 30V10.5L22.5 3Z" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg> Invoices & Payments';

    $items['wishlist'] = '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 36 36" fill="none">
        <path d="M17.2889 4.4423C17.3546 4.3095 17.4562 4.19772 17.5821 4.11957C17.7079 4.04141 17.8532 4 18.0013 4C18.1495 4 18.2947 4.04141 18.4206 4.11957C18.5465 4.19772 18.6481 4.3095 18.7138 4.4423L22.1785 11.4602C22.4067 11.9221 22.7436 12.3217 23.1603 12.6247C23.577 12.9278 24.061 13.1252 24.5708 13.2L32.3191 14.3339C32.4659 14.3552 32.6038 14.4171 32.7173 14.5127C32.8307 14.6083 32.9151 14.7337 32.961 14.8748C33.0069 15.0158 33.0124 15.1669 32.9769 15.311C32.9414 15.455 32.8662 15.5862 32.76 15.6898L27.1565 21.1463C26.787 21.5064 26.5105 21.9509 26.3508 22.4416C26.1912 22.9323 26.1532 23.4544 26.2401 23.963L27.563 31.6723C27.5889 31.8191 27.573 31.9702 27.5172 32.1083C27.4614 32.2465 27.3679 32.3662 27.2473 32.4538C27.1268 32.5413 26.984 32.5933 26.8353 32.6036C26.6867 32.6139 26.5381 32.5823 26.4066 32.5123L19.4802 28.8706C19.0238 28.631 18.5161 28.5058 18.0006 28.5058C17.4851 28.5058 16.9774 28.631 16.521 28.8706L9.5961 32.5123C9.46461 32.5819 9.31623 32.6132 9.16782 32.6026C9.01941 32.5921 8.87695 32.5401 8.75662 32.4526C8.6363 32.3651 8.54295 32.2456 8.48719 32.1076C8.43143 31.9697 8.4155 31.8189 8.44121 31.6723L9.76259 23.9645C9.84988 23.4557 9.81206 22.9332 9.65241 22.4422C9.49275 21.9513 9.21605 21.5065 8.84617 21.1463L3.24268 15.6913C3.13558 15.5878 3.05968 15.4564 3.02364 15.3119C2.9876 15.1675 2.99286 15.0158 3.03881 14.8741C3.08477 14.7325 3.16958 14.6066 3.28359 14.5109C3.39759 14.4151 3.53621 14.3533 3.68364 14.3324L11.4304 13.2C11.9408 13.1258 12.4254 12.9286 12.8427 12.6255C13.2599 12.3225 13.5973 11.9225 13.8257 11.4602L17.2889 4.4423Z" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg> Wishlist';

    $items['sale-remimder'] = '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 36 36" fill="none">
        <path d="M15.402 31.5001C15.6653 31.9561 16.044 32.3348 16.5001 32.5981C16.9561 32.8614 17.4734 33 18 33C18.5266 33 19.0439 32.8614 19.4999 32.5981C19.956 32.3348 20.3347 31.9561 20.598 31.5001M33 12C33 8.55002 31.8 5.55001 30 3M6 3C4.2 5.55001 3 8.55002 3 12M4.893 22.9891C4.69705 23.2039 4.56773 23.471 4.52078 23.7579C4.47384 24.0448 4.51128 24.3392 4.62856 24.6052C4.74584 24.8712 4.9379 25.0974 5.18138 25.2563C5.42486 25.4152 5.70927 25.4999 6 25.5001H30C30.2907 25.5002 30.5752 25.4158 30.8188 25.2573C31.0624 25.0987 31.2547 24.8727 31.3723 24.6069C31.4899 24.341 31.5277 24.0467 31.4812 23.7598C31.4346 23.4728 31.3056 23.2056 31.11 22.9906C29.115 20.9341 27 18.7486 27 12C27 9.61308 26.0518 7.32389 24.364 5.63605C22.6761 3.94822 20.3869 3 18 3C15.6131 3 13.3239 3.94822 11.636 5.63605C9.94821 7.32389 9 9.61308 9 12C9 18.7486 6.8835 20.9341 4.893 22.9891Z" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg> Sale reminder';

    $items['lot-alerts'] = '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 36 36" fill="none">
        <path d="M17.3258 25.2652C17.1481 25.9093 16.8453 26.5121 16.4347 27.0391C16.0241 27.5662 15.5136 28.0072 14.9326 28.337C14.3515 28.6668 13.7112 28.8789 13.0481 28.9612C12.3851 29.0436 11.7123 28.9945 11.0683 28.8168C10.4242 28.6391 9.82141 28.3363 9.29435 27.9257C8.7673 27.5151 8.32628 27.0047 7.99649 26.4236C7.6667 25.8425 7.45458 25.2022 7.37225 24.5392C7.28993 23.8761 7.339 23.2033 7.51668 22.5593M2.78125 15.4561L33.2233 7V27.2947L2.78125 20.5298V15.4561Z" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg> Lot alerts';

    $items['appointments'] = '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 36 36" fill="none">
        <path d="M31 11.25V9C31 8.20435 30.6839 7.44129 30.1213 6.87868C29.5587 6.31607 28.7956 6 28 6H7C6.20435 6 5.44129 6.31607 4.87868 6.87868C4.31607 7.44129 4 8.20435 4 9V30C4 30.7956 4.31607 31.5587 4.87868 32.1213C5.44129 32.6839 6.20435 33 7 33H12.25M23.5 3V9M11.5 3V9M4 15H11.5M25.75 26.25L23.5 24.45V21M32.5 24C32.5 28.9706 28.4706 33 23.5 33C18.5294 33 14.5 28.9706 14.5 24C14.5 19.0294 18.5294 15 23.5 15C28.4706 15 32.5 19.0294 32.5 24Z" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg> Appointments';

    $items['identity-documents'] = '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 36 36" fill="none">
        <path d="M24 14.5H27M24 20.5H27M9.255 22C9.56421 21.121 10.1387 20.3597 10.899 19.8211C11.6594 19.2826 12.5682 18.9933 13.5 18.9933C14.4318 18.9933 15.3406 19.2826 16.101 19.8211C16.8613 20.3597 17.4358 21.121 17.745 22M16.5 16C16.5 17.6569 15.1569 19 13.5 19C11.8431 19 10.5 17.6569 10.5 16C10.5 14.3431 11.8431 13 13.5 13C15.1569 13 16.5 14.3431 16.5 16ZM6 7H30C31.6569 7 33 8.34315 33 10V25C33 26.6569 31.6569 28 30 28H6C4.34315 28 3 26.6569 3 25V10C3 8.34315 4.34315 7 6 7Z" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg> Identity documents';

    $items['current-bids'] = '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 36 36" fill="none">
        <path d="M25.1554 23.3961L31.4311 17.1204L18.8796 4.56893L12.6039 10.8446L16.5262 14.767L21.233 19.4738L25.1554 23.3961Z" fill="black"/>
        <path d="M8.6816 32.0252L21.233 19.4738L16.5262 14.767L3.97481 27.3184C3.35065 27.9426 3 28.7891 3 29.6718C3 30.5545 3.35065 31.401 3.97481 32.0252C4.59897 32.6494 5.44551 33 6.3282 33C7.2109 33 8.05744 32.6494 8.6816 32.0252Z" fill="black"/>
        <path d="M21.233 19.4738L8.6816 32.0252C8.05744 32.6494 7.2109 33 6.3282 33C5.44551 33 4.59897 32.6494 3.97481 32.0252C3.35065 31.401 3 30.5545 3 29.6718C3 28.7891 3.35065 27.9426 3.97481 27.3184L16.5262 14.767M21.233 19.4738L16.5262 14.767M21.233 19.4738L25.1554 23.3961M16.5262 14.767L12.6039 10.8446M23.5864 24.965L33 15.5514M11.035 12.4136L20.4486 3M12.6039 10.8446L25.1554 23.3961M12.6039 10.8446L18.8796 4.56893L31.4311 17.1204L25.1554 23.3961" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg> Current bids';

    $items['past-bids'] = '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 36 36" fill="none">
        <path d="M21.233 19.4738L8.6816 32.0252C8.05744 32.6494 7.2109 33 6.3282 33C5.44551 33 4.59897 32.6494 3.97481 32.0252C3.35065 31.401 3 30.5545 3 29.6718C3 28.7891 3.35065 27.9426 3.97481 27.3184L16.5262 14.767M23.5864 24.965L33 15.5514M11.035 12.4136L20.4486 3M12.6039 10.8446L25.1554 23.3961M31.4311 17.1204L18.8796 4.56893" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg> Past bids';

    $items['live-bidding-registrations'] = '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 36 36" fill="none">
        <path d="M24 6H27C27.7956 6 28.5587 6.31607 29.1213 6.87868C29.6839 7.44129 30 8.20435 30 9V30C30 30.7956 29.6839 31.5587 29.1213 32.1213C28.5587 32.6839 27.7956 33 27 33H9C8.20435 33 7.44129 32.6839 6.87868 32.1213C6.31607 31.5587 6 30.7956 6 30V9C6 8.20435 6.31607 7.44129 6.87868 6.87868C7.44129 6.31607 8.20435 6 9 6H12M13.5 21L16.5 24L22.5 18M13.5 3H22.5C23.3284 3 24 3.67157 24 4.5V7.5C24 8.32843 23.3284 9 22.5 9H13.5C12.6716 9 12 8.32843 12 7.5V4.5C12 3.67157 12.6716 3 13.5 3Z" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg> Live bidding registrations';

    return $items;
}
add_filter('woocommerce_account_menu_items', 'editing_navigation_account');


/**
 * Formatea un datetime (string "Y-m-d H:i:s") a "12th Feb, 2025 - 9:00 am"
 */
function hnh_format_auction_datetime($datetime_string)
{
    if (empty($datetime_string)) return '';
    $ts = strtotime($datetime_string);
    if ($ts === false) return esc_html($datetime_string);
    // date_i18n respeta el timezone de WP
    return date_i18n('jS M, Y - g:i a', $ts);
}

/**
 * Renderiza la tarjeta de un auction.
 *
 * @param int      $auction_id  ID del post tipo "auction".
 * @param int|null $venue_id    ID del Venue (opcional). Si es 0/empty, intenta leerlo de ACF "template_venue".
 */
function hnh_render_auction_card($auction_id, $venue_id = 0)
{
    if (!$auction_id) return;

    // Asegurar IDs enteros
    $auction_id = (int) $auction_id;
    $venue_id   = (int) $venue_id;

    // Si no enviaron venue, tomarlo del ACF del auction
    if (!$venue_id) {
        $venue_id = (int) get_field('template_venue', $auction_id);
    }

    // Datos del auction
    $title        = get_the_title($auction_id);
    $permalink    = get_permalink($auction_id);
    $lots         = get_field('lots', $auction_id);
    $auction_date = get_field('auction_date', $auction_id); // guardado como "Y-m-d H:i:s"
    $date_label   = $auction_date ? hnh_format_auction_datetime($auction_date) : '';

    // Ubicación desde el Venue (ej.: tu campo 'slider_subtitle')
    $ubication = $venue_id ? get_field('slider_subtitle', $venue_id) : '';

    // Thumbnail (con fallback)
    $thumb_url = get_the_post_thumbnail_url($auction_id, 'large');
    if (!$thumb_url) {
        // Fallback: usa la constante IMG si existe, sino construye ruta al tema
        $img_base = defined('IMG') ? IMG : get_template_directory_uri() . '/assets/img';
        $thumb_url = $img_base . '/auction1.png';
    }

    // Link al venue si existe
    $venue_link = $venue_id ? get_permalink($venue_id) : '';

?>
    <div class="auction">
        <div class="auction_thumb">
            <div class="thumb">
                <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($title); ?>">
            </div>
        </div>

        <div class="auction_info">
            <h2><?php echo esc_html($title); ?></h2>

            <div class="content">
                <ul>
                    <?php if ($date_label): ?>
                        <li>Date: <b><?php echo esc_html($date_label); ?></b></li>
                    <?php endif; ?>

                    <?php if (!empty($ubication)): ?>
                        <li>Location: <b><?php echo esc_html($ubication); ?></b></li>
                    <?php endif; ?>

                    <?php if (!empty($lots)): ?>
                        <li>View Lots: <b><?php echo esc_html($lots); ?></b></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="content">
                <p><b>Classic Motorcars:</b> An auction of classic, collector and performance motorcars to be held in the beautiful surrounds of the Pavilion Gardens, Buxton, Derbyshire.</p>
            </div>

            <?php if ($venue_link): ?>
                <a alt="Venue Details" href="<?php echo esc_url($venue_link); ?>">
                    Venue Details
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="12" viewBox="0 0 22 12" fill="none">
                        <path d="M0.25 6H20.25M20.25 6L15.25 1M20.25 6L15.25 11" stroke="#8C6E47" stroke-width="1.20833" />
                    </svg>
                </a>
            <?php endif; ?>
        </div>

        <div class="auction_actions">
            <ul>
                <li><a href="<?php echo esc_url($permalink); ?>" alt="View Upcoming Lots">View Upcoming Lots</a></li>
                <li><a href="">Consign Your Classic</a></li>
                <li><a href="">Watch Live</a></li>
                <li><a href="">Learn how to bid</a></li>
                <li><a href="">View E-Catalogue</a></li>
            </ul>
        </div>

        <div class="auction_keytimes">
            <div class="auction_keytimes-grid">
                <div class="w-100">
                    <p>KEY TIMES:</p>
                </div>
                <div class="w-100">
                    <p>Viewing:</p>
                    <p>Tuesday, February 11th: From 12:00 PM (Noon) Wednesday, February 12th: From 9:00 AM</p>
                </div>
                <div class="w-100">
                    <p>Sale Time:</p>
                    <p>Wednesday, February 12th: From 12:00 PM (Noon)</p>
                </div>
            </div>
        </div>
    </div>
<?php
}