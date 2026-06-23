<?php
if (!defined('ABSPATH')) {
  exit;
}

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
define('THEME_VERSION', '1.0.7');

require_once get_template_directory() . '/VehiclesSearchRepository.php';

// === Helpers ===
require_once get_template_directory() . '/inc/helpers/index.php';

// === Enqueue Styles and Scripts ===
require_once get_template_directory() . '/inc/assets.php';
require_once get_template_directory() . '/inc/walker.php';
// === Theme Support ===
require_once get_template_directory() . '/inc/theme-setup.php';

// === Template Tags ===
require_once get_template_directory() . '/inc/template-tags/banners.php';
require_once get_template_directory() . '/inc/template-tags/product-card.php';

// === Integrations ===
require_once get_template_directory() . '/inc/integrations/gravity-forms.php';
require_once get_template_directory() . '/inc/integrations/woocommerce-account-menu.php';

// === Modules ===
require_once get_template_directory() . '/inc/modules/auctions/cpt.php';
require_once get_template_directory() . '/inc/modules/auctions/import.php';
require_once get_template_directory() . '/inc/modules/auctions/structure.php';
require_once get_template_directory() . '/inc/modules/auctions/bulk-images.php';
require_once get_template_directory() . '/inc/modules/auctions/admin-columns.php';

require_once get_template_directory() . '/inc/modules/vehicles/cpt.php';
require_once get_template_directory() . '/inc/modules/vehicles/quick_edit.php';
require_once get_template_directory() . '/inc/modules/vehicles/admin-bulk-edit.php';
require_once get_template_directory() . '/inc/modules/vehicles/import.php';
require_once get_template_directory() . '/inc/modules/vehicles/structure.php';
require_once get_template_directory() . '/inc/modules/vehicles/footnote.php';
require_once get_template_directory() . '/inc/modules/vehicles/admin-columns.php';
require_once get_template_directory() . '/inc/modules/vehicles/admin-scripts.php';
require_once get_template_directory() . '/inc/modules/vehicles/admin-gallery.php';
require_once get_template_directory() . '/inc/modules/vehicles/admin-gallery-replace.php';
require_once get_template_directory() . '/inc/modules/vehicles/export.php';
require_once get_template_directory() . '/inc/modules/vehicles/backend-search.php';
require_once get_template_directory() . '/inc/modules/vehicles/thead-filters-by-sql.php';

require_once get_template_directory() . '/vehicles-search-sync.php';

require_once get_template_directory() . '/inc/modules/cpt_venues.php';
require_once get_template_directory() . '/inc/modules/cpt_models.php';
require_once get_template_directory() . '/inc/modules/cpt_testimonials.php';

require_once get_template_directory() . '/inc/hooks.php';
require_once get_template_directory() . '/inc/social_information.php';

// Admin Vehicles Prev / Next navigation
require_once get_template_directory() . '/nav-admin-buttons.php';

// TRACKING SYSTEM
require_once get_template_directory() . '/tracking/index.php';

// ADMIN 
require_once get_template_directory() . '/inc/admin/admin-ui.php';
require_once get_template_directory() . '/inc/admin/auction-search.php';

// -----------------------------------------------------------------------
require_once get_template_directory() . '/inc/admin/export-hide.php';
// FEATURES - MEMBER TEAM
require_once get_template_directory() . '/inc/features/member-team.php';
// FEED
require_once get_template_directory() . '/inc/feeds/vehicles-feed.php';

require_once get_template_directory() . '/inc/feeds/vehicles-feed.php';

require_once get_template_directory() . '/inc/change-url.php';

// LAST - LO DEJO PORSEACASO
function hnh_remove_editor_from_vehicle()
{
    remove_post_type_support('vehicle', 'editor');
}
add_action('init', 'hnh_remove_editor_from_vehicle');


add_filter( 'rank_math/sitemap/exclude_posts', function ( $exclude, $post ) {

    $excluded_ids = [57, 58]; // IDs reales de cart y checkout

    if ( in_array( $post->ID, $excluded_ids, true ) ) {
        return true;
    }

    return $exclude;
}, 10, 2 );




function render_makes_by_category($category_slug)
{
    $args = [
        'post_type'      => 'make',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'tax_query'      => [
            [
                'taxonomy' => 'make-category',
                'field'    => 'slug',
                'terms'    => $category_slug,
            ]
        ],
		'meta_query'     => [
        	[
            	'key'     => 'visible_in_web',
            	'value'   => '1',
            	'compare' => '=',
        	]
    	],
    ];

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        wp_reset_postdata();
        return; 
    }

    echo '<ul class="submenu_dropdown-listing w-100">';

    while ($query->have_posts()) {
        $query->the_post();
        echo '<li>';
        echo '<a href="' . get_permalink() . '" class="submenu-link small">';
        echo '<p>' . get_the_title() . '</p>';
        echo '</a>';
        echo '</li>';
    }

    echo '</ul>';

    wp_reset_postdata();
}

function format_auction_title($post) {
    if (!$post) return '';

    $auction_date = get_field('auction_date', $post->ID);
    $sale_number  = get_field('sale_number', $post->ID);

    $parts = [$post->post_title];

    if ($auction_date) {
        $parts[] = date('Y-m-d H:i:s', strtotime($auction_date));
    }

    if ($sale_number) {
        $parts[] = 'Sale #' . $sale_number;
    }

    return implode(' | ', $parts);
}

add_filter('acf/fields/post_object/result', function($title, $post, $field, $post_id) {
    if ($field['name'] !== 'auction_number_latest') return $title;

    return format_auction_title($post);

}, 10, 4);
