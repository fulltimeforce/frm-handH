<?php
if (!defined('ABSPATH')) {
  exit;
}

add_filter(
  'export_post_type_enabled',
  'hide_custom_post_types_from_export',
  10,
  2
);
add_filter(
  'register_post_type_args',
  'disable_can_export_for_custom_post_types',
  10,
  2
);

function hide_custom_post_types_from_export($enabled, $post_type)
{
  $remove = ['vehicles', 'team', 'venues', 'models'];

  if (in_array($post_type, $remove)) {
    return false; // los oculta de la pantalla Export
  }

  return $enabled;
}

function disable_can_export_for_custom_post_types($args, $post_type)
{

  // Slugs de los CPT que quieres ocultar en Export.
  $to_hide = array(
    'vehicles',
    'team',
    'venue',
    'testimonials',
    'auction',
    'model',
  );

  if (in_array($post_type, $to_hide, true)) {
    $args['can_export'] = false;
  }

  return $args;
}