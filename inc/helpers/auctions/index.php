<?php

if (!defined('ABSPATH')) {
  exit;
}
function hnh_get_today_start_datetime(): string
{
  $today = current_time('Y-m-d');
  return $today . ' 00:00:00';
}

function hnh_get_upcoming_auctions_query(array $overrides = []): WP_Query
{
  $defaults = [
    'post_type' => 'auction',
    'posts_per_page' => -1,
    'orderby' => 'meta_value',
    'order' => 'ASC',
    'meta_key' => 'auction_date',
    'meta_type' => 'DATETIME',
    'meta_query' => [
      [
        'key' => 'auction_date',
        'value' => hnh_get_today_start_datetime(),
        'compare' => '>=',
        'type' => 'DATETIME',
      ],
    ],
  ];

  return new WP_Query(wp_parse_args($overrides, $defaults));
}

/**
 * Obtiene y sanitiza los filtros de subasta desde parámetros GET
 *
 * @return array Array asociativo con valores de filtros sanitizados
 */
function hnh_get_auction_filters(): array
{
  $currentYear = (int) date('Y');
  
  $filters = [
    'paged' => max(
      1,
      get_query_var('paged') ? (int) get_query_var('paged') : (int) get_query_var('page')
    ),
    'posts_per_page' => isset($_GET['posts_per_page']) ? max(1, (int) $_GET['posts_per_page']) : 6,
    'sale_type' => isset($_GET['sale_type']) ? sanitize_text_field($_GET['sale_type']) : 'all',
    'auction_year' => isset($_GET['auction_year']) ? sanitize_text_field($_GET['auction_year']) : (string) $currentYear,
  ];

  // Validar sale_type contra lista blanca
  $allowed_sale_types = ['all', 'motorcars', 'motorcycles', 'automobilia', 'bicycles', 'liveonline'];
  if (!in_array($filters['sale_type'], $allowed_sale_types, true)) {
    $filters['sale_type'] = 'all';
  }

  return $filters;
}

/**
 * Obtiene query de subastas pasadas con filtros
 *
 * @param array $filters Filtros opcionales (paged, posts_per_page, sale_type, auction_year)
 * @param array $overrides Argumentos adicionales de WP_Query para sobrescribir valores predeterminados
 * @return WP_Query
 */
function hnh_get_past_auctions_query(array $filters = [], array $overrides = []): WP_Query
{
  // Si no se pasan filtros, obtenerlos de GET
  if (empty($filters)) {
    $filters = hnh_get_auction_filters();
  }

  $today_start = hnh_get_today_start_datetime();

  // Meta query base: solo fechas pasadas
  $meta_query = [
    'relation' => 'AND',
    [
      'key' => 'auction_date',
      'value' => $today_start,
      'compare' => '<',
      'type' => 'DATETIME',
    ],
  ];

  // Filtro por año
  if (
    !empty($filters['auction_year']) &&
    $filters['auction_year'] !== 'all' &&
    ctype_digit($filters['auction_year'])
  ) {
    $y = (int) $filters['auction_year'];
    $start_of_year = sprintf('%04d-01-01 00:00:00', $y);
    $end_of_year = sprintf('%04d-12-31 23:59:59', $y);

    $meta_query[] = [
      'key' => 'auction_date',
      'value' => [$start_of_year, $end_of_year],
      'compare' => 'BETWEEN',
      'type' => 'DATETIME',
    ];
  }

  // Filtro por sale_type
  if (!empty($filters['sale_type']) && $filters['sale_type'] !== 'all') {
    $meta_query[] = [
      'key' => 'sale_type',
      'value' => $filters['sale_type'],
      'compare' => '=',
    ];
  }

  $defaults = [
    'post_type' => 'auction',
    'posts_per_page' => $filters['posts_per_page'] ?? 6,
    'paged' => $filters['paged'] ?? 1,
    'orderby' => 'meta_value',
    'order' => 'DESC',
    'meta_key' => 'auction_date',
    'meta_type' => 'DATETIME',
    'meta_query' => $meta_query,
  ];

  return new WP_Query(wp_parse_args($overrides, $defaults));
}

function hnh_cleanup_old_runs_by_age(int $minutes = 0): void
{
    if (!function_exists('wp_upload_dir')) {
        return;
    }

    $uploads = wp_upload_dir();

    $base = trailingslashit($uploads['basedir']) . 'hnh-bulk-images';

    if (!is_dir($base)) {
        return;
    }

    $limit = time() - ($minutes * 60);

    $items = scandir($base);
    if (!$items) {
        return;
    }

    foreach ($items as $item) {

        if ($item === '.' || $item === '..') {
            continue;
        }

        if (strpos($item, 'run-') !== 0) {
            continue;
        }

        $path = $base . '/' . $item;

        if (!is_dir($path)) {
            continue;
        }

        $modified = filemtime($path);
        if (!$modified) {
            continue;
        }

        if ($modified < $limit) {
            hnh_delete_directory_recursive($path);
        }
    }
}

function hnh_delete_directory_recursive(string $dir): void
{
    if (realpath($dir) === false) {
        return;
    }

    $uploads = wp_upload_dir();
    $base = realpath(trailingslashit($uploads['basedir']) . 'hnh-bulk-images');

    $real = realpath($dir);

    if (!$real || $real === $base) {
        return;
    }

    if (!str_starts_with($real, $base)) {
        return;
    }

    if (!is_dir($real)) {
        return;
    }

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($real, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($items as $item) {
        if ($item->isDir()) {
            @rmdir($item->getRealPath());
        } else {
            @unlink($item->getRealPath());
        }
    }

    @rmdir($real);
}

add_action('admin_init', function () {

    if (
        !isset($_GET['post_type'], $_GET['page']) ||
        $_GET['post_type'] !== 'auction' ||
        $_GET['page'] !== 'hnh-bulk-images'
    ) {
        return;
    }

    hnh_cleanup_old_runs_by_age(1440); // 24h
});