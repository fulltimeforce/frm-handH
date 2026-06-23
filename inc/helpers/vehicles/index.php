<?php

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Contexto de navegación del listado de auction (sort + filtros).
 */
function hnh_get_auction_list_nav_context_from_request(): array
{
  $order_by = isset($_GET['order_by']) ? sanitize_text_field(wp_unslash($_GET['order_by'])) : 'lot';
  $allowed_order = ['lot', 'low-to-high', 'high-to-low'];

  if (!in_array($order_by, $allowed_order, true)) {
    $order_by = 'lot';
  }

  return [
    'order_by'           => $order_by,
    'search_vehicle'     => isset($_GET['search_vehicle']) ? sanitize_text_field(wp_unslash($_GET['search_vehicle'])) : '',
    'make_id'            => isset($_GET['make_id']) ? (int) $_GET['make_id'] : 0,
    'vehicle_categories' => isset($_GET['vehicle_categories']) ? (int) $_GET['vehicle_categories'] : 0,
  ];
}

/**
 * Query args para propagar en URLs (omite valores vacíos y orden por defecto).
 */
function hnh_auction_list_nav_query_args(array $context): array
{
  $args = [];

  $order_by = $context['order_by'] ?? 'lot';
  if ($order_by !== '' && $order_by !== 'lot') {
    $args['order_by'] = $order_by;
  }

  if (!empty($context['search_vehicle'])) {
    $args['search_vehicle'] = $context['search_vehicle'];
  }

  if (!empty($context['make_id'])) {
    $args['make_id'] = (int) $context['make_id'];
  }

  if (!empty($context['vehicle_categories'])) {
    $args['vehicle_categories'] = (int) $context['vehicle_categories'];
  }

  return $args;
}

/**
 * IDs de vehículos del auction con el mismo orden/filtros que el listado.
 */
function hnh_get_auction_list_vehicle_ids(int $auction_id, array $context = []): array
{
  if ($auction_id <= 0) {
    return [];
  }

  if (!class_exists('VehiclesSearchRepository')) {
    require_once get_template_directory() . '/VehiclesSearchRepository.php';
  }

  global $wpdb;
  $repo = new VehiclesSearchRepository($wpdb);

  $allowed_order = ['lot', 'low-to-high', 'high-to-low'];
  $order_by = $context['order_by'] ?? 'lot';
  if (!in_array($order_by, $allowed_order, true)) {
    $order_by = 'lot';
  }

  $per_page = 200;
  $page = 1;
  $ids = [];

  do {
    $result = $repo->search([
      'auction_id'  => $auction_id,
      'q'           => (string) ($context['search_vehicle'] ?? ''),
      'order_by'    => $order_by,
      'make_id'     => (int) ($context['make_id'] ?? 0),
      'category_id' => (int) ($context['vehicle_categories'] ?? 0),
      'per_page'    => $per_page,
      'page'        => $page,
      'debug'       => false,
    ]);

    foreach ($result['items'] as $row) {
      $ids[] = (int) $row['vehicle_id'];
    }

    $total = (int) ($result['total'] ?? 0);
    $page++;
  } while (count($ids) < $total && !empty($result['items']));

  return $ids;
}

/**
 * Devuelve el ID del vehículo anterior o siguiente según el orden del listado del auction.
 *
 * @param int    $vehicle_id  ID del vehículo actual.
 * @param int    $auction_id  ID del auction (auction_number_latest).
 * @param string $direction   'prev' | 'next'.
 * @param array  $context     Sort/filtros del listado (order_by, search_vehicle, make_id, vehicle_categories).
 * @return int 0 si no hay adyacente.
 */
function hnh_get_adjacent_lot_vehicle_id(int $vehicle_id, int $auction_id, string $direction = 'next', array $context = []): int
{
  if ($vehicle_id <= 0 || $auction_id <= 0) {
    return 0;
  }

  $ids = hnh_get_auction_list_vehicle_ids($auction_id, $context);

  if (empty($ids)) {
    return 0;
  }

  $index = array_search($vehicle_id, $ids, true);
  if ($index === false) {
    return 0;
  }

  if ($direction === 'prev') {
    return (int) ($ids[$index - 1] ?? 0);
  }

  return (int) ($ids[$index + 1] ?? 0);
}

/**
 * Permalink de vehículo con contexto de listado del auction.
 */
function hnh_vehicle_permalink_with_list_context(int $vehicle_id, array $list_context = []): string
{
  $permalink = get_permalink($vehicle_id);

  if (!$permalink || empty($list_context)) {
    return (string) $permalink;
  }

  $query_args = hnh_auction_list_nav_query_args($list_context);

  if (empty($query_args)) {
    return (string) $permalink;
  }

  return (string) add_query_arg($query_args, $permalink);
}
