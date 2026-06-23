<?php
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Añade la columna "Auction Date" al listado del CPT Auctions.
 */
add_filter(
  'manage_auction_posts_columns',
  'add_auction_date_column'
);

function add_auction_date_column($columns)
{
  // Inserta la columna después del título
  $new_columns = [];
  foreach ($columns as $key => $label) {
    $new_columns[$key] = $label;
    if ($key === 'title') {
      $new_columns['auction_date'] = __('Auction Date', 'textdomain');
    }
  }
  return $new_columns;
}

/**
 * Muestra el valor del campo ACF "auction_date" en la columna.
 */
add_action(
  'manage_auction_posts_custom_column',
  function ($column, $post_id) {
    if ($column === 'auction_date') {
      $date = get_field('auction_date', $post_id);
      echo $date ? esc_html($date) : '—';
    }
  },
  10,
  2
);

/**
 * Hace que la columna "Auction Date" sea ordenable.
 */
add_filter(
  'manage_edit-auction_sortable_columns',
  function ($columns) {
    $columns['auction_date'] = 'auction_date';
    return $columns;
  }
);

/**
 * Aplica el ordenamiento por el campo ACF "auction_date".
 * Además, establece que por defecto se muestren los más recientes primero.
 */
add_action(
  'pre_get_posts',
  function ($query) {
    if (!is_admin() || !$query->is_main_query()) {
      return;
    }

    if ($query->get('post_type') !== 'auction') {
      return;
    }

    // Si el usuario hace clic en la columna para ordenar
    if ($query->get('orderby') === 'auction_date') {
      $query->set('meta_key', 'auction_date');
      $query->set('orderby', 'meta_value');
      $query->set('meta_type', 'DATETIME');
    }

    // Si no hay orden definido, aplica el orden por defecto
    if (!$query->get('orderby')) {
      $query->set('meta_key', 'auction_date');
      $query->set('orderby', 'meta_value');
      $query->set('meta_type', 'DATETIME');
      $query->set('order', 'DESC'); // Más recientes primero
    }
  }
);

/**
 * Añade la columna "Auction Date" al listado del CPT Auctions.
 */
// add_filter('manage_auction_posts_columns', function ($columns) {
//     // Inserta la columna después del título
//     $new_columns = [];
//     foreach ($columns as $key => $label) {
//         $new_columns[$key] = $label;
//         if ($key === 'title') {
//             $new_columns['auction_date'] = __('Auction Date', 'textdomain');
//         }
//     }
//     return $new_columns;
// });