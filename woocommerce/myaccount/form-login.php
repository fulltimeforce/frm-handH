<?php

/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-login.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.9.0
 */

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

do_action('woocommerce_before_customer_login_form'); ?>

<?php if ('yes' === get_option('woocommerce_enable_myaccount_registration')) : ?>

	<div class="u-columns col2-set" id="customer_login" data-scene="1">

		<div class="u-column1 col-1">

		<?php endif; ?>

		<div class="myaccount_page-title">
			<h1><?php esc_html_e('Sign in to your Account', 'woocommerce'); ?></h1>
			<p>Bid online directly with H&H.</p>
		</div>

		<form class="woocommerce-form woocommerce-form-login login" method="post" novalidate>

			<div class="woocommerce-form-fields">
				<?php do_action('woocommerce_login_form_start'); ?>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="username"><?php esc_html_e('Email', 'woocommerce'); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e('Required', 'woocommerce'); ?></span></label>
					<input placeholder="<?php esc_html_e('Email', 'woocommerce'); ?>" type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo (! empty($_POST['username']) && is_string($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>" required aria-required="true" /><?php // @codingStandardsIgnoreLine 
																																																																																															?>
				</p>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="password"><?php esc_html_e('Password', 'woocommerce'); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e('Required', 'woocommerce'); ?></span></label>
					<input placeholder="<?php esc_html_e('Password', 'woocommerce'); ?>" class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" required aria-required="true" />
					<a class="lost_password" href="<?php echo esc_url(wp_lostpassword_url()); ?>"><?php esc_html_e('Forgot Password?', 'woocommerce'); ?></a>
				</p>

				<?php do_action('woocommerce_login_form'); ?>
			</div>

			<div class="form-action">
				<?php if (false): ?>
					<label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
						<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span><?php esc_html_e('Remember me', 'woocommerce'); ?></span>
					</label>
				<?php endif; ?>
				<?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
				<button type="submit" class="woocommerce-button button woocommerce-form-login__submit<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>" name="login" value="<?php esc_attr_e('Log in', 'woocommerce'); ?>">
					<?php esc_html_e('Sign in', 'woocommerce'); ?>
					<svg xmlns="http://www.w3.org/2000/svg" width="26" height="14" viewBox="0 0 26 14" fill="none">
						<path d="M0.5 7H24.5M24.5 7L18.5 1M24.5 7L18.5 13" stroke="white" />
					</svg>
				</button>
				<a class="change_scene" data-id="2">Create an account</a>
			</div>

			<?php do_action('woocommerce_login_form_end'); ?>

		</form>

		<?php if ('yes' === get_option('woocommerce_enable_myaccount_registration')) : ?>

		</div>

		<div class="u-column2 col-2">

			<div class="myaccount_page-title">
				<h1><?php esc_html_e('Register An Account', 'woocommerce'); ?></h1>
				<p>Registering an account with us is quick, easy and safe. It is also the first step in registering to bid for one of our auctions. Enter your details below to get started.</p>
			</div>

			<form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action('woocommerce_register_form_tag'); ?>>

				<div class="woocommerce-form-fields">
					<?php do_action('woocommerce_register_form_start'); ?>

					<?php //if ('no' === get_option('woocommerce_registration_generate_username')) : 
					?>

					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="reg_username"><?php esc_html_e('Username', 'woocommerce'); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e('Required', 'woocommerce'); ?></span></label>
						<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo (! empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>" required aria-required="true" /><?php // @codingStandardsIgnoreLine 
																																																																										?>
					</p>

					<?php //endif; 
					?>

					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="reg_email"><?php esc_html_e('Email address', 'woocommerce'); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e('Required', 'woocommerce'); ?></span></label>
						<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo (! empty($_POST['email'])) ? esc_attr(wp_unslash($_POST['email'])) : ''; ?>" required aria-required="true" /><?php // @codingStandardsIgnoreLine 
																																																																							?>
					</p>

					<?php if ('no' === get_option('woocommerce_registration_generate_password')) : ?>

						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="reg_password"><?php esc_html_e('Password', 'woocommerce'); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e('Required', 'woocommerce'); ?></span></label>
							<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" required aria-required="true" />
						</p>

					<?php else : ?>

						<p><?php esc_html_e('A link to set a new password will be sent to your email address.', 'woocommerce'); ?></p>

					<?php endif; ?>

					<?php do_action('woocommerce_register_form'); ?>
				</div>

				<div class="woocommerce-form-row form-action">
					<?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
					<button type="submit" class="woocommerce-Button woocommerce-button button<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?> woocommerce-form-register__submit" name="register" value="<?php esc_attr_e('Register', 'woocommerce'); ?>">
						<?php esc_html_e('Register Account', 'woocommerce'); ?>
						<svg xmlns="http://www.w3.org/2000/svg" width="26" height="14" viewBox="0 0 26 14" fill="none">
							<path d="M0.5 7H24.5M24.5 7L18.5 1M24.5 7L18.5 13" stroke="white" />
						</svg>
					</button>
					<a class="change_scene" data-id="1">Already have an account? Sign in</a>
				</div>

				<?php do_action('woocommerce_register_form_end'); ?>

			</form>

		</div>

	</div>
<?php endif; ?>

<?php do_action('woocommerce_after_customer_login_form'); ?>

<script>
	document.querySelectorAll('.change_scene').forEach(btn => {
		btn.addEventListener('click', e => {
			e.preventDefault();
			document.querySelector('#customer_login')
				.setAttribute('data-scene', btn.dataset.id);
			window.scrollTo({
				top: 0,
				behavior: 'smooth'
			});
		});
	});
</script>