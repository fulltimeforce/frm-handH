<?php
if (!defined('ABSPATH')) {
  exit;
}
add_action('wp_enqueue_scripts', 'enqueue_assets');
// add_filter('script_loader_tag', 'script_as_module', 10, 3);
function enqueue_assets(): void
{
  enqueue_base_assets();
  enqueue_accordion_assets();
}

function enqueue_base_assets(): void
{
  wp_enqueue_style(
    'main-css',
    get_template_directory_uri() . '/public/css/app.min.css',
    [],
    THEME_VERSION,
    'all'
  );

  wp_enqueue_style(
    'style',
    get_stylesheet_uri(),
    [],
    THEME_VERSION,
    'all'
  );

  wp_enqueue_style(
    'custom-css',
    get_template_directory_uri() . '/public/css/custom.css',
    ['main-css'],
    THEME_VERSION,
    'all'
  );

  wp_enqueue_script(
    'main-js',
    get_template_directory_uri() . '/public/js/main.min.js',
    [],
    THEME_VERSION,
    true
  );

  // Este lo vamos a transformar a type="module" con el filtro
  wp_enqueue_script(
    'custom-js',
    get_template_directory_uri() . '/public/js/custom.js',
    [],
    THEME_VERSION,
    true
  );

  wp_enqueue_script(
    'events-slider-js',
    get_template_directory_uri() . '/public/js/pages/events/index.js',
    ['main-js'],
    filemtime(get_template_directory() . '/public/js/pages/events/index.js'),
    true
  );
}

function enqueue_accordion_assets(): void
{
  if (!should_load_accordion_assets()) {
    return;
  }

  wp_enqueue_style(
    'accordioncss',
    CSS . '/accordion.css',
    [],
    THEME_VERSION,
    'all'
  );
  wp_enqueue_script(
    'jquerycustom',
    JS . '/jquery.min.js',
    [],
    THEME_VERSION,
    true
  );
  wp_enqueue_script(
    'accordionjs',
    JS . '/accordion.min.js',
    ['jquery', 'jquerycustom'],
    THEME_VERSION,
    true
  );
}

function should_load_accordion_assets(): bool
{
  $pages = [
    609,
    210,
    228,
    298,
    280,
    302,
    300,
  ];

  $post_types = [
    'vehicles',
    'auction',
    'model',
    'make'
  ];

  return is_page($pages) || is_singular($post_types);
}

// function script_as_module(string $tag, string $handle, string $src): string
// {
//   if ($handle !== 'custom-js') {
//     return $tag;
//   }

//   // Genera: <script type="module" src="..."></script>
//   return sprintf(
//     '<script type="module" src="%s" id="%s-js"></script>' . "\n",
//     esc_url($src),
//     esc_attr($handle)
//   );
// }
