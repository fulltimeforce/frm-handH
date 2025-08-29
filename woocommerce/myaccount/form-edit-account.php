<?php

/**
 * Edit account form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-edit-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.7.0
 */

defined('ABSPATH') || exit;

/**
 * Hook - woocommerce_before_edit_account_form.
 *
 * @since 2.6.0
 */
do_action('woocommerce_before_edit_account_form');

?>



<?php if (is_user_logged_in()) :
	$current_user = wp_get_current_user();

	// Nombre
	$full_name = trim($current_user->first_name . ' ' . $current_user->last_name);
	if (empty($full_name)) {
		$full_name = $current_user->display_name;
	}

	// Teléfono
	$telephone = get_user_meta($current_user->ID, 'billing_phone', true);

	// Dirección de facturación
	$billing_address_1 = get_user_meta($current_user->ID, 'billing_address_1', true);
	$billing_address_2 = get_user_meta($current_user->ID, 'billing_address_2', true);
	$billing_city      = get_user_meta($current_user->ID, 'billing_city', true);
	$billing_postcode  = get_user_meta($current_user->ID, 'billing_postcode', true);
	$billing_state     = get_user_meta($current_user->ID, 'billing_state', true);
	$billing_country   = get_user_meta($current_user->ID, 'billing_country', true);

	// Dirección de envío
	$shipping_address_1 = get_user_meta($current_user->ID, 'shipping_address_1', true);
	$shipping_address_2 = get_user_meta($current_user->ID, 'shipping_address_2', true);
	$shipping_city      = get_user_meta($current_user->ID, 'shipping_city', true);
	$shipping_postcode  = get_user_meta($current_user->ID, 'shipping_postcode', true);
	$shipping_state     = get_user_meta($current_user->ID, 'shipping_state', true);
	$shipping_country   = get_user_meta($current_user->ID, 'shipping_country', true);

	// Email
	$email = $current_user->user_email;
