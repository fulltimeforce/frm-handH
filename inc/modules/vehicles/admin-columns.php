<?php
if (!defined('ABSPATH')) {
  exit;
}

add_filter(
  'manage_vehicles_posts_columns',
  function ($cols) {
    $new = [];
    foreach ($cols as $k => $v) {
      $new[$k] = $v;
      if ($k === 'title') {
        $new['auction_date_latest'] = __('Auction Date', 'textdomain');
      }
    }
    return $new;
  }
);

add_action(
  'manage_vehicles_posts_custom_column',
  function ($col, $post_id) {
    if ($col !== 'auction_date_latest')
      return;
    $raw = get_post_meta($post_id, 'auction_date_latest', true); // ACF/meta key
    echo $raw ? esc_html($raw) : '—';
  },
  10,
  2
);

/**
 * Orden por defecto y ordenación desde cabecera para Vehicles
 * Campo ACF: auction_date_latest (formato 'Y-m-d H:i:s' o 'Y-m-d H:i')
 */
add_action('pre_get_posts', function ($query) {

  if (!is_admin() || !$query->is_main_query()) return;
  if ($query->get('post_type') !== 'vehicles') return;

  // ✅ Si el usuario clickea una columna (orderby viene en GET), respeta eso
  if (isset($_GET['orderby']) && $_GET['orderby'] !== '') {

    if ($_GET['orderby'] === 'auction_date_latest') {
      $query->set('meta_key', 'auction_date_latest');
      $query->set('meta_type', 'DATETIME');
      $query->set('orderby', 'meta_value');
      // respeta &order=ASC|DESC
    }

    return;
  }

  // ✅ Default: orden por menu_order (lo que usa Simple Custom Post Order)
  $query->set('orderby', 'menu_order');
  $query->set('order', 'ASC');
});

/**
 * Hacer la columna de fecha de subasta "sortable" (si tienes una columna para ello)
 * Cambia 'auction_date_latest' por el ID de la columna que uses en el listado.
 */
add_filter(
  'manage_edit-vehicles_sortable_columns',
  function ($columns) {
    // clave = ID de la columna; valor = 'orderby' que envia WP
    $columns['auction_date_latest'] = 'auction_date_latest';
    return $columns;
  }
);
