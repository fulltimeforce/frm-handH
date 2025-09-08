<?php

add_action('admin_menu', 'iv_script');

function iv_script()
{
	add_menu_page('HandH', 'Social', 'administrator', 'iv_script', 'iv_opciones', '', 20);
	add_action('admin_init', 'jqs_registrar_opciones_contacto');
}

function jqs_registrar_opciones_contacto()
{

	register_setting('koller_opciones_grupo_contacto', 'linkedin');
	register_setting('koller_opciones_grupo_contacto', 'facebook');
	register_setting('koller_opciones_grupo_contacto', 'instagram');
	register_setting('koller_opciones_grupo_contacto', 'x');
	register_setting('koller_opciones_grupo_contacto', 'youtube');
}

function iv_opciones()
{
?>
	<div class="wrap">
		<form method="post" action="options.php">
			<?php settings_fields('koller_opciones_grupo_contacto'); ?>
			<?php do_settings_sections('koller_opciones_grupo_contacto'); ?>
			<h1>Información de contacto</h1>
			<br>

			<table class="form-table">

				<tr valign="top">
					<th scope="row" style="padding:0 0 16px 0;vertical-align:middle;">LinkedIn</th>
					<td style="padding:0 0 16px 0"><input style="width: 100%;" type="text" name="linkedin" value="<?php echo get_option('linkedin'); ?>"></td>
				</tr>
				<tr valign="top">
					<th scope="row" style="padding:0 0 16px 0;vertical-align:middle;">Facebook</th>
					<td style="padding:0 0 16px 0"><input style="width: 100%;" type="text" name="facebook" value="<?php echo get_option('facebook'); ?>"></td>
				</tr>
				<tr valign="top">
					<th scope="row" style="padding:0 0 16px 0;vertical-align:middle;">Instagram</th>
					<td style="padding:0 0 16px 0"><input style="width: 100%;" type="text" name="instagram" value="<?php echo get_option('instagram'); ?>"></td>
				</tr>
				<tr valign="top">
					<th scope="row" style="padding:0 0 16px 0;vertical-align:middle;">X</th>
					<td style="padding:0 0 16px 0"><input style="width: 100%;" type="text" name="x" value="<?php echo get_option('x'); ?>"></td>
				</tr>
				<tr valign="top">
					<th scope="row" style="padding:0 0 16px 0;vertical-align:middle;">YouTube</th>
					<td style="padding:0 0 16px 0"><input style="width: 100%;" type="text" name="youtube" value="<?php echo get_option('youtube'); ?>"></td>
				</tr>

			</table>

			<br>
			<?php submit_button(); ?>
		</form>
	</div>
<?php
}
?>