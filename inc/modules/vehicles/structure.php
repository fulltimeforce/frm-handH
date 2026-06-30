<?php

/**
 * Convierte HTML a texto plano y lo recorta en el último límite de palabra.
 * @param string $html       Texto/HTML completo.
 * @param int    $max_chars  Cantidad aprox. de caracteres para ~4 líneas (ajusta si quieres).
 * @return string
 */
function hnh_snippet_from_html($html, $max_chars = 260)
{
    $text = (string) $html;
    // Normaliza <br> a espacios, quita etiquetas y colapsa espacios
    $text = preg_replace('~<br\s*/?>~i', ' ', $text);
    $text = wp_strip_all_tags($text);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5);
    $text = trim(preg_replace('/\s+/u', ' ', $text));

    if ($text === '') return '';

    if (mb_strlen($text, 'UTF-8') <= $max_chars) {
        return $text;
    }

    $snippet = mb_substr($text, 0, $max_chars, 'UTF-8');
    // Corta hasta antes de la última palabra incompleta
    $snippet = preg_replace('/\s+\S*$/u', '', $snippet);
    return rtrim($snippet, " \t\n\r\0\x0B") . '…';
}

/**
 * Renderiza la card de un Vehicle.
 *
 * @param int   $vehicle_id  ID del post (post_type: vehicles).
 * @param array $args        Opcionales: ['thumb_size' => 'large', 'fallback_img' => '']
 */