?>
	<div class="profile">
		<h3>Profile and Settings</h3>
		<div class="profile_data w-100">
			<div class="profile_data-row">
				<div class="table">
					<?php if (!empty($full_name)) : ?>
						<div class="th">
							<p><b>Full Name</b></p>
						</div>
						<div class="td"><?php echo esc_html($full_name); ?></div>
					<?php endif; ?>

					<?php if (!empty($telephone)) : ?>
						<div class="th">
							<p><b>Telephone</b></p>
						</div>
						<div class="td"><?php echo esc_html($telephone); ?></div>
					<?php endif; ?>

					<?php if ($billing_address_1 || $billing_address_2 || $billing_city || $billing_postcode || $billing_state || $billing_country) : ?>
						<div class="th">
							<p><b>Address</b></p>
						</div>
						<div class="td">
							<p>
								<?php if ($billing_address_1) echo esc_html($billing_address_1) . '<br>'; ?>
								<?php if ($billing_address_2) echo esc_html($billing_address_2) . '<br>'; ?>
								<?php if ($billing_city) echo esc_html($billing_city) . '<br>'; ?>
								<?php if ($billing_postcode) echo esc_html($billing_postcode) . '<br>'; ?>
								<?php if ($billing_state) echo esc_html($billing_state) . '<br>'; ?>
								<?php if ($billing_country) echo esc_html($billing_country); ?>
							</p>
						</div>
					<?php endif; ?>
				</div>
				<a class="open_modal" data-modal="1">
					Change My Details
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
						<path d="M11.4755 3.04896H4.10567C3.54721 3.04896 3.01163 3.27081 2.61674 3.6657C2.22185 4.06059 2 4.59617 2 5.15463V19.8943C2 20.4528 2.22185 20.9884 2.61674 21.3833C3.01163 21.7782 3.54721 22 4.10567 22H18.8454C19.4038 22 19.9394 21.7782 20.3343 21.3833C20.7292 20.9884 20.951 20.4528 20.951 19.8943V12.5245M18.1873 2.65415C18.6062 2.2353 19.1743 2 19.7666 2C20.3589 2 20.927 2.2353 21.3459 2.65415C21.7647 3.07299 22 3.64107 22 4.2334C22 4.82574 21.7647 5.39381 21.3459 5.81265L11.8566 15.3029C11.6066 15.5527 11.2978 15.7355 10.9586 15.8346L7.93378 16.719C7.84319 16.7454 7.74715 16.747 7.65574 16.7236C7.56432 16.7001 7.48088 16.6526 7.41415 16.5859C7.34742 16.5191 7.29986 16.4357 7.27644 16.3443C7.25302 16.2528 7.2546 16.1568 7.28102 16.0662L8.1654 13.0414C8.26493 12.7025 8.44813 12.394 8.69814 12.1444L18.1873 2.65415Z" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</a>
			</div>

			<div class="profile_data-row">
				<div class="table">
					<?php if ($shipping_address_1 || $shipping_address_2 || $shipping_city || $shipping_postcode || $shipping_state || $shipping_country) : ?>
						<div class="th">
							<p><b>Delivery Address</b></p>
						</div>
						<div class="td">
							<p>
								<?php if ($shipping_address_1) echo esc_html($shipping_address_1) . '<br>'; ?>
								<?php if ($shipping_address_2) echo esc_html($shipping_address_2) . '<br>'; ?>
								<?php if ($shipping_city) echo esc_html($shipping_city) . '<br>'; ?>
								<?php if ($shipping_postcode) echo esc_html($shipping_postcode) . '<br>'; ?>
								<?php if ($shipping_state) echo esc_html($shipping_state) . '<br>'; ?>
								<?php if ($shipping_country) echo esc_html($shipping_country); ?>
							</p>
						</div>
					<?php endif; ?>
				</div>
				<a class="open_modal" data-modal="3">
					Change Delivery Address
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
						<path d="M11.4755 3.04896H4.10567C3.54721 3.04896 3.01163 3.27081 2.61674 3.6657C2.22185 4.06059 2 4.59617 2 5.15463V19.8943C2 20.4528 2.22185 20.9884 2.61674 21.3833C3.01163 21.7782 3.54721 22 4.10567 22H18.8454C19.4038 22 19.9394 21.7782 20.3343 21.3833C20.7292 20.9884 20.951 20.4528 20.951 19.8943V12.5245M18.1873 2.65415C18.6062 2.2353 19.1743 2 19.7666 2C20.3589 2 20.927 2.2353 21.3459 2.65415C21.7647 3.07299 22 3.64107 22 4.2334C22 4.82574 21.7647 5.39381 21.3459 5.81265L11.8566 15.3029C11.6066 15.5527 11.2978 15.7355 10.9586 15.8346L7.93378 16.719C7.84319 16.7454 7.74715 16.747 7.65574 16.7236C7.56432 16.7001 7.48088 16.6526 7.41415 16.5859C7.34742 16.5191 7.29986 16.4357 7.27644 16.3443C7.25302 16.2528 7.2546 16.1568 7.28102 16.0662L8.1654 13.0414C8.26493 12.7025 8.44813 12.394 8.69814 12.1444L18.1873 2.65415Z" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</a>
			</div>
		</div>

		<div class="profile_data w-100">
			<div class="profile_data-row">
				<div class="table">
					<div class="th">
						<p><b>Email Address</b></p>
					</div>
					<div class="td">
						<p><?php echo esc_html($email); ?></p>
					</div>
				</div>
				<a class="open_modal" data-modal="1">
					Change Email Address
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
						<path d="M11.4755 3.04896H4.10567C3.54721 3.04896 3.01163 3.27081 2.61674 3.6657C2.22185 4.06059 2 4.59617 2 5.15463V19.8943C2 20.4528 2.22185 20.9884 2.61674 21.3833C3.01163 21.7782 3.54721 22 4.10567 22H18.8454C19.4038 22 19.9394 21.7782 20.3343 21.3833C20.7292 20.9884 20.951 20.4528 20.951 19.8943V12.5245M18.1873 2.65415C18.6062 2.2353 19.1743 2 19.7666 2C20.3589 2 20.927 2.2353 21.3459 2.65415C21.7647 3.07299 22 3.64107 22 4.2334C22 4.82574 21.7647 5.39381 21.3459 5.81265L11.8566 15.3029C11.6066 15.5527 11.2978 15.7355 10.9586 15.8346L7.93378 16.719C7.84319 16.7454 7.74715 16.747 7.65574 16.7236C7.56432 16.7001 7.48088 16.6526 7.41415 16.5859C7.34742 16.5191 7.29986 16.4357 7.27644 16.3443C7.25302 16.2528 7.2546 16.1568 7.28102 16.0662L8.1654 13.0414C8.26493 12.7025 8.44813 12.394 8.69814 12.1444L18.1873 2.65415Z" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</a>
			</div>

			<div class="profile_data-row">
				<div class="table">
					<div class="th">
						<p><b>Password</b></p>
					</div>
					<div class="td">
						<a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-account')); ?>" class="open_modal" data-modal="2">
							Change My Password
							<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
								<path d="M11.4755 3.04896H4.10567C3.54721 3.04896 3.01163 3.27081 2.61674 3.6657C2.22185 4.06059 2 4.59617 2 5.15463V19.8943C2 20.4528 2.22185 20.9884 2.61674 21.3833C3.01163 21.7782 3.54721 22 4.10567 22H18.8454C19.4038 22 19.9394 21.7782 20.3343 21.3833C20.7292 20.9884 20.951 20.4528 20.951 19.8943V12.5245M18.1873 2.65415C18.6062 2.2353 19.1743 2 19.7666 2C20.3589 2 20.927 2.2353 21.3459 2.65415C21.7647 3.07299 22 3.64107 22 4.2334C22 4.82574 21.7647 5.39381 21.3459 5.81265L11.8566 15.3029C11.6066 15.5527 11.2978 15.7355 10.9586 15.8346L7.93378 16.719C7.84319 16.7454 7.74715 16.747 7.65574 16.7236C7.56432 16.7001 7.48088 16.6526 7.41415 16.5859C7.34742 16.5191 7.29986 16.4357 7.27644 16.3443C7.25302 16.2528 7.2546 16.1568 7.28102 16.0662L8.1654 13.0414C8.26493 12.7025 8.44813 12.394 8.69814 12.1444L18.1873 2.65415Z" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
							</svg>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>

