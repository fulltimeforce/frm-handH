<?php

// Día con sufijo ordinal: 1st, 2nd, 3rd, 4th...
function hnh_ordinal_day($day)
{
    $day = (int) $day;
    if ($day % 100 >= 11 && $day % 100 <= 13) return $day . 'th';
    switch ($day % 10) {
        case 1:
            return $day . 'st';
        case 2:
            return $day . 'nd';
        case 3:
            return $day . 'rd';
        default:
            return $day . 'th';
    }
}

/**
 * Formatea un datetime MySQL (Y-m-d H:i:s) a:
 *   "Wednesday, February 12th: From 12:00 PM (Noon)"
 *
 * @param string $mysql_datetime  Ej: "2025-08-20 00:00:00"
 * @param bool   $assume_noon_if_midnight  Si true y la hora es 00:00, muestra 12:00 PM (Noon)
 * @return string
 */
function hnh_format_mysql_datetime_friendly($mysql_datetime, $assume_noon_if_midnight = true)
{
    if (! $mysql_datetime) return '';

    try {
        $tz = function_exists('wp_timezone') ? wp_timezone() : new DateTimeZone(wp_timezone_string() ?: 'UTC');
        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $mysql_datetime, $tz);
        if (! $dt) $dt = new DateTime($mysql_datetime, $tz);
    } catch (Exception $e) {
        return '';
    }

    // Si viene 00:00 y quieres asumir mediodía, cámbialo a 12:00
    if ($assume_noon_if_midnight && $dt->format('H:i') === '00:00') {
        $dt->setTime(12, 0); // 12:00
    }

    $dow   = $dt->format('l');         // Wednesday
    $month = $dt->format('F');         // February
    $day   = hnh_ordinal_day($dt->format('j')); // 12th

    $time_label = $dt->format('g:i A'); // 12:00 PM
    // Etiqueta especial Noon/Midnight
    $note = '';
    if ($dt->format('g:i A') === '12:00 PM') {
        $note = ' (Noon)';
    } elseif ($dt->format('g:i A') === '12:00 AM') {
        $note = ' (Midnight)';
    }

    return sprintf('%s, %s %s: From %s%s', $dow, $month, $day, $time_label, $note);
}

/**
 * Formatea un datetime (string "Y-m-d H:i:s") a "12th Feb, 2025 - 9:00 am"
 */
function hnh_format_auction_datetime($datetime_string)
{
    if (empty($datetime_string)) return '';
    $ts = strtotime($datetime_string);
    if ($ts === false) return esc_html($datetime_string);
    // date_i18n respeta el timezone de WP
    return date_i18n('jS M, Y - g:i a', $ts);
}

/**
 * Renderiza la tarjeta de un auction.
 *
 * @param int      $auction_id  ID del post tipo "auction".
 * @param int|null $venue_id    ID del Venue (opcional). Si es 0/empty, intenta leerlo de ACF "template_venue".
 */
