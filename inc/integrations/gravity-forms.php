<?php
/**
 * Gravity Forms Customizations
 * - Botón de envío con iconos personalizados
 * - Wrapper de UI para campo de carga de archivos
 * - Enqueue de estilos en templates con shortcodes hardcodeados
 *
 * @package HandH
 */
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Los shortcodes de Gravity Forms en templates (no en post_content) no son detectados
 * por parse_forms(), así que los estilos foundation/framework nunca se encolan.
 * Forzamos el enqueue en wp_enqueue_scripts para templates que tienen forms.
 */
add_action('wp_enqueue_scripts', 'hnh_gform_enqueue_template_forms', 5);

function hnh_gform_enqueue_template_forms(): void
{
  if (is_admin() || !class_exists('GFAPI') || !class_exists('GFFormDisplay')) {
    return;
  }

  $form_ids = [];

  // Form 1: footer (todas las páginas)
  $form_ids[] = 1;

  // Forms en page templates
  if (is_page_template('page-template/vehicles-for-sale.php') ||
      is_page_template('page-template/buy-it-now.php') ||
      is_page_template('page-template/contact.php') ||
      is_page_template('page-template/vehicles-wanted.php')) {
    $form_ids[] = 2;
  }
  if (is_page_template('page-template/careers.php')) {
    $form_ids[] = 3;
  }
  if (is_page_template('page-template/get-a-valuation.php')) {
    $form_ids[] = 4;
  }
  if (is_page_template('page-template/insurance.php')) {
    $form_ids[] = 6;
  }
  if (is_page_template('page-template/our-services.php')) {
    $form_ids[] = 14;
  }
  if (is_page_template('page-template/other-forms.php')) {
    $form_ids[] = 8;
    $form_ids[] = 12;
    $form_ids[] = 13;
    $form_ids[] = 15;
    $form_ids[] = 16;
    $form_ids[] = 17;
  }

  $form_ids = array_unique($form_ids);
  $theme = class_exists('GFForms') ? GFForms::get_default_theme() : 'gravity-theme';

  foreach ($form_ids as $form_id) {
    $form = GFAPI::get_form((int) $form_id);
    if ($form && $form['is_active'] && empty($form['is_trash'])) {
      GFFormDisplay::enqueue_form_scripts($form, true, $theme);
    }
  }
}

// === Personalización del botón de envío ===
add_filter(
  'gform_submit_button',
  'hnh_custom_submit_button',
  10,
  2
);

// === Personalización del campo de carga de archivos ===
add_filter(
  'gform_field_content_3',
  'hnh_wrap_file_upload',
  10,
  5
);
add_filter(
  'gform_field_content_4',
  'hnh_wrap_gform_fileupload_field',
  10,
  5
);


function hnh_custom_submit_button(
  $button_html,
  $form
) {
  if (in_array((int) $form['id'], [2, 3, 4, 5, 14], true)) {
    // Extrae attrs del input original
    preg_match('/id="([^"]+)"/', $button_html, $mId);
    preg_match('/class="([^"]+)"/', $button_html, $mClass);
    preg_match('/onclick="([^"]+)"/', $button_html, $mOnclick);
    preg_match('/value="([^"]+)"/', $button_html, $mValue);

    $id = $mId[1] ?? '';
    $class = $mClass[1] ?? 'gform_button button';
    $onclick = hnh_gform_submit_onclick($mOnclick[1] ?? '');
    $label = $mValue[1] ?? __('Submit', 'gravityforms');

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

    $id = $mId[1] ?? '';
    $class = $mClass[1] ?? 'gform_button button';
    $onclick = hnh_gform_submit_onclick($mOnclick[1] ?? '');
    $label = $mValue[1] ?? __('Submit', 'gravityforms');

    // SVG (hereda color del texto)
    $svg = '<img src="' . IMG . '/arrow-brown.png" alt="arrow">';

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
}

function hnh_gform_submit_onclick(string $original_onclick = ''): string
{
  $handler = 'if(window.gform&&window.gform.submission){window.gform.submission.handleButtonClick(this);}';
  $onclick = trim($original_onclick);

  if ($onclick === '') {
    $onclick = $handler;
  } elseif (strpos($onclick, 'window.gform.submission.handleButtonClick') === false) {
    $onclick = rtrim($onclick, ';') . ';' . $handler;
  }

  return ' onclick="' . esc_attr($onclick) . '"';
}

function hnh_wrap_file_upload(
  $content,
  $field,
  $value,
  $entry_id,
  $form_id
) {
  if ((int) $field->id === 8 && $field->type === 'fileupload' && !is_admin()) {
    return '<div class="my-filewrap">'
      . $content .
      '<img src="' . IMG . '/upload.png">
            <p>Drag and drop files here to upload, or click to select.</p>
            <span class="browse_file">Browse File</span>
            <span class="file_upload_status" aria-live="polite"></span>
        </div>';
  }
  return $content;
}

function hnh_wrap_gform_fileupload_field(
  $content,
  $field,
  $value,
  $entry_id,
  $form_id
) {
  if ((int) $field->id === 8 && $field->type === 'fileupload' && !is_admin()) {
    return '<div class="my-filewrap">'
      . $content .
      '<img src="' . IMG . '/upload.png">
            <p>Drag and drop files here to upload, or click to select.</p>
            <span class="browse_file">Browse File</span>
            <span class="file_upload_status" aria-live="polite"></span>
        </div>';
  }
  return $content;
}

/*add_filter('gform_field_content_8', function ($content, $field, $value, $entry_id, $form_id) {
    if ((int) $field->id === 21 && $field->type === 'fileupload') {
        return '<div class="my-filewrap">'
            . $content .
            '<img src="' . IMG . '/upload.png">
            <p>Drag and drop files here to upload, or click to select.</p>
            <span class="browse_file">Browse File</span>
        </div>';
    }
    return $content;
}, 10, 5);

add_filter('gform_field_content_10', function ($content, $field, $value, $entry_id, $form_id) {
    if ((int) $field->id === 21 && $field->type === 'fileupload') {
        return '<div class="my-filewrap">'
            . $content .
            '<img src="' . IMG . '/upload.png">
            <p>Drag and drop files here to upload, or click to select.</p>
            <span class="browse_file">Browse File</span>
        </div>';
    }
    return $content;
}, 10, 5);

add_filter('gform_field_content_11', function ($content, $field, $value, $entry_id, $form_id) {
    if ((int) $field->id === 21 && $field->type === 'fileupload') {
        return '<div class="my-filewrap">'
            . $content .
            '<img src="' . IMG . '/upload.png">
            <p>Drag and drop files here to upload, or click to select.</p>
            <span class="browse_file">Browse File</span>
        </div>';
    }
    return $content;
}, 10, 5);*/
