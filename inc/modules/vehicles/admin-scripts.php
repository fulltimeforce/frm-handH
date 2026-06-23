<?php
if (!defined('ABSPATH')) {
  exit;
}

add_action(
  'admin_enqueue_scripts',
  function ($hook) {
    // Solo pantalla de edición/creación de posts
    if ($hook !== 'post.php' && $hook !== 'post-new.php')
      return;

    $screen = get_current_screen();
    if (!$screen)
      return;

    // Solo tu CPT vehicles
    if ($screen->post_type !== 'vehicles')
      return;

    // ACF debe existir
    if (!function_exists('acf'))
      return;

    wp_enqueue_script(
      'vehicles-acf-gallery-bulk-actions',
      URL . '/public/js/admin/vehicles/gallery-bulk-actions.js',
      ['acf-input'], // importante: depende de acf-input
      '1.0.0',
      true
    );
    wp_enqueue_script(
      'vehicles-acf-gallery-bulk-select',
      URL . '/public/js/admin/vehicles/acf-gallery-bulk-select.js',
      ['acf-input'], // importante: depende de acf-input
      THEME_VERSION,
      true
    );
    wp_enqueue_script(
      'vehicles-field-sync',
      URL . '/public/js/admin/vehicles/field-sync.js',
      ['acf-input'], // importante: depende de acf-input
      THEME_VERSION,
      true
    );
  }
);