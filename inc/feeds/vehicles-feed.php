<?php
/**
 * Feed RSS personalizado para:
 * /vehicles/feed/
 * Interceptamos la carga de la página ANTES de que WordPress imprima contenido.
 */
if (!defined('ABSPATH')) {
  exit;
}

add_action(
  'template_redirect',
  'hnh_render_vehicles_feed',
  1
);

function hnh_render_vehicles_feed()
{
  // Debe ser un feed para continuar.
  if (!is_feed()) {
    return;
  }

  // Obtenemos el path actual ("vehicles/feed")
  $request_path = hnh_get_request_path();

  // Si no es la misma URL, salimos.
  if ($request_path !== 'vehicles/feed') {
    return;
  }

  // Traemos los vehicles
  $query = hnh_build_vehicles_feed_query();

  // Hasta aqui todo bien
  status_header(200);

  // Evita que el navegador “guarde” el feed en caché (por defecto)
  nocache_headers();

  /**
   * - WordPress genera el RSS usando el "query global" ($wp_query).
   * - Entonces reemplazamos el query global por el nuestro.
   */
  $GLOBALS['wp_query'] = $query;

  add_action('rss2_head', 'hnh_feed_paging_links');

  // Imprime el feed RSS2 ahora mismo con el query que ya pusimos.
  do_feed_rss2(false);

  exit;
}
/**
 * Devuelve el "path" de la URL actual sin dominio.
 */
function hnh_get_request_path()
{
  $path = '';

  if (isset($_SERVER['REQUEST_URI'])) {
    // Tomamos solo la parte del path, sin query string
    $path = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    // Quitamos "/" al inicio y final
    $path = trim($path, '/');
  }

  return $path;
}
/**
 * Crea el WP_Query para el feed de vehicles.
 *
 * - $items_per_page: cuántos items por página (en feeds suele ser 20-100).
 * - $max_page: "freno" para evitar que alguien pida paginados absurdos (seguridad).
 * - $page: página actual (sale de ?paged=)
 */
function hnh_build_vehicles_feed_query()
{
  // $items_per_page = 2;
  $items_per_page = 200;
  $max_page = 500;     // Límite máximo de páginas permitidas
  $page = hnh_get_feed_page($max_page); // Obtiene la página actual (1,2,3...)

  return new WP_Query([
    'post_type' => 'vehicles',
    'post_status' => 'publish',
    'posts_per_page' => $items_per_page,
    'paged' => $page, // Paginación    
    'orderby' => 'date',
    'order' => 'DESC',
    'no_found_rows' => true,
    'ignore_sticky_posts' => true,
    'update_post_meta_cache' => false,
    'update_post_term_cache' => false,
  ]);
}
/**
 * Lee la página actual desde la URL con la convención:
 * /vehicles/feed/?paged=2  
 */
function hnh_get_feed_page($max_page)
{
  $page = 1;

  // Si existe ?paged= en la URL, lo usamos
  if (isset($_GET['paged'])) {
    // absint: lo convierte a entero positivo (seguro)
    $page = absint($_GET['paged']);
  }

  // Mínimo 1
  if ($page < 1) {
    $page = 1;
  }

  // Máximo el freno que definimos
  if ($page > $max_page) {
    $page = $max_page;
  }

  return $page;
}
/**
 * Agrega links de paginación en el <head> del RSS (rss2_head).
 * 
 * La feed mostrará:
 * - cuál es la siguiente página
 * - cuál es la anterior 
 */
function hnh_feed_paging_links()
{
  $current = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
  $base = home_url('/vehicles/feed/');

  // Link a la siguiente página
  $next = add_query_arg('paged', $current + 1, $base);
  echo "\n" . '<atom:link rel="next" type="application/rss+xml" href="' . esc_url($next) . '" />' . "\n";

  // Link a la página anterior (solo si estamos en página 2 o más)
  if ($current > 1) {
    $prev = add_query_arg('paged', $current - 1, $base);
    echo '<atom:link rel="prev" type="application/rss+xml" href="' . esc_url($prev) . '" />' . "\n";
  }
}