function hnh_render_vehicle_item($vehicle_id, $args = [])
{
    $vehicle_id = (int) $vehicle_id;
    if (!$vehicle_id) return;

    $thumb_size   = $args['thumb_size']   ?? 'large';
    $fallback_img = $args['fallback_img'] ?? (defined('IMG') ? IMG . '/placeholder-vehicle.png' : '');

    // Datos
    $title     = get_the_title($vehicle_id);
    $permalink = get_permalink($vehicle_id);

    if (!empty($args['list_context']) && is_array($args['list_context'])) {
        $permalink = hnh_vehicle_permalink_with_list_context($vehicle_id, $args['list_context']);
    }

    $registration_no = get_field('registration_no', $vehicle_id);
    $chassis_no      = get_field('chassis_no', $vehicle_id);
    $vehicle_mot     = get_field('mot', $vehicle_id);

    $lot_number     = get_field('lot_number_latest', $vehicle_id);

    // --------------------------------------------

    $auction = get_field('auction_number_latest', $vehicle_id);

    if ($auction) {
        $auction_id = $auction->ID;
        $check_provisional_number = get_field('show_provisional_numbers', $auction_id);

        if (!$check_provisional_number && stripos($lot_number, 'p')) {
            $lot_number = '';
        }
    }

    // --------------------------------------------

    $estimate_low    = get_field('estimate_low', $vehicle_id);
    $estimate_high   = get_field('estimate_high', $vehicle_id);

    $vehicle_status  = get_field('status', $vehicle_id);

    $full_description          = get_field('description', $vehicle_id);
    $vehicle_short_description = hnh_snippet_from_html($full_description, 260);

    // === Imagen: featured -> primera de galería -> fallback ===
    $image     = '';
    $image_alt = $title ?: 'Vehicle';

    // 1) Featured
    $image = get_the_post_thumbnail_url($vehicle_id, $thumb_size);
    if ($image) {
        $thumb_id  = get_post_thumbnail_id($vehicle_id);
        $image_alt = get_post_meta($thumb_id, '_wp_attachment_image_alt', true) ?: $image_alt;
    }

    // 2) Si no hay featured, intenta con la primera de la galería ACF
    if (!$image) {
        $gallery = get_field('gallery_vehicle', $vehicle_id);
        if ($gallery && is_array($gallery)) {
            foreach ($gallery as $item) {
                // ACF puede devolver array, ID o URL
                if (is_array($item)) {
                    $att_id = isset($item['ID']) ? (int)$item['ID'] : 0;
                    if ($att_id) {
                        $image     = wp_get_attachment_image_url($att_id, $thumb_size);
                        $image_alt = get_post_meta($att_id, '_wp_attachment_image_alt', true)
                            ?: ($item['alt'] ?? ($item['title'] ?? $image_alt));
                    } else {
                        $image     = $item['url'] ?? '';
                        $image_alt = $item['alt'] ?? ($item['title'] ?? $image_alt);
                    }
                } elseif (is_numeric($item)) {
                    $att_id    = (int)$item;
                    $image     = wp_get_attachment_image_url($att_id, $thumb_size);
                    $image_alt = get_post_meta($att_id, '_wp_attachment_image_alt', true) ?: $image_alt;
                } elseif (is_string($item) && $item !== '') {
                    $image     = $item;
                    $image_alt = $image_alt; // deja el título como alt
                }

                if ($image) break; // solo la primera válida
            }
        }
    }

    // 3) Fallback si no hay nada
    if (!$image && $fallback_img) {
        $image     = $fallback_img;
        $image_alt = $image_alt ?: 'Vehicle';
    }

?>
    <div class="auction_result-list-item" vehicle-id="<?php echo $vehicle_id; ?>">
        <div class="auction_result-list-img">
            <?php if ($image): ?>
                <img class="w-100" src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($image_alt); ?>">
            <?php endif; ?>
        </div>
        <div class="auction_result-list-info">
            <h3><?php echo esc_html($title); ?></h3>

            <div class="auction_result-list-data">
                <?php if (strtolower((string)$vehicle_status) === 'sold'): ?>


                    <?php
                    $sold_price = get_field('sold_price');
                    $hide_sold_price = get_field('hide_sold_price') ?: false;

                    if ($hide_sold_price):
                    ?>
                        <div style="border: none;padding-left:0;">
                            <p class="gold-text only-text"><?php esc_html_e('Sold'); ?></p>
                        </div>
                    <?php elseif ($sold_price):
                        $sold = (float) preg_replace('/[^\d.\-]/', '', (string) $sold_price);
                    ?>
                        <div style="border: none;padding-left:0;">
                            <p><?php esc_html_e('Sold for'); ?></p>
                            <p class="gold-text">
                                <?php echo '£' . esc_html(number_format_i18n($sold, 0)); ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div style="border: none;padding-left:0;">
                            <p class="gold-text"><?php esc_html_e('Sold'); ?></p>
                        </div>
                    <?php endif; ?>

                <?php else: ?>


                    <?php if ($registration_no || $chassis_no || $vehicle_mot || $lot_number) : ?>
                        <div>
                            <?php if ($lot_number) : ?>
                                <p>Lot No: <span><?php echo esc_html($lot_number); ?></span></p>
                            <?php endif; ?>

                            <?php if ($registration_no) : ?>
                                <p>Registration No: <span><?php echo esc_html($registration_no); ?></span></p>
                            <?php endif; ?>

                            <?php if ($chassis_no) : ?>
                                <p>
                                    <?php
                                    // Si pertenece a la categoría "motorcycles", renombra el label
                                    echo has_term('motorcycles', 'vehicle_category', $vehicle_id) ? 'Frame No:' : 'Chassis No:';
                                    ?>
                                    <span><?php echo esc_html($chassis_no); ?></span>
                                </p>
                            <?php endif; ?>

                            <?php if ($vehicle_mot) : ?>
                                <p>MOT: <span><?php echo esc_html($vehicle_mot); ?></span></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>


                    <?php
                    $low  = (float) preg_replace('/[^\d.\-]/', '', (string) $estimate_low);
                    $high = (float) preg_replace('/[^\d.\-]/', '', (string) $estimate_high);

                    if ($low > 0 || $high > 0) : ?>
                        <div>
                            <p>Estimate</p>
                            <p class="gold-text">
                                <?php
                                if ($low > 0 && $high > 0) {
                                    // Mostrar rango
                                    printf(
                                        '£%s - £%s',
                                        esc_html(number_format_i18n($low, 0)),
                                        esc_html(number_format_i18n($high, 0))
                                    );
                                } elseif ($low > 0) {
                                    // Solo low
                                    printf(
                                        '£%s',
                                        esc_html(number_format_i18n($low, 0))
                                    );
                                } elseif ($high > 0) {
                                    // Solo high
                                    printf(
                                        '£%s',
                                        esc_html(number_format_i18n($high, 0))
                                    );
                                }
                                ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div></div>
                    <?php endif; ?>


                <?php endif; ?>
            </div>

            <?php if ($vehicle_short_description) : ?>
                <p class="auction_result-list-description">
                    <?php echo esc_html($vehicle_short_description); ?>
                </p>
            <?php endif; ?>

            <?php if (is_page('vehicles-for-sale')): ?>
                <a alt="View Details" href="<?php echo esc_url($permalink); ?>" class="permalink_border">
                    View Details
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                        <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                    </svg>
                </a>
            <?php else: ?>
                <?php $enquire_href = esc_url(get_permalink(123876)) . '?vehicle=' . $vehicle_id; ?>
                <a alt="Enquire Now" href="<?php echo esc_url($enquire_href); ?>" class="permalink_border">
                    Enquire Now
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                        <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                    </svg>
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php
}


