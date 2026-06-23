<?php
if (!defined('ABSPATH')) {
  exit;
}

add_action('admin_head', 'hnh_admin_head_css');
add_action('admin_footer', 'hnh_admin_footer_css');

function hnh_admin_head_css()
{
  echo '<style>
		tr[data-slug="media-sync"], tr[data-slug="wp-sort-order"] { display: none !important; }
		.acf-field[data-key="field_auction_date_latest"] input {
			background: #f5f5f5 !important;
            color: #666 !important;
            opacity: .8;
            pointer-events: none;
			cursor: not-allowed;
		}
	</style>';
}

function hnh_admin_footer_css()
{
  ?>
  <style>
    tr[data-plugin="wp-duplicate-page/wp-duplicate-page.php"],
    tr[data-plugin="woocommerce-ajax-cart/wooajaxcart.php"] {
      display: none !important;
    }
  </style>
  <?php
}