<?php
if (!defined('ABSPATH')) {
  exit;
}

// Admin search: Auctions -> title OR ACF `sale_number`
add_filter(
  'posts_search',
  'hnh_admin_auction_search_title_or_sale_number',
  10,
  2
);

function hnh_admin_auction_search_title_or_sale_number(
  $search,
  $wp_query
) {
  global $wpdb;

  // Solo en admin, query principal y para el CPT 'auction'
  if (!is_admin() || !$wp_query->is_main_query())
    return $search;
  if ($wp_query->get('post_type') !== 'auction')
    return $search;

  $s = $wp_query->get('s');
  if ($s === null || $s === '')
    return $search;

  // Construye condiciones
  $like = '%' . $wpdb->esc_like($s) . '%';
  $numeric = is_numeric($s) ? (int) $s : null;

  $where_parts = [];

  // Título del post
  $where_parts[] = $wpdb->prepare("{$wpdb->posts}.post_title LIKE %s", $like);

  // Meta ACF sale_number (búsqueda parcial)
  $where_parts[] = $wpdb->prepare(
    "EXISTS (
            SELECT 1 FROM {$wpdb->postmeta} pm
            WHERE pm.post_id = {$wpdb->posts}.ID
              AND pm.meta_key = 'sale_number'
              AND pm.meta_value LIKE %s
        )",
    $like
  );

  // Si la búsqueda es numérica, también prueba igualdad exacta (más precisa)
  if ($numeric !== null) {
    $where_parts[] = $wpdb->prepare(
      "EXISTS (
                SELECT 1 FROM {$wpdb->postmeta} pm2
                WHERE pm2.post_id = {$wpdb->posts}.ID
                  AND pm2.meta_key = 'sale_number'
                  AND CAST(pm2.meta_value AS UNSIGNED) = %d
            )",
      $numeric
    );
  }

  // Reemplaza la cláusula de búsqueda por la nuestra (solo título + sale_number)
  $search = ' AND (' . implode(' OR ', $where_parts) . ') ';

  return $search;
}