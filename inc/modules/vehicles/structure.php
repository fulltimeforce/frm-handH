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

    $registration_no = get_field('registration_no', $vehicle_id);
    $chassis_no      = get_field('chassis_no', $vehicle_id);
    $vehicle_mot     = get_field('mot', $vehicle_id);

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
    <div class="auction_result-list-item">
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
                    if ($sold_price):
                        $sold = (float) preg_replace('/[^\d.\-]/', '', (string) $sold_price);
                    ?>
                        <div style="border: none;">
                            <p><?php esc_html_e('Sold for'); ?></p>
                            <p class="gold-text">
                                <?php echo '£' . esc_html(number_format_i18n($sold, 0)); ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div>
                            <p>Sold</p>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <?php if ($registration_no || $chassis_no || $vehicle_mot) : ?>
                        <div>
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
                <?php endif; ?>

                <?php if ($estimate_low && $estimate_high) : ?>
                    <div>
                        <p>Estimated at</p>
                        <p class="gold-text">
                            <?php
                            $low  = (float) preg_replace('/[^\d.\-]/', '', (string) $estimate_low);
                            $high = (float) preg_replace('/[^\d.\-]/', '', (string) $estimate_high);

                            printf(
                                '£%s - £%s',
                                esc_html(number_format_i18n($low, 0)),
                                esc_html(number_format_i18n($high, 0))
                            );
                            ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($vehicle_short_description) : ?>
                <p class="auction_result-list-description">
                    <?php echo esc_html($vehicle_short_description); ?>
                </p>
            <?php endif; ?>

            <a alt="Enquire Now" href="<?php echo esc_url($permalink); ?>" class="permalink_border">
                Enquire Now
                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                    <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                </svg>
            </a>
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

    if (is_page('refine-your-search')) {
        if (is_page('refine-your-search')) {
            $permalink = add_query_arg([
                'c' => 'search',
            ], get_permalink($vehicle_id));
        }
    }

    $registration_no = get_field('registration_no', $vehicle_id);
    $chassis_no      = get_field('chassis_no', $vehicle_id);
    $vehicle_mot     = get_field('mot', $vehicle_id);

    $estimate_low    = get_field('estimate_low', $vehicle_id);
    $estimate_high   = get_field('estimate_high', $vehicle_id);

    $vehicle_status   = get_field('status', $vehicle_id);

    // Formateo "Estimated at"
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
    $enquire_href = get_field('lot_link') ?? $permalink;

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
                                        alt="<?php echo esc_attr($s['alt'] ?: ($title ?: 'Vehicle Image')); ?>">
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
                    <img class="vehicle_card-single" src="<?php echo esc_url($single_url); ?>"
                        alt="<?php echo esc_attr($single_alt); ?>">
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
                <?php if ($estimate_html): ?>
                    <h4>
                        <span><?php esc_html_e('Estimated at'); ?></span>
                        <?php echo $estimate_html; ?>
                    </h4>
                <?php endif; ?>
            </div>

            <div class="vehicle_card-price">
                <?php if (strtolower($vehicle_status) == 'sold'): ?>
                    <?php
                    $sold_price = get_field('sold_price');
                    if ($sold_price):
                        $sold = (float) preg_replace('/[^\d.\-]/', '', (string) $sold_price);
                    ?>
                        <h4 style="margin:0">
                            <span><?php esc_html_e('Sold for'); ?></span>
                            <?php echo '£' . esc_html(number_format_i18n($sold, 0)); ?>
                        </h4>
                    <?php else: ?>
                        <ul>
                            <li><b>Sold</b></li>
                        </ul>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if ($registration_no || $chassis_no || $vehicle_mot): ?>
                        <ul>
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
                <a class="btn-enquire" href="<?php echo esc_url($enquire_href); ?>" target="_blank">
                    <?php esc_html_e('Enquire Now'); ?>
                </a>
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
        $defaults = [
            // 'min_year' => 1920,
            'post_type' => 'vehicles',
        ];
        $opt = array_merge($defaults, $options);

        // Paginación
        $paged = max(1, get_query_var('paged') ? (int) get_query_var('paged') : (int) get_query_var('page'));

        // Per page
        $ppp = isset($_GET['posts_per_page']) ? max(1, (int) $_GET['posts_per_page']) : 6;

        // GET params
        $q               = isset($_GET['search_vehicle']) ? sanitize_text_field($_GET['search_vehicle']) : '';
        $vehicle_status  = isset($_GET['vehicle_status']) ? sanitize_text_field($_GET['vehicle_status']) : '';
        $year_from_param = isset($_GET['year_from'])      ? sanitize_text_field($_GET['year_from'])      : '';
        $year_to_param   = isset($_GET['year_to'])        ? sanitize_text_field($_GET['year_to'])        : '';
        $brand_slug      = isset($_GET['vehicle_brand'])  ? sanitize_text_field($_GET['vehicle_brand'])  : '';
        $order_by        = isset($_GET['order_by']) ? sanitize_text_field($_GET['order_by']) : 'lot';

        // Campo meta de fecha/hora a comparar (ajusta si tu clave es otra)
        $auction_date_meta = 'auction_date_latest';

        // Meta query builder
        $meta_query = ['relation' => 'AND'];

        // Status (ACF: status) — exacto, ignorando mayúsculas y espacios
        if ($vehicle_status !== '') {
            $status_regex = '^[[:space:]]*' . preg_quote(strtolower($vehicle_status), '~') . '[[:space:]]*$';
            $meta_query[] = [
                'key'     => 'status',
                'value'   => $status_regex,
                'compare' => 'REGEXP',
            ];
        }

        // Rango por año sobre ACF: auction_date_latest (YYYY-mm-dd HH:ii)
        $year_from = (ctype_digit($year_from_param) ? (int) $year_from_param : null);
        $year_to   = (ctype_digit($year_to_param)   ? (int) $year_to_param   : null);

        if ($year_from && $year_to) {
            if ($year_from > $year_to) {
                [$year_from, $year_to] = [$year_to, $year_from];
            }
            $start_dt = sprintf('%04d-01-01 00:00:00', $year_from);
            $end_dt   = sprintf('%04d-12-31 23:59:59', $year_to);
            $meta_query[] = [
                'key'     => 'auction_date_latest',
                'value'   => [$start_dt, $end_dt],
                'compare' => 'BETWEEN',
                'type'    => 'DATETIME',
            ];
        } elseif ($year_from) {
            $start_dt = sprintf('%04d-01-01 00:00:00', $year_from);
            $meta_query[] = [
                'key'     => 'auction_date_latest',
                'value'   => $start_dt,
                'compare' => '>=',
                'type'    => 'DATETIME',
            ];
        } elseif ($year_to) {
            $end_dt = sprintf('%04d-12-31 23:59:59', $year_to);
            $meta_query[] = [
                'key'     => 'auction_date_latest',
                'value'   => $end_dt,
                'compare' => '<=',
                'type'    => 'DATETIME',
            ];
        }

        // Solo traer vehicles con thumbnail
        /*$meta_query[] = [
            'key'     => '_thumbnail_id',
            'compare' => 'EXISTS',
        ];*/

        // Tax query (brands)
        $tax_query = [];
        if ($brand_slug !== '') {
            $tax_query[] = [
                'taxonomy' => 'vehicle_brand',
                'field'    => 'slug',
                'terms'    => [$brand_slug],
            ];
        }

        // ...después de construir $meta_query con status y rango de años:

        // Filtrar por tipo de vehículo SOLO en la página "vehicles-for-sale"
        if (is_page('vehicles-for-sale')) {
            // Si el ACF guarda exactamente "private-sale", usa '=' (más rápido):
            $meta_query[] = [
                'key'     => 'type_of_vehicle',
                'value'   => 'private-sale',
                'compare' => '=',
            ];
        }

        if (is_page('buy-it-now')) {
            // Fin de hoy en el timezone de WP (incluye todo el día de hoy)
            $end_today = date_i18n('Y-m-d 23:59:59', current_time('timestamp'));

            $meta_query[] = [
                'relation' => 'AND',

                // 1) status != 'sold' (case/whitespace-insensitive)
                [
                    'relation' => 'OR',
                    [
                        'key'     => 'status',
                        'value'   => '^[[:space:]]*sold[[:space:]]*$',
                        'compare' => 'NOT REGEXP',
                    ],
                    // si quieres incluir posts sin status, vuelve a agregar este bloque:
                    // [ 'key' => 'status', 'compare' => 'NOT EXISTS' ],
                ],

                // 2) type_of_vehicle != 'private-sale' (case/whitespace-insensitive)
                [
                    'relation' => 'OR',
                    [
                        'key'     => 'type_of_vehicle',
                        'value'   => '^[[:space:]]*private-sale[[:space:]]*$',
                        'compare' => 'NOT REGEXP',
                    ],
                    // idem nota de arriba:
                    // [ 'key' => 'type_of_vehicle', 'compare' => 'NOT EXISTS' ],
                ],

                // 3) Fecha <= fin de hoy (excluir futuros)
                [
                    'key'     => 'auction_date_latest', // <-- cambia a 'auction_date_latest' si ese es tu meta key
                    'value'   => $end_today,
                    'compare' => '<=',
                    'type'    => 'DATETIME',
                ],
            ];
        }

        // Query
        $argsVehicle = [
            'post_type'      => $opt['post_type'],
            'posts_per_page' => $ppp,
            'paged'          => $paged,
            'meta_query'     => $meta_query,
            'meta_key'       => $auction_date_meta,
            'orderby'        => 'meta_value',
            'order'          => 'DESC',
            'meta_type'      => 'DATETIME',
        ];

        if ($q !== '') {
            $argsVehicle['s'] = $q; // Busca en título/contenido
        }
        if (!empty($tax_query)) {
            $argsVehicle['tax_query'] = $tax_query;
        }

        // ===== Orden dinámico según order_by =====
        switch ($order_by) {
            case 'lot':
                // Solo con número de lote y orden numérico
                $meta_query[] = [
                    'key'     => 'lot_number_latest',
                    'compare' => 'EXISTS',
                ];
                $argsVehicle['meta_query'] = $meta_query;

                $argsVehicle['meta_key'] = 'lot_number_latest';
                $argsVehicle['orderby']  = 'meta_value_num';
                $argsVehicle['order']    = 'ASC'; // cambia a DESC si lo prefieres
                unset($argsVehicle['meta_type']);
                break;

            case 'low-to-high':
                // Precio estimado bajo (ASC) – numérico
                $meta_query[] = [
                    'key'     => 'estimate_low',
                    'compare' => 'EXISTS',
                ];
                $argsVehicle['meta_query'] = $meta_query;

                $argsVehicle['meta_key'] = 'estimate_low';
                $argsVehicle['orderby']  = 'meta_value_num';
                $argsVehicle['order']    = 'ASC';
                unset($argsVehicle['meta_type']);
                break;

            case 'high-to-low':
                // Precio estimado bajo (DESC) – numérico
                $meta_query[] = [
                    'key'     => 'estimate_low',
                    'compare' => 'EXISTS',
                ];
                $argsVehicle['meta_query'] = $meta_query;

                $argsVehicle['meta_key'] = 'estimate_low';
                $argsVehicle['orderby']  = 'meta_value_num';
                $argsVehicle['order']    = 'DESC';
                unset($argsVehicle['meta_type']);
                break;

            case 'oldest':
                // Fecha más antigua primero (string "Y-m-d H:i")
                $argsVehicle['meta_key']  = $auction_date_meta;
                $argsVehicle['orderby']   = 'meta_value';
                $argsVehicle['order']     = 'ASC';
                $argsVehicle['meta_type'] = 'CHAR';
                break;

            case 'newest':
                // Fecha más reciente primero
                $argsVehicle['meta_key']  = $auction_date_meta;
                $argsVehicle['orderby']   = 'meta_value';
                $argsVehicle['order']     = 'DESC';
                $argsVehicle['meta_type'] = 'CHAR';
                break;

            default:
                // Deja la orden base por fecha según CURRENT/PAST
                break;
        }

        $vehicles = new WP_Query($argsVehicle);

        // Years
        $yf_sel = $year_from_param;
        $yt_sel = $year_to_param;
        $minYear = (int) $opt['min_year'];
        $maxYear = (int) date('Y');

        ob_start();
    ?>
        <form class="auction_result-filter" method="get" action="">
            <div class="auction_result-filter-search">
                <input type="search" name="search_vehicle" placeholder="Search for..." value="<?php echo esc_attr($q); ?>">
                <button type="submit">Go</button>
            </div>

            <div class="auction_result-filter-select">
                <?php
                $brands = get_terms([
                    'taxonomy'   => 'vehicle_brand',
                    'hide_empty' => true,
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                ]);
                ?>
                <select name="vehicle_brand" onchange="this.form.submit()">
                    <option value=""><?php esc_html_e('All Models'); ?></option>
                    <?php if (!is_wp_error($brands) && $brands): ?>
                        <?php foreach ($brands as $term): ?>
                            <option value="<?php echo esc_attr($term->slug); ?>" <?php selected($brand_slug, $term->slug); ?>>
                                <?php echo esc_html($term->name); ?>
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
                    <?php if (NOT_APPEAR): ?>
                        <option value="oldest" <?php selected($order_by, 'oldest');       ?>><?php esc_html_e('Date - Oldest first'); ?></option>
                        <option value="newest" <?php selected($order_by, 'newest');       ?>><?php esc_html_e('Date - Newest first'); ?></option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="auction_result-filter-select">
                <select name="vehicle_status" onchange="this.form.submit()">
                    <option value="">Select status</option>
                    <?php
                    $status_selected = $vehicle_status ?: '';
                    if (is_page('buy-it-now')) {
                        $status_opts = [
                            'available' => 'Available for Sale',
                            'appraisal' => 'Appraisal',
                            'allocated' => 'Allocated',
                        ];
                    } else {
                        $status_opts = [
                            'available' => 'Available for Sale',
                            'appraisal' => 'Appraisal',
                            'allocated' => 'Allocated',
                            'sold'      => 'Sold'
                        ];
                    }
                    foreach ($status_opts as $val => $label): ?>
                        <option value="<?php echo esc_attr($val); ?>" <?php selected($status_selected, $val); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if (NOT_APPEAR): ?>
                <div class="auction_result-filter-year">
                    <select name="year_from" onchange="this.form.submit()">
                        <option value=""><?php esc_html_e('From'); ?></option>
                        <?php for ($y = $minYear; $y <= $maxYear; $y++): ?>
                            <option value="<?php echo $y; ?>" <?php selected($yf_sel, (string)$y); ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                    <p><?php esc_html_e('To'); ?></p>
                    <select name="year_to" onchange="this.form.submit()">
                        <option value=""><?php esc_html_e('To'); ?></option>
                        <?php for ($y = $minYear; $y <= $maxYear; $y++): ?>
                            <option value="<?php echo $y; ?>" <?php selected($yt_sel, (string)$y); ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            <?php else: ?>
                <div class="w-100"></div>
            <?php endif; ?>

            <div class="auction_result-filter-page">
                <p>
                    <?php esc_html_e('Showing'); ?>
                    <select id="blog-perpage" class="blog_section-filter-page" name="posts_per_page" onchange="this.form.submit()">
                        <option value="6" <?php selected((int)$ppp, 6);  ?>>6</option>
                        <option value="12" <?php selected((int)$ppp, 12); ?>>12</option>
                        <option value="24" <?php selected((int)$ppp, 24); ?>>24</option>
                    </select>
                    <?php esc_html_e('Per Page'); ?>
                </p>
            </div>
        </form>

        <?php if ($vehicles->have_posts()): ?>
            <div class="w-100">
                <?php while ($vehicles->have_posts()) : $vehicles->the_post(); ?>
                    <?php
                    $id = get_the_ID();
                    // Función de render propia (como en tu template)
                    if (function_exists('hnh_render_vehicle_item')) {
                        hnh_render_vehicle_item($id);
                    } else {
                        // Fallback mínimo
                        printf('<article><a href="%s">%s</a></article>', esc_url(get_permalink()), esc_html(get_the_title()));
                    }
                    ?>
                <?php endwhile;
                wp_reset_postdata(); ?>
            </div>

            <?php
            $pagination = paginate_links([
                'total'     => (int) $vehicles->max_num_pages,
                'current'   => $paged,
                'mid_size'  => 2,
                'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="19" height="14" viewBox="0 0 19 14" fill="none"><path d="M19 7L1.00049 7M1.00049 7L7.00049 13M1.00049 7L7.0005 0.999999" stroke="#8C6E47"/></svg>',
                'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="19" height="14" viewBox="0 0 19 14" fill="none"><path d="M-7.15494e-08 7L17.9995 7M17.9995 7L11.9995 1M17.9995 7L11.9995 13" stroke="#8C6E47"/></svg>',
                'add_args'  => array_filter([
                    'search_vehicle' => $q,
                    'vehicle_brand'  => $brand_slug,
                    'vehicle_model'  => $_GET['vehicle_model']  ?? '',
                    'order_by'       => $_GET['order_by']       ?? '',
                    'vehicle_status' => $vehicle_status,
                    'year_from'      => $year_from_param,
                    'year_to'        => $year_to_param,
                    'posts_per_page' => $ppp,
                ], static fn($v) => $v !== '' && $v !== null),
            ]);

            if ($pagination) {
                echo '<div class="auction_result-pagination with_border">' . $pagination . '</div>';
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