function hnh_render_auction_card($auction_id, $venue_id = 0)
{
    if (!$auction_id) return;

    // Asegurar IDs enteros
    $auction_id = (int) $auction_id;
    $venue_id   = (int) $venue_id;

    // Si no enviaron venue, tomarlo del ACF del auction
    if (!$venue_id) {
        $venue_id = (int) get_field('template_venue', $auction_id);
    }

    // Datos del auction
    $title        = get_the_title($auction_id);
    $permalink    = get_permalink($auction_id);
    $lots         = get_field('lots', $auction_id);
    $auction_date = get_field('auction_date', $auction_id); // guardado como "Y-m-d H:i:s"
    $date_label   = $auction_date ? hnh_format_auction_datetime($auction_date) : '';

    // Ubicación desde el Venue (ej.: tu campo 'slider_subtitle')
    $ubication = $venue_id ? get_field('slider_subtitle', $venue_id) : '';

    // Thumbnail (con fallback)
    $thumb_url = get_the_post_thumbnail_url($auction_id, 'large');
    if (!$thumb_url) {
        // Fallback: usa la constante IMG si existe, sino construye ruta al tema
        $img_base = defined('IMG') ? IMG : get_template_directory_uri() . '/assets/img';
        $thumb_url = $img_base . '/auction1.png';
    }

    // Link al venue si existe
    $venue_link = $venue_id ? get_permalink($venue_id) : '';

    // -------- NUEVO: Contenido del post (tu "content") --------
    $content_html = get_post_field('post_content', $auction_id);
    if ($content_html) {
        // Aplica shortcodes, embeds, autop
        $content_html = apply_filters('the_content', $content_html);
        // Opcionalmente, sanitiza etiquetas permitidas:
        // $content_html = wp_kses_post($content_html);
    } else {
        // Fallback opcional: excerpt si no hay contenido
        $excerpt = get_the_excerpt($auction_id);
        $content_html = $excerpt ? wpautop(esc_html($excerpt)) : '';
    }
    // ----------------------------------------------------------
?>
    <div class="auction">
        <div class="auction_thumb">
            <div class="thumb">
                <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($title); ?>">
            </div>
        </div>

        <div class="auction_info">
            <h2><?php echo esc_html($title); ?></h2>

            <div class="content">
                <ul>
                    <?php if ($date_label): ?>
                        <li>Date: <b><?php echo esc_html($date_label); ?></b></li>
                    <?php endif; ?>

                    <?php if (!empty($ubication)): ?>
                        <li>Location: <b><?php echo esc_html($ubication); ?></b></li>
                    <?php endif; ?>

                    <?php if (!empty($lots)): ?>
                        <li>View Lots: <b><?php echo esc_html($lots); ?></b></li>
                    <?php endif; ?>
                </ul>
            </div>

            <?php if (!empty($content_html)): ?>
                <div class="content">
                    <?php echo $content_html; ?>
                </div>
            <?php endif; ?>

            <?php if ($venue_link): ?>
                <a alt="Venue Details" href="<?php echo esc_url($venue_link); ?>">
                    Venue Details
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="12" viewBox="0 0 22 12" fill="none">
                        <path d="M0.25 6H20.25M20.25 6L15.25 1M20.25 6L15.25 11" stroke="#8C6E47" stroke-width="1.20833" />
                    </svg>
                </a>
            <?php endif; ?>
        </div>

        <div class="auction_actions">
            <ul>
                <li>
                    <?php if (is_page('auction-results')): ?>
                        <a href="<?php echo esc_url($permalink); ?>" alt="View Auction Results">View Auction Results</a>
                    <?php else: ?>
                        <a href="<?php echo esc_url($permalink); ?>" alt="View Upcoming Lots">View Upcoming Lots</a>
                    <?php endif; ?>
                </li>
                <li><a href="<?php echo esc_url(home_url('get-a-valuation')) ?>">Consign Your Classic</a></li>

                <?php
                $watch_live = get_field('watch_live', $auction_id);
                $toogle_watch_live = get_field('toogle_watch_live', $auction_id);
                if ($toogle_watch_live && !empty($watch_live)):
                ?>
                    <li><a href="<?php echo $watch_live; ?>" target="_blank" alt="Watch Live">Watch Live</a></li>
                <?php endif; ?>

                <li><a href="<?php echo esc_url(home_url('ways-to-bid')) ?>" alt="Learn how to bid">Learn how to bid</a></li>

                <?php
                $catalogue = get_field('view_e-catalogue', $auction_id);
                if (!empty($catalogue)):
                ?>
                    <li><a href="<?php echo $catalogue; ?>" target="_blank" alt="View E-Catalogue">View E-Catalogue</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="auction_keytimes">
            <div class="auction_keytimes-grid">
                <div class="w-100">
                    <p>KEY TIMES:</p>
                </div>

                <?php
                $viewing_raw  = get_field('auction_date', $auction_id);          // "2025-08-20 00:00:00"
                $sale_raw     = get_field('viewing_dates', $auction_id);

                $viewing_txt  = hnh_format_mysql_datetime_friendly($viewing_raw, true); // true => 00:00 => Noon
                // $sale_txt     = hnh_format_mysql_datetime_friendly($sale_raw, true);
                ?>

                <?php if ($sale_raw) : ?>
                    <div class="w-100">
                        <p>Viewing:</p>
                        <p><?php echo $sale_raw; ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($viewing_txt) : ?>
                    <div class="w-100">
                        <p>Sale Time:</p>
                        <p><?php echo esc_html($viewing_txt); ?></p>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
<?php
}
