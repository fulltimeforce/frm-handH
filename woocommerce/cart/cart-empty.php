<?php

/**
 * Empty cart page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-empty.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined('ABSPATH') || exit;

/*
 * @hooked wc_empty_cart_message - 10
 */
do_action('woocommerce_cart_is_empty');

if (wc_get_page_id('shop') > 0) : ?>
	<div class="cart_page-empty">
		<svg xmlns="http://www.w3.org/2000/svg" width="65" height="64" viewBox="0 0 65 64" fill="none">
			<path d="M5.96875 5.46875H11.3021L18.3954 38.5887C18.6556 39.8017 19.3305 40.886 20.304 41.655C21.2774 42.4241 22.4885 42.8297 23.7288 42.8021H49.8088C51.0226 42.8001 52.1994 42.3842 53.1448 41.623C54.0903 40.8618 54.7478 39.8008 55.0088 38.6154L59.4088 18.8021H14.1554M24.5021 56.0021C24.5021 57.4748 23.3082 58.6687 21.8354 58.6687C20.3627 58.6687 19.1687 57.4748 19.1687 56.0021C19.1687 54.5293 20.3627 53.3354 21.8354 53.3354C23.3082 53.3354 24.5021 54.5293 24.5021 56.0021ZM53.8354 56.0021C53.8354 57.4748 52.6415 58.6687 51.1688 58.6687C49.696 58.6687 48.5021 57.4748 48.5021 56.0021C48.5021 54.5293 49.696 53.3354 51.1688 53.3354C52.6415 53.3354 53.8354 54.5293 53.8354 56.0021Z" stroke="#8C6E47" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
		</svg>
		<h2>You shopping cart is currently empty</h2>
		<a class="wc-backward<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>" href="<?php echo esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))); ?>">
			<?php
			/**
			 * Filter "Return To Shop" text.
			 *
			 * @since 4.6.0
			 * @param string $default_text Default text.
			 */
			echo esc_html(apply_filters('woocommerce_return_to_shop_text', __('Return to shop', 'woocommerce')));
			?>
		</a>
	</div>
<?php endif; ?>