<!-- ----------------------------------- -->

<div class="modal" data-id="1">
	<div class="modal_bg close_modal"></div>
	<div class="modal_box">
		<div class="modal_box-header">
			<h4>Change My Details</h4>
			<button type="button" class="close_modal">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
					<path d="M4.0804 18.9241L18.9296 4.07491M4.08061 4.07564L18.9299 18.9249" stroke="#8C6E47" stroke-width="2" />
				</svg>
			</button>
		</div>
		<div class="modal_box-body">
			<form class="woocommerce-EditAccountForm edit-account" action="" method="post" <?php do_action('woocommerce_edit_account_form_tag'); ?>>

				<?php do_action('woocommerce_edit_account_form_start'); ?>

				<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
					<label for="account_first_name"><?php esc_html_e('First name', 'woocommerce'); ?><span class="required" aria-hidden="true">*</span></label>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr($user->first_name); ?>" aria-required="true" />
				</p>
				<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
					<label for="account_last_name"><?php esc_html_e('Last name', 'woocommerce'); ?><span class="required" aria-hidden="true">*</span></label>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_last_name" id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr($user->last_name); ?>" aria-required="true" />
				</p>
				<div class="clear"></div>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="account_display_name"><?php esc_html_e('Display name', 'woocommerce'); ?><span class="required" aria-hidden="true">*</span></label>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_display_name" id="account_display_name" aria-describedby="account_display_name_description" value="<?php echo esc_attr($user->display_name); ?>" aria-required="true" /> <span id="account_display_name_description"><em><?php esc_html_e('This will be how your name will be displayed in the account section and in reviews', 'woocommerce'); ?></em></span>
				</p>
				<div class="clear"></div>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="account_email"><?php esc_html_e('Email address', 'woocommerce'); ?><span class="required" aria-hidden="true">*</span></label>
					<input type="email" class="woocommerce-Input woocommerce-Input--email input-text" name="account_email" id="account_email" autocomplete="email" value="<?php echo esc_attr($user->user_email); ?>" aria-required="true" />
				</p>

				<?php
				/**
				 * Hook where additional fields should be rendered.
				 *
				 * @since 8.7.0
				 */
				do_action('woocommerce_edit_account_form_fields');
				?>

				<div class="clear"></div>

				<?php
				/**
				 * My Account edit account form.
				 *
				 * @since 2.6.0
				 */
				do_action('woocommerce_edit_account_form');
				?>

				<div class="w-100 modal_box-footer">
					<button type="button" class="cancel close_modal">Cancel</button>

					<?php wp_nonce_field('save_account_details', 'save-account-details-nonce'); ?>
					<button type="submit" class="woocommerce-Button button<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>" name="save_account_details" value="<?php esc_attr_e('Save changes', 'woocommerce'); ?>"><?php esc_html_e('Save changes', 'woocommerce'); ?></button>
					<input type="hidden" name="action" value="save_account_details" />
				</div>

				<?php do_action('woocommerce_edit_account_form_end'); ?>
			</form>
		</div>
	</div>
</div>

