<?php
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Vehicle Gallery - Replace Mode
 *
 * - Botón "Replace" (JS)
 * - Filtrado del Media Modal para que en Replace SOLO se vean las recién subidas
 * - Endpoint AJAX para "detaching" (post_parent = 0) de las imágenes reemplazadas
 *
 * @package HandH
 * @subpackage Vehicles
 */

/**
 * A) AJAX endpoint: detach old attachments (post_parent -> 0)
 *    - NO borra archivos
 *    - Solo despega del vehículo (y solo si el parent actual es el vehículo)
 */
add_action('wp_ajax_hnh_vehicle_gallery_detach_old', function () {
  // Permisos: debe poder editar posts
  if (!current_user_can('edit_posts')) {
    wp_send_json_error(['message' => 'No permission'], 403);
  }

  $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
  $ids = isset($_POST['ids']) ? (array) $_POST['ids'] : [];
  $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field($_POST['_wpnonce']) : '';

  if (!$post_id || get_post_type($post_id) !== 'vehicles') {
    wp_send_json_error(['message' => 'Invalid vehicle'], 400);
  }

  if (!wp_verify_nonce($nonce, 'hnh_vehicle_gallery_replace_' . $post_id)) {
    wp_send_json_error(['message' => 'Invalid nonce'], 403);
  }

  $ids = array_values(array_unique(
    array_filter(array_map('intval', $ids))
  ));
  if (!$ids) {
    wp_send_json_success(['updated' => 0]);
  }

  $updated = 0;

  foreach ($ids as $att_id) {
    if (get_post_type($att_id) !== 'attachment') {
      continue;
    }

    $parent = (int) wp_get_post_parent_id($att_id);

    // Solo detach si actualmente está adjunta al vehículo
    if ($parent === $post_id) {
      wp_update_post([
        'ID' => $att_id,
        'post_parent' => 0,
      ]);
      $updated++;
    }
  }

  wp_send_json_success(['updated' => $updated]);
});


/**
 * B) AJAX endpoint: borrar permanentemente attachments viejos
 * - SOLO borra si post_parent === vehicle_id
 * - NO borra si es "histórica" (post_parent distinto)
 */
add_action('wp_ajax_hnh_vehicle_gallery_delete_old', function () {
  $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
  $ids = isset($_POST['ids']) ? (array) $_POST['ids'] : [];
  $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field($_POST['_wpnonce']) : '';

  // Validaciones básicas
  if (!$post_id || get_post_type($post_id) !== 'vehicles') {
    wp_send_json_error(['message' => 'Invalid vehicle'], 400);
  }

  if (!current_user_can('edit_post', $post_id)) {
    wp_send_json_error(['message' => 'No permission'], 403);
  }

  if (!wp_verify_nonce($nonce, 'hnh_vehicle_gallery_replace_' . $post_id)) {
    wp_send_json_error(['message' => 'Invalid nonce'], 403);
  }

  // Sanitizar IDs
  $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
  if (!$ids) {
    wp_send_json_success(['deleted' => 0, 'skipped' => 0]);
  }

  $deleted = 0;
  $skipped = 0;

  foreach ($ids as $att_id) {
    if (get_post_type($att_id) !== 'attachment') {
      $skipped++;
      continue;
    }

    $parent = (int) wp_get_post_parent_id($att_id);

    // ✅ Regla crítica: solo borrar si es del vehículo
    if ($parent === $post_id) {
      // true = force delete (borrado permanente, sin papelera)
      $result = wp_delete_attachment($att_id, true);
      if ($result) {
        $deleted++;
      } else {
        $skipped++;
      }
    } else {
      // histórica -> no se borra
      $skipped++;
    }
  }

  wp_send_json_success([
    'deleted' => $deleted,
    'skipped' => $skipped,
  ]);
});


