<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once dirname(__FILE__, 4) . '/wp-load.php';

if (!current_user_can('manage_options')) {
    wp_die('No autorizado');
}

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

echo '<pre>';

$site_host = parse_url(home_url(), PHP_URL_HOST);

$posts = get_posts([
    'post_type'      => 'post',
    'post_status'    => ['publish', 'future', 'draft', 'pending', 'private'],
    'posts_per_page' => -1, // Cambiar a -1 cuando quieras procesar todos
    'orderby'        => 'date',
    'order'          => 'DESC',
]);

foreach ($posts as $post) {

    echo "=====================================================\n";
    echo "Post ID: {$post->ID}\n";
    echo "Título : {$post->post_title}\n";
    echo "=====================================================\n";

    $content = $post->post_content;
    $original_content = $content;

    preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $matches);

    if (empty($matches[1])) {
        echo "No se encontraron imágenes.\n\n";
        continue;
    }

    foreach ($matches[1] as $image_url) {

        $image_host = parse_url($image_url, PHP_URL_HOST);

        if (!$image_host) {
            continue;
        }

        // Ya es una imagen local
        if ($image_host === $site_host) {
            echo "✓ Imagen local, se omite:\n";
            echo "  {$image_url}\n\n";
            continue;
        }

        $extension = strtolower(pathinfo(parse_url($image_url, PHP_URL_PATH), PATHINFO_EXTENSION));

        $allowed_extensions = [
            'jpg',
            'jpeg',
            'png',
            'gif',
            'webp'
        ];

        if (!in_array($extension, $allowed_extensions)) {
            echo "✗ No parece una imagen:\n";
            echo "  {$image_url}\n\n";
            continue;
        }

        echo "↓ Descargando:\n";
        echo "  {$image_url}\n";

        $attachment_id = media_sideload_image($image_url, $post->ID, null, 'id');

        if (is_wp_error($attachment_id)) {
            echo "ERROR: " . $attachment_id->get_error_message() . "\n\n";
            continue;
        }

        $new_url = wp_get_attachment_url($attachment_id);

        if (!$new_url) {
            echo "ERROR obteniendo URL local.\n\n";
            continue;
        }

        $content = str_replace($image_url, $new_url, $content);

        echo "✓ Reemplazada\n";
        echo "Antes:\n";
        echo "  {$image_url}\n";
        echo "Ahora:\n";
        echo "  {$new_url}\n\n";
    }

    if ($content !== $original_content) {

        wp_update_post([
            'ID' => $post->ID,
            'post_content' => $content
        ]);

        echo "✅ POST ACTUALIZADO.\n\n";

    } else {

        echo "Sin cambios.\n\n";
    }
}

echo "=====================\n";
echo "Proceso terminado.\n";
echo "=====================";

echo '</pre>';