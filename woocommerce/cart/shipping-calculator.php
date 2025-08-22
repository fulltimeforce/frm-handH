<?php

/**
 * Shipping Calculator
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/shipping-calculator.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.7.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_shipping_calculator'); ?>

<form class="woocommerce-shipping-calculator" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">

	<?php printf('<a href="#" class="shipping-calculator-button" aria-expanded="false" aria-controls="shipping-calculator-form" role="button">
		<p>Change address</p>
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
  			<path d="M14 18V6C14 5.46957 13.7893 4.96086 13.4142 4.58579C13.0391 4.21071 12.5304 4 12 4H4C3.46957 4 2.96086 4.21071 2.58579 4.58579C2.21071 4.96086 2 5.46957 2 6V17C2 17.2652 2.10536 17.5196 2.29289 17.7071C2.48043 17.8946 2.73478 18 3 18H5M5 18C5 19.1046 5.89543 20 7 20C8.10457 20 9 19.1046 9 18M5 18C5 16.8954 5.89543 16 7 16C8.10457 16 9 16.8954 9 18M15 18H9M15 18C15 19.1046 15.8954 20 17 20C18.1046 20 19 19.1046 19 18M15 18C15 16.8954 15.8954 16 17 16C18.1046 16 19 16.8954 19 18M19 18H21C21.2652 18 21.5196 17.8946 21.7071 17.7071C21.8946 17.5196 22 17.2652 22 17V13.35C21.9996 13.1231 21.922 12.903 21.78 12.726L18.3 8.376C18.2065 8.25888 18.0878 8.16428 17.9528 8.0992C17.8178 8.03412 17.6699 8.00021 17.52 8H14" stroke="#A38B6C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
	</a>', esc_html(! empty($button_text) ? $button_text : __('Calculate shipping', 'woocommerce'))); ?>

	<section class="shipping-calculator-form" id="shipping-calculator-form" style="display:none;">

		<?php if (apply_filters('woocommerce_shipping_calculator_enable_country', true)) : ?>
			<p class="form-row form-row-wide" id="calc_shipping_country_field">
				<label for="calc_shipping_country"><?php esc_html_e('Country / region', 'woocommerce'); ?></label>
				<select name="calc_shipping_country" id="calc_shipping_country" class="country_to_state country_select" rel="calc_shipping_state">
					<option value="default"><?php esc_html_e('Select a country / region&hellip;', 'woocommerce'); ?></option>
					<?php
					foreach (WC()->countries->get_shipping_countries() as $key => $value) {
						echo '<option value="' . esc_attr($key) . '"' . selected(WC()->customer->get_shipping_country(), esc_attr($key), false) . '>' . esc_html($value) . '</option>';
					}
					?>
				</select>
			</p>
		<?php endif; ?>

		<?php if (apply_filters('woocommerce_shipping_calculator_enable_state', true)) : ?>
			<p class="form-row form-row-wide" id="calc_shipping_state_field">
				<?php
				$current_cc = WC()->customer->get_shipping_country();
				$current_r  = WC()->customer->get_shipping_state();
				$states     = WC()->countries->get_states($current_cc);

				if (is_array($states) && empty($states)) {
				?>
					<input type="hidden" name="calc_shipping_state" id="calc_shipping_state" />
				<?php
				} elseif (is_array($states)) {
				?>
					<span>
						<label for="calc_shipping_state"><?php esc_html_e('State / County', 'woocommerce'); ?></label>
						<select name="calc_shipping_state" class="state_select" id="calc_shipping_state">
							<option value=""><?php esc_html_e('Select an option&hellip;', 'woocommerce'); ?></option>
							<?php
							foreach ($states as $ckey => $cvalue) {
								echo '<option value="' . esc_attr($ckey) . '" ' . selected($current_r, $ckey, false) . '>' . esc_html($cvalue) . '</option>';
							}
							?>
						</select>
					</span>
				<?php
				} else {
				?>
					<label for="calc_shipping_state"><?php esc_html_e('State / County', 'woocommerce'); ?></label>
					<input type="text" class="input-text" value="<?php echo esc_attr($current_r); ?>" name="calc_shipping_state" id="calc_shipping_state" placeholder="<?php esc_html_e('State / County', 'woocommerce'); ?>" />
				<?php
				}
				?>
			</p>
		<?php endif; ?>

		<?php if (apply_filters('woocommerce_shipping_calculator_enable_city', true)) : ?>
			<p class="form-row form-row-wide" id="calc_shipping_city_field">
				<label for="calc_shipping_city"><?php esc_html_e('City:', 'woocommerce'); ?></label>
				<input type="text" class="input-text" value="<?php echo esc_attr(WC()->customer->get_shipping_city()); ?>" name="calc_shipping_city" id="calc_shipping_city" placeholder="<?php esc_html_e('Town / City', 'woocommerce'); ?>" />
			</p>
		<?php endif; ?>

		<?php if (apply_filters('woocommerce_shipping_calculator_enable_postcode', true)) : ?>
			<p class="form-row form-row-wide" id="calc_shipping_postcode_field">
				<label for="calc_shipping_postcode"><?php esc_html_e('Postcode / ZIP:', 'woocommerce'); ?></label>
				<input type="text" class="input-text" value="<?php echo esc_attr(WC()->customer->get_shipping_postcode()); ?>" name="calc_shipping_postcode" id="calc_shipping_postcode" placeholder="<?php esc_html_e('Postcode / ZIP', 'woocommerce'); ?>" />
			</p>
		<?php endif; ?>

		<div class="form-row-last">
			<button type="submit" name="calc_shipping" value="1" class="button<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>"><?php esc_html_e('Update', 'woocommerce'); ?></button>
		</div>
		<?php wp_nonce_field('woocommerce-shipping-calculator', 'woocommerce-shipping-calculator-nonce'); ?>
	</section>
</form>

<?php do_action('woocommerce_after_shipping_calculator'); ?>