/**
 * B1) Extender ajax_query_attachments_args para "replace scope"
 *
 * scope: gallery_vehicle_replace
 * - Queremos filtrar por:
 *   - post_parent = vehicle_id (porque uploads nuevos quedan adjuntos al post)
 *   - y en posts_clauses agregamos:
 *       - ID IN (uploaded_ids) si ya se conoce
 *       - o post_date_gmt >= started_at mientras no se conozcan
 */
add_filter('ajax_query_attachments_args', function ($args) {
  $post_id = isset($_REQUEST['post_id']) ? (int) $_REQUEST['post_id'] : 0;
  if (!$post_id) {
    return $args;
  }

  if (get_post_type($post_id) !== 'vehicles') {
    return $args;
  }

  $scope = '';
  if (isset($_REQUEST['query']['vehicle_scope'])) {
    $scope = (string) $_REQUEST['query']['vehicle_scope'];
  } elseif (isset($_REQUEST['vehicle_scope'])) {
    $scope = (string) $_REQUEST['vehicle_scope'];
  }

  if ($scope !== 'gallery_vehicle_replace') {
    return $args;
  }

  // Solo imágenes
  $args['post_mime_type'] = 'image';

  // Replace trabaja únicamente con uploads nuevos => deben estar adjuntos al vehículo
  $args['post_parent'] = $post_id;

  // Flags custom para posts_clauses
  $args['hnh_vehicle_post_id'] = $post_id;
  $args['hnh_vehicle_scope'] = $scope;

  $started_at = 0;
  if (isset($_REQUEST['query']['hnh_replace_started_at'])) {
    $started_at = (int) $_REQUEST['query']['hnh_replace_started_at'];
  }

  $uploaded_ids = '';
  if (isset($_REQUEST['query']['hnh_uploaded_ids'])) {
    $uploaded_ids = (string) $_REQUEST['query']['hnh_uploaded_ids'];
  }

  $args['hnh_replace_started_at'] = $started_at;
  $args['hnh_uploaded_ids'] = $uploaded_ids;

  return $args;
}, 20);


/**
 * B2) posts_clauses para "replace scope"
 *
 * - Si ya tenemos uploaded_ids: mostramos SOLO esos IDs
 * - Si todavía no: mostramos SOLO lo subido desde started_at (por fecha)
 */
add_filter('posts_clauses', function ($clauses, $query) {
  if (!is_admin() || !wp_doing_ajax()) {
    return $clauses;
  }

  if (empty($_REQUEST['action']) || $_REQUEST['action'] !== 'query-attachments') {
    return $clauses;
  }

  $scope = '';
  if (isset($_REQUEST['query']['vehicle_scope'])) {
    $scope = (string) $_REQUEST['query']['vehicle_scope'];
  } elseif (isset($_REQUEST['vehicle_scope'])) {
    $scope = (string) $_REQUEST['vehicle_scope'];
  }

  if ($scope !== 'gallery_vehicle_replace') {
    return $clauses;
  }

  if ($query->get('post_type') !== 'attachment') {
    return $clauses;
  }

  $post_id = (int) $query->get('hnh_vehicle_post_id');
  if (!$post_id) {
    return $clauses;
  }

  global $wpdb;

  $where = $clauses['where'];

  // 1) Filtro por IDs exactos (mejor)
  $uploaded_ids_raw = (string) $query->get('hnh_uploaded_ids');
  if (!empty($uploaded_ids_raw)) {
    $ids = array_values(array_unique(array_filter(array_map('intval', explode(',', $uploaded_ids_raw)))));
    if (!empty($ids)) {
      $ids_sql = implode(',', $ids);
      $clauses['where'] = $where . " AND {$wpdb->posts}.ID IN ($ids_sql) ";
      return $clauses;
    }
  }

  // 2) Fallback por tiempo (mientras el JS captura los selected IDs)
  $started_at = (int) $query->get('hnh_replace_started_at');
  if ($started_at > 0) {
    $started_gmt = gmdate('Y-m-d H:i:s', $started_at);
    $clauses['where'] = $where . $wpdb->prepare(" AND {$wpdb->posts}.post_date_gmt >= %s ", $started_gmt);
  }

  return $clauses;
}, 20, 2);