<div class="modal" data-id="2">
	<div class="modal_bg close_modal"></div>
	<div class="modal_box">
		<div class="modal_box-header">
			<h4>Change My Password</h4>
			<button type="button" class="close_modal">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
					<path d="M4.0804 18.9241L18.9296 4.07491M4.08061 4.07564L18.9299 18.9249" stroke="#8C6E47" stroke-width="2" />
				</svg>
			</button>
		</div>
		<div class="modal_box-body">
			<form class="woocommerce-EditAccountForm edit-account" action="" method="post" <?php do_action('woocommerce_edit_account_form_tag'); ?>>

				<?php do_action('woocommerce_edit_account_form_start'); ?>

				<?php
				/**
				 * Hook where additional fields should be rendered.
				 *
				 * @since 8.7.0
				 */
				do_action('woocommerce_edit_account_form_fields');
				?>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide span-2">
					<label for="password_current"><?php esc_html_e('Current password (leave blank to leave unchanged)', 'woocommerce'); ?></label>
					<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_current" id="password_current" autocomplete="off" />
				</p>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide span-2">
					<label for="password_1"><?php esc_html_e('New password (leave blank to leave unchanged)', 'woocommerce'); ?></label>
					<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_1" id="password_1" autocomplete="off" />
				</p>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide span-2">
					<label for="password_2"><?php esc_html_e('Confirm new password', 'woocommerce'); ?></label>
					<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_2" id="password_2" autocomplete="off" />
				</p>
				<div class="clear"></div>

				<?php
				/**
				 * My Account edit account form.
				 *
				 * @since 2.6.0
				 */
				do_action('woocommerce_edit_account_form');
				?>

				<div class="w-100 modal_box-footer">
					<button type="button" class="cancel close_modal">Cancel</button>
					<?php wp_nonce_field('save_account_details', 'save-account-details-nonce'); ?>
					<button type="submit" class="woocommerce-Button button<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>" name="save_account_details" value="<?php esc_attr_e('Save changes', 'woocommerce'); ?>"><?php esc_html_e('Save changes', 'woocommerce'); ?></button>
					<input type="hidden" name="action" value="save_account_details" />
				</div>

				<?php do_action('woocommerce_edit_account_form_end'); ?>
			</form>
		</div>
	</div>
</div>

<?php do_action('woocommerce_after_edit_account_form'); ?>




<?php
function build_address_fields_for_user($load_address, $customer_id)
{
	// billing_country o shipping_country del usuario; si no hay, usa el país base.
	$meta_country_key = $load_address . '_country';
	$country = get_user_meta($customer_id, $meta_country_key, true);
	if (empty($country)) {
		$base = wc_get_base_location();
		$country = isset($base['country']) ? $base['country'] : '';
	}

	// Prefijo requerido: "billing_" o "shipping_"
	$address = WC()->countries->get_address_fields($country, $load_address . '_');

	// Rellenar valores desde user_meta
	foreach ($address as $key => $field) {
		$saved = get_user_meta($customer_id, $key, true);
		if ('' !== $saved && null !== $saved) {
			$address[$key]['value'] = $saved;
		}
	}

	return $address;
}

$customer_id = get_current_user_id();
?>

<!-- Modal de direcciones (sin tu <h5> para evitar duplicado) -->
<div class="modal" data-id="3">
	<div class="modal_bg close_modal"></div>
	<div class="modal_box">
		<div class="modal_box-header">
			<h4>Change Delivery Address</h4>
			<button type="button" class="close_modal">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
					<path d="M4.0804 18.9241L18.9296 4.07491M4.08061 4.07564L18.9299 18.9249" stroke="#8C6E47" stroke-width="2" />
				</svg>
			</button>
		</div>
		<div class="modal_box-body">

			<?php
			// BILLING
			/*wc_get_template(
				'myaccount/form-edit-address.php',
				array(
					'load_address' => 'billing',
					'address'      => build_address_fields_for_user('billing', $customer_id),
				)
			);*/
			?>

			<?php
			// SHIPPING
			wc_get_template(
				'myaccount/form-edit-address.php',
				array(
					'load_address' => 'shipping',
					'address'      => build_address_fields_for_user('shipping', $customer_id),
				)
			);
			?>

		</div>
	</div>
</div>







<script>
	let open_modal = document.querySelectorAll('.open_modal'),
		close_modal = document.querySelectorAll('.close_modal');

	if (open_modal) {
		Array.from(open_modal).forEach(open => {
			open.addEventListener('click', (e) => {
				e.preventDefault();
				let id = e.currentTarget.getAttribute('data-modal'),
					modal = document.querySelector(`.modal[data-id="${id}"]`);


				if (!modal.classList.contains('active')) {
					modal.classList.add('active')
				}
			})
		})
	}

	if (close_modal) {

		Array.from(close_modal).forEach(close => {
			close.addEventListener('click', (e) => {
				e.preventDefault();
				let modal = e.currentTarget.closest('.modal');
				if (modal.classList.contains('active')) {
					modal.classList.remove('active')
				}
			})
		})

	}
</script>