/**
 * Renderiza la nueva card de un Vehicle con carrusel de imágenes (Splide).
 *
 * @param int   $vehicle_id  ID del post (post_type: vehicles).
 * @param array $args        Opcionales:
 *   - thumb_size   (string) Tamaño WP de la imagen. Default 'large'
 *   - fallback_img (string) URL fallback si no hay imágenes
 *   - max_slides   (int)    Máximo de slides a mostrar. Default 8
 *   - enquire_href (string) URL para "Enquire Now" (si no se pasa usa el permalink)
 */
function hnh_render_vehicle_card($vehicle_id, $args = [], $format = 1)
{
    $vehicle_id = (int) $vehicle_id;
    if (!$vehicle_id) return;

    $thumb_size   = $args['thumb_size']   ?? 'large';
    $fallback_img = $args['fallback_img'] ?? (defined('IMG') ? IMG . '/placeholder-vehicle.png' : '');
    $max_slides   = isset($args['max_slides']) ? (int)$args['max_slides'] : 8;

    // Datos
    $title     = get_the_title($vehicle_id);
    $permalink = get_permalink($vehicle_id);

    if (!empty($args['list_context']) && is_array($args['list_context'])) {
        $permalink = hnh_vehicle_permalink_with_list_context($vehicle_id, $args['list_context']);
    } elseif (is_page('refine-your-search')) {
        if (is_page('refine-your-search')) {
            $permalink = add_query_arg([
                'c' => 'search',
            ], get_permalink($vehicle_id));
        }
    }

    $registration_no = get_field('registration_no', $vehicle_id);
    $chassis_no      = get_field('chassis_no', $vehicle_id);
    $vehicle_mot     = get_field('mot', $vehicle_id);

    $lot_number     = get_field('lot_number_latest', $vehicle_id);

    // --------------------------------------------

    $auction = get_field('auction_number_latest', $vehicle_id);

    if ($auction) {
        $auction_id = $auction->ID;
        $check_provisional_number = get_field('show_provisional_numbers', $auction_id);

        if (!$check_provisional_number && stripos($lot_number, 'p')) {
            $lot_number = '';
        }
    }

    // --------------------------------------------

    $estimate_low    = get_field('estimate_low', $vehicle_id);
    $estimate_high   = get_field('estimate_high', $vehicle_id);

    $vehicle_status   = get_field('status', $vehicle_id);

    // Formateo "Price"
    $estimate_html = '';
    if ($estimate_low && $estimate_high) {
        $low  = (float) preg_replace('/[^\d.\-]/', '', (string) $estimate_low);
        $high = (float) preg_replace('/[^\d.\-]/', '', (string) $estimate_high);
        $estimate_html = '£' . esc_html(number_format_i18n($low, 0)) . ' - £' . esc_html(number_format_i18n($high, 0));
    }

    // Galería: usa ACF 'gallery_vehicle'. Si no hay, usar thumbnail. Si no hay, fallback.
    $slides = [];

    // 1) Intentar con galería
    $gallery = get_field('gallery_vehicle', $vehicle_id);
    if ($gallery && is_array($gallery)) {
        foreach ($gallery as $item) {
            $id  = 0;
            $url = '';
            $alt = '';
            if (is_array($item)) {
                $id  = isset($item['ID']) ? (int)$item['ID'] : 0;
                $url = $item['url'] ?? ($id ? wp_get_attachment_image_url($id, 'full') : '');
                $alt = $item['alt'] ?? ($id ? get_post_meta($id, '_wp_attachment_image_alt', true) : ($item['title'] ?? ''));
            } elseif (is_numeric($item)) {
                $id  = (int)$item;
                $url = wp_get_attachment_image_url($id, 'full');
                $alt = get_post_meta($id, '_wp_attachment_image_alt', true);
            } elseif (is_string($item) && $item !== '') {
                $url = $item;
                $alt = '';
            }
            if ($url) $slides[] = ['url' => $url, 'alt' => $alt];
            if (count($slides) >= $max_slides) break;
        }
    }

    // 2) Si no hay galería, usar featured
    if (empty($slides) && has_post_thumbnail($vehicle_id)) {
        $thumb_id  = get_post_thumbnail_id($vehicle_id);
        $thumb_url = wp_get_attachment_image_url($thumb_id, 'full');
        $thumb_alt = get_post_meta($thumb_id, '_wp_attachment_image_alt', true);
        if ($thumb_alt === '') $thumb_alt = $title;
        if ($thumb_url) {
            $slides[] = ['url' => $thumb_url, 'alt' => $thumb_alt];
        }
    }

    // 3) Fallback final
    if (empty($slides) && $fallback_img) {
        $slides[] = ['url' => $fallback_img, 'alt' => $title ?: 'Vehicle Image'];
    }

    // Enquire
    // $enquire_href = get_field('lot_link');
    $enquire_href = esc_url(get_permalink(123876)) . '?vehicle=' . $vehicle_id;
?>
    <div class="vehicle_card">
        <div class="vehicle_card-image">
            <?php
            // ¿La galería tiene más de 1 imagen?
            $has_gallery_multi = (is_array($gallery) && count($gallery) > 1);
            ?>

            <?php if ($has_gallery_multi && $format == 1): ?>
                <div class="splide vehicle_card-thumbs" role="group" aria-label="<?php echo esc_attr($title ?: 'Vehicle'); ?>">
                    <div class="splide__arrows">
                        <button class="splide__arrow splide__arrow--prev" type="button" aria-label="<?php esc_attr_e('Previous'); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="14" viewBox="0 0 13 14" fill="none">
                                <path d="M0 7H12M12 7L6 1M12 7L6 13" stroke="black" />
                            </svg>
                        </button>
                        <button class="splide__arrow splide__arrow--next" type="button" aria-label="<?php esc_attr_e('Next'); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="14" viewBox="0 0 13 14" fill="none">
                                <path d="M0 7H12M12 7L6 1M12 7L6 13" stroke="black" />
                            </svg>
                        </button>
                    </div>
                    <div class="splide__track">
                        <ul class="splide__list">
                            <?php foreach ($slides as $s): ?>
                                <li class="splide__slide">
                                    <img src="<?php echo esc_url($s['url']); ?>"
                                        alt="<?php echo esc_attr($s['alt'] ?: ($title ?: 'Vehicle Image')); ?>" title="Vehicle Image">
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <?php
                // Mostrar thumbnail (y si no existe, usar primer slide o el fallback).
                $single_url = '';
                $single_alt = $title ?: 'Vehicle Image';

                if (has_post_thumbnail($vehicle_id)) {
                    $thumb_id  = get_post_thumbnail_id($vehicle_id);
                    $single_url = wp_get_attachment_image_url($thumb_id, $thumb_size);
                    $single_alt = get_post_meta($thumb_id, '_wp_attachment_image_alt', true) ?: $single_alt;
                } elseif (!empty($slides)) {
                    $single_url = $slides[0]['url'] ?? '';
                    $single_alt = $slides[0]['alt'] ?: $single_alt;
                } elseif (!empty($fallback_img)) {
                    $single_url = $fallback_img;
                }
                ?>

                <?php if ($single_url): ?>
                    <img class="vehicle_card-single" src="<?php echo esc_url($single_url); ?>" alt="<?php echo esc_attr($single_alt); ?>" title="Vehicle Image">
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="vehicle_card-info">
            <div class="w-100">
                <div class="vehicle_card-content">
                    <a href="<?php echo esc_url($permalink); ?>" alt="<?php echo esc_html($title); ?>">
                        <h3><?php echo esc_html($title); ?></h3>
                    </a>
                </div>
                <?php $sold_price = get_field('sold_price'); ?>
                <?php if (!$sold_price): ?>
                    <?php if ($estimate_html): ?>
                        <h4>
                            <span><?php esc_html_e('Estimate'); ?></span>
                            <?php echo $estimate_html; ?>
                        </h4>
                    <?php else: ?>
                        <h4 style="opacity:0;pointer-events:none;user-select:none;">
                            <span>-</span>
                            -
                        </h4>
                    <?php endif; ?>
                <?php else: ?>
                    <h4 style="opacity:0;pointer-events:none;user-select:none;">
                        <span>-</span>
                        -
                    </h4>
                <?php endif; ?>
            </div>

            <div class="vehicle_card-price">
                <?php if (strtolower($vehicle_status) == 'sold'): ?>
                    <?php
                    $hide_sold_price = get_field('hide_sold_price') ?: false;

                    if ($hide_sold_price):
                    ?>
                        <h4 style="margin:0">
                            <span class="only-text"><?php esc_html_e('Sold'); ?></span>
                        </h4>
                    <?php elseif ($sold_price):
                        $sold = (float) preg_replace('/[^\d.\-]/', '', (string) $sold_price);
                    ?>
                        <h4 style="margin:0">
                            <span><?php esc_html_e('Sold for'); ?></span>
                            <?php echo '£' . esc_html(number_format_i18n($sold, 0)); ?>
                        </h4>
                    <?php else: ?>
                        <h4 style="margin:0">
                            <span><?php esc_html_e('Sold'); ?></span>
                        </h4>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if ($registration_no || $chassis_no || $vehicle_mot || $lot_number): ?>
                        <ul>
                            <?php if ($lot_number): ?>
                                <li><b><?php esc_html_e('Lot No:'); ?></b> <?php echo esc_html($lot_number); ?></li>
                            <?php endif; ?>
                            <?php if ($registration_no): ?>
                                <li><b><?php esc_html_e('Registration No:'); ?></b> <?php echo esc_html($registration_no); ?></li>
                            <?php endif; ?>
                            <?php if ($chassis_no): ?>
                                <li><b><?php
                                        if (has_term('motorcycles', 'vehicle_category', $vehicle_id)) {
                                            esc_html_e('Frame No:');
                                        } else {
                                            esc_html_e('Chassis No:');
                                        }
                                        ?></b> <?php echo esc_html($chassis_no); ?></li>
                            <?php endif; ?>
                            <?php if ($vehicle_mot): ?>
                                <li><b><?php esc_html_e('MOT:'); ?></b> <?php echo esc_html($vehicle_mot); ?></li>
                            <?php endif; ?>
                        </ul>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="vehicle_card-actions">
                <a class="btn-view" href="<?php echo esc_url($permalink); ?>">
                    <?php esc_html_e('View Details'); ?>
                </a>
                <?php if (!empty($enquire_href)): ?>
                    <a class="btn-enquire" href="<?php echo esc_url($enquire_href); ?>" target="_blank">
                        <?php esc_html_e('Enquire Now'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}


// En functions.php
if (!function_exists('hnh_render_buy_it_now_block')) {
    /**
     * Renderiza el bloque "Buy It Now" (form + loop + paginación) para vehicles.
     * Se apoya en GET params y preserva los mismos en paginación.
     *
     * @param array $options  Opcional: ['min_year' => 1920, 'post_type' => 'vehicles']
     * @return string HTML listo para imprimir
     */
    function hnh_render_buy_it_now_block(array $options = []): string
    {

        // ===== Paginación y per page =====
        $pgn = isset($_GET['pgn']) ? max(1, (int) $_GET['pgn']) : 1;
        $ppp = isset($_GET['posts_per_page']) ? max(1, (int) $_GET['posts_per_page']) : 48;

        // ===== GET params =====
        $q        = isset($_GET['search_vehicle']) ? sanitize_text_field($_GET['search_vehicle']) : '';

        // GET params
        $order_by = isset($_GET['order_by']) ? sanitize_text_field($_GET['order_by']) : '';
        $model_id  = isset($_GET['model_id']) ? (int) $_GET['model_id'] : 0;
        $vehicle_type = is_page(803) ? 'private-sale' : 'auction';
        $vehicle_status  = isset($_GET['vehicle_status']) ? sanitize_text_field($_GET['vehicle_status']) : '';

        $lots = is_page(6297) ? 'past' : '';

        if(is_page(6297)){
            $order_by = 'lot';
        }

        // ===== Repo query =====
        if (!class_exists('VehiclesSearchRepository')) {
            require_once get_template_directory() . '/VehiclesSearchRepository.php';
        }

        global $wpdb;
        $repo = new VehiclesSearchRepository($wpdb);

        $result = $repo->search([
            'q'        => $q,
            'lots'     => $lots,
            'order_by' => $order_by,
            'model_id'  => $model_id,
            'status' => $vehicle_status,
            'vehicle_type' => $vehicle_type,
            'per_page' => $ppp,
            'page'     => $pgn,
        ]);

        $rows      = $result['items'];
        $total     = (int) $result['total'];
        $max_pages = (int) max(1, (int) ceil($total / $ppp));

    ?>
        <form class="auction_result-filter" method="get" action="">
            <div class="auction_result-filter-search">
                <input type="search" name="search_vehicle" placeholder="Search for..." value="<?php echo esc_attr($q); ?>">
                <button type="submit">Go</button>
            </div>

            <div class="auction_result-filter-select">
                <?php
                $models = get_posts([
                    'post_type'      => 'model',
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'orderby'        => 'title',
                    'order'          => 'ASC',
                    'fields'         => 'ids',
                ]);
                ?>
                <select name="model_id" onchange="this.form.submit()">
                    <option value=""><?php esc_html_e('All Models'); ?></option>

                    <?php if ($models): ?>
                        <?php foreach ($models as $mid): ?>
                            <option value="<?php echo esc_attr($mid); ?>" <?php selected($model_id, (int)$mid); ?>>
                                <?php echo esc_html(get_the_title($mid)); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="auction_result-filter-select">
                <select name="order_by" onchange="this.form.submit()">
                    <option value=""><?php esc_html_e('Sort by'); ?></option>
                    <option value="lot" <?php selected($order_by, 'lot');          ?>><?php esc_html_e('Sort by lot number'); ?></option>
                    <option value="low-to-high" <?php selected($order_by, 'low-to-high');  ?>><?php esc_html_e('Estimate/Price - Low to High'); ?></option>
                    <option value="high-to-low" <?php selected($order_by, 'high-to-low');  ?>><?php esc_html_e('Estimate/Price - High to Low'); ?></option>
                </select>
            </div>

            <div class="auction_result-filter-select">
                <select name="vehicle_status" onchange="this.form.submit()">
                    <option value="">Select status</option>
                    <?php
                    $status_selected = $vehicle_status ?: '';

                    if (is_page(803)) {
                        $status_opts = [
                            'Appraisal' => 'Appraisal',
                            'Available' => 'Available',
                            'Allocated' => 'Allocated',
                            'Sold' => 'Sold',
                            'Back to vendor' => 'Back to vendor',
                            'Offsite/Disposal' => 'Offsite/Disposal',
                            'Merged' => 'Merged',
                            'Split' => 'Split',
                        ];
                    } else {
                        $status_opts = [
                            'Appraisal' => 'Appraisal',
                            'Available' => 'Available',
                            'Allocated' => 'Allocated',
                        ];
                    }

                    ?>
                    <?php foreach ($status_opts as $val => $label): ?>
                        <option value="<?php echo esc_attr($val); ?>" <?php selected($status_selected, $val); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="w-100"></div>

            <div class="auction_result-filter-page">
                <p>
                    <?php esc_html_e('Showing'); ?>
                    <select id="blog-perpage" class="blog_section-filter-page" name="posts_per_page" onchange="this.form.submit()">
                        <option value="12" <?php selected((int)$ppp, 12); ?>>12</option>
                        <option value="24" <?php selected((int)$ppp, 24); ?>>24</option>
                        <option value="48" <?php selected((int)$ppp, 48); ?>>48</option>
                        <option value="96" <?php selected((int)$ppp, 96); ?>>96</option>
                    </select>
                    <?php esc_html_e('Per Page'); ?>
                </p>
            </div>
        </form>

        <?php if (!empty($rows)): ?>
            <div class="w-100">
                <?php foreach ($rows as $r): ?>
                    <?php hnh_render_vehicle_item((int)$r['vehicle_id']); ?>
                <?php endforeach; ?>
            </div>

            <?php
            $pagination = paginate_links([
                'base'      => esc_url_raw(add_query_arg('pgn', '%#%', $page_url)),
                'format'    => '',
                'current'   => $pgn,
                'total'     => $max_pages,
                'mid_size'  => 2,
                'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="19" height="14" viewBox="0 0 19 14" fill="none"><path d="M19 7L1.00049 7M1.00049 7L7.00049 13M1.00049 7L7.0005 0.999999" stroke="#8C6E47"/></svg>',
                'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="19" height="14" viewBox="0 0 19 14" fill="none"><path d="M-7.15494e-08 7L17.9995 7M17.9995 7L11.9995 1M17.9995 7L11.9995 13" stroke="#8C6E47"/></svg>',
                'add_args'  => array_filter([
                    'search_vehicle'     => $q,
                    'lots'               => $lots,
                    'order_by'           => $order_by,
                    'model_id'            => $model_id,
                    'vehicle_status' => $vehicle_status,
                    'posts_per_page'     => $ppp,
                ], static fn($v) => $v !== '' && $v !== null && $v !== 0),
            ]);

            if ($pagination) {
                echo '<div class="auction_result-pagination">' . $pagination . '</div>';
            }
            ?>
        <?php else: ?>
            <div class="no-one">
                <p><?php esc_html_e('No results found'); ?></p>
            </div>
        <?php endif; ?>

<?php
        return ob_get_clean();
    }
}