/**
 * C) AJAX endpoint: guardar orden de galería automáticamente
 *    - Recibe el orden final de IDs de la galería
 *    - Guarda en el campo ACF sin necesidad de presionar Update/Publish
 */
add_action('wp_ajax_hnh_vehicle_gallery_save_order', function () {
  $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
  $field_name = isset($_POST['field_name']) ? sanitize_text_field($_POST['field_name']) : '';
  $field_key = isset($_POST['field_key']) ? sanitize_text_field($_POST['field_key']) : '';
  $ids = isset($_POST['ids']) ? (array) $_POST['ids'] : [];
  $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field($_POST['_wpnonce']) : '';

  // Validar post_id existe y es de tipo vehicles
  if (!$post_id) {
    wp_send_json_error(['message' => 'Invalid post_id'], 400);
  }

  $post = get_post($post_id);
  if (!$post || get_post_type($post_id) !== 'vehicles') {
    wp_send_json_error(['message' => 'Invalid vehicle'], 400);
  }

  // Validar permisos
  if (!current_user_can('edit_post', $post_id)) {
    wp_send_json_error(['message' => 'No permission'], 403);
  }

  // Validar nonce
  if (!wp_verify_nonce($nonce, 'hnh_vehicle_gallery_replace_' . $post_id)) {
    wp_send_json_error(['message' => 'Invalid nonce'], 403);
  }

  // Validar field_name y field_key
  if (empty($field_name) || empty($field_key)) {
    wp_send_json_error(['message' => 'Missing field_name or field_key'], 400);
  }

  // Validar que field_key sea válido (hardening)
  // 1. Debe empezar con 'field_' (formato ACF estándar)
  if (strpos($field_key, 'field_') !== 0) {
    wp_send_json_error(['message' => 'Invalid field_key format'], 400);
  }

  // 2. Si ACF está activo, verificar que el field exista
  if (function_exists('acf_get_field')) {
    $field = acf_get_field($field_key);
    if (!$field) {
      wp_send_json_error(['message' => 'ACF field not found'], 400);
    }
  }

  // Sanitizar y validar IDs
  $ids = array_values(array_unique(
    array_filter(array_map('intval', $ids))
  ));

  // Guardar en formato ACF
  // 1. Guardar el array de IDs en el meta field_name
  update_post_meta($post_id, $field_name, $ids);

  // 2. Guardar la referencia ACF en _field_name
  update_post_meta($post_id, '_' . $field_name, $field_key);

  wp_send_json_success([
    'count' => count($ids),
    'message' => 'Gallery order saved successfully'
  ]);
});


/**
 * D) Enqueue JS + pasar nonce/config
 *
 * IMPORTANTE:
 * - Este script debería ejecutarse DESPUÉS del inline grande que ya tenés en admin-gallery.php
 * - Para eso, le ponemos dependency al handle "vehicle-gallery-scope" (que vos ya encolás)
 */
add_action('admin_enqueue_scripts', function ($hook) {
  if ($hook !== 'post.php' && $hook !== 'post-new.php') {
    return;
  }

  $screen = function_exists('get_current_screen') ? get_current_screen() : null;
  if (!$screen || $screen->post_type !== 'vehicles') {
    return;
  }

  // wp.media disponible
  wp_enqueue_media();

  $post_id = isset($_GET['post']) ? (int) $_GET['post'] : 0;

  wp_enqueue_script(
    'hnh-acf-gallery-replace',
    get_stylesheet_directory_uri() . '/public/js/admin/vehicles/acf-gallery-replace.js',
    ['vehicle-gallery-scope'], // clave: correr después del inline existente
    '1.0.0',
    true
  );

  wp_localize_script('hnh-acf-gallery-replace', 'hnhGalleryReplace', [
    'fieldName' => 'gallery_vehicle',
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'postId' => $post_id,
    'nonce' => $post_id ? wp_create_nonce('hnh_vehicle_gallery_replace_' . $post_id) : '',
  ]);
});
