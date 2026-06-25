<?php

get_header();

$current_lot = get_field('lot_number_latest');
$estimate_high = get_field('estimate_high');
$estimate_low = get_field('estimate_low');
$sold_price = get_field('sold_price');
$short_text = get_field('title_sub');
$status = get_field('status');

$vehicle_video = '';
$post_id = get_the_ID();

$auction = get_field('auction_number_latest');

$auction_name = '';
$auction_permalink = '';
$auction_number = '';
$auction_date = '';
$auction_id = 0;

if ($auction) {
    $auction_id = $auction->ID;

    $auction_name = $auction->post_title;
    $auction_permalink = get_permalink($auction_id);

    $auction_number = get_field('sale_number', $auction_id);

    $check_provisional_number = get_field('show_provisional_numbers', $auction_id);
    if (!$check_provisional_number && stripos($current_lot, 'p')) {
        $current_lot = '';
    }

    $auction_date = get_field('auction_date', $auction_id);
}

$nav_context = hnh_get_auction_list_nav_context_from_request();
$nav_query_args = hnh_auction_list_nav_query_args($nav_context);
$auction_return_url = $auction_permalink;
if ($nav_query_args && $auction_permalink) {
    $auction_return_url = add_query_arg($nav_query_args, $auction_permalink);
}

?>

<style>
    .listing_fullview {
        position: fixed;
    }

    .listing_grid {
        background: white;
    }

    .listing_grid-item img {
        cursor: zoom-in;
        pointer-events: all;
    }

    @media (min-width: 1420px) {
        .listing_info-details {
            margin-top: 3.3333333333vw;
        }
    }

    @media (max-width: 1420px) {
        .listing_info-details {
            margin-top: 44px;
        }
    }

    @media (max-width: 768px) {
        .listing_info-details {
            margin-top: 32px;
        }

        .accordionjs .acc_section .acc_head p {
            display: none !important;
        }
    }
</style>

<section class="listing_head">
    <div class="container">
        <div class="listing_head-col">
            <div>
                <?php if (!isset($_GET['c']) && $auction_number): ?>
                    <?php if (!empty($auction_return_url)): ?>
                        <a href="<?php echo esc_url($auction_return_url); ?>" class="listing_btn-white p14">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M2.60156 8H2.60756M2.60156 14H2.60756M2.60156 2H2.60756M5.60156 8H13.4016M5.60156 14H13.4016M5.60156 2H13.4016" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            Return to Auction List</a>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($auction_date): ?>
                    <p class="p20">
                        <?php
                        $date = new DateTime($auction_date);
                        $formatted_date = $date->format('jS M, Y H:i');
                        echo $formatted_date;
                        ?>
                    </p>
                <?php endif; ?>
            </div>

            <?php if ($auction_name): ?>
                <p class="listing_head-title"><?php echo $auction_name; ?></p>
            <?php endif; ?>
        </div>
        <div class="listing_head-col">
            <?php if ($current_lot): ?>
                <div class="listing_head-count">
                    <p class="p17">Current Lot</p>
                    <span class="p24"><?php echo $current_lot; ?></span>
                </div>
            <?php endif; ?>

            <div class="listing_head-actions">

                <?php if (!isset($_GET['c'])): ?>
                    <?php
                    $prev_post = null;
                    $next_post = null;

                    if ($auction_id > 0) {
                        $prev_id = hnh_get_adjacent_lot_vehicle_id($post_id, $auction_id, 'prev', $nav_context);
                        $next_id = hnh_get_adjacent_lot_vehicle_id($post_id, $auction_id, 'next', $nav_context);

                        if ($prev_id) {
                            $prev_post = get_post($prev_id);
                        }
                        if ($next_id) {
                            $next_post = get_post($next_id);
                        }
                    }

                    $prev_url = $prev_post
                        ? ($nav_query_args
                            ? add_query_arg($nav_query_args, get_permalink($prev_post->ID))
                            : get_permalink($prev_post->ID))
                        : '';
                    $next_url = $next_post
                        ? ($nav_query_args
                            ? add_query_arg($nav_query_args, get_permalink($next_post->ID))
                            : get_permalink($next_post->ID))
                        : '';
                    ?>

                    <?php if ($prev_post) : ?>
                        <a href="<?php echo esc_url($prev_url); ?>" class="listing_btn-brown p14">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <g clip-path="url(#clip0_1921_36983)">
                                    <path d="M14.3008 8L1.70078 8M1.70078 8L8.00078 15M1.70078 8L8.00078 0.999999" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </g>
                                <defs>
                                    <clipPath id="clip0_1921_36983">
                                        <rect width="16" height="16" fill="white" transform="translate(16 16) rotate(-180)" />
                                    </clipPath>
                                </defs>
                            </svg>
                            Previous Lot
                        </a>
                    <?php else : ?>
                        <a class="listing_btn-brown p14 disabled">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <g clip-path="url(#clip0_1921_36983)">
                                    <path d="M14.3008 8L1.70078 8M1.70078 8L8.00078 15M1.70078 8L8.00078 0.999999" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </g>
                                <defs>
                                    <clipPath id="clip0_1921_36983">
                                        <rect width="16" height="16" fill="white" transform="translate(16 16) rotate(-180)" />
                                    </clipPath>
                                </defs>
                            </svg>
                            Previous Lot
                        </a>
                    <?php endif; ?>


                    <?php if ($next_post) : ?>
                        <a href="<?php echo esc_url($next_url); ?>" class="listing_btn-brown p14">
                            Following Lot
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <g clip-path="url(#clip0_1921_36988)">
                                    <path d="M1.69922 8H14.2992M14.2992 8L7.99922 1M14.2992 8L7.99922 15" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </g>
                                <defs>
                                    <clipPath id="clip0_1921_36988">
                                        <rect width="16" height="16" fill="white" />
                                    </clipPath>
                                </defs>
                            </svg>
                        </a>
                    <?php else : ?>
                        <a class="listing_btn-brown p14 disabled">
                            Following Lot
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <g clip-path="url(#clip0_1921_36988)">
                                    <path d="M1.69922 8H14.2992M14.2992 8L7.99922 1M14.2992 8L7.99922 15" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </g>
                                <defs>
                                    <clipPath id="clip0_1921_36988">
                                        <rect width="16" height="16" fill="white" />
                                    </clipPath>
                                </defs>
                            </svg>
                        </a>
                    <?php endif; ?>

                <?php endif; ?>

                <a href="#share" class="listing_btn-white p14">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M6.11222 9.057L10.8932 11.843M10.8862 4.157L6.11222 6.943M14.7992 3.1C14.7992 4.2598 13.859 5.2 12.6992 5.2C11.5394 5.2 10.5992 4.2598 10.5992 3.1C10.5992 1.9402 11.5394 1 12.6992 1C13.859 1 14.7992 1.9402 14.7992 3.1ZM6.39922 8C6.39922 9.1598 5.45902 10.1 4.29922 10.1C3.13942 10.1 2.19922 9.1598 2.19922 8C2.19922 6.8402 3.13942 5.9 4.29922 5.9C5.45902 5.9 6.39922 6.8402 6.39922 8ZM14.7992 12.9C14.7992 14.0598 13.859 15 12.6992 15C11.5394 15 10.5992 14.0598 10.5992 12.9C10.5992 11.7402 11.5394 10.8 12.6992 10.8C13.859 10.8 14.7992 11.7402 14.7992 12.9Z" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Share
                </a>
            </div>
        </div>
    </div>
</section>

<?php $gallery = get_field('gallery_vehicle'); ?>

<?php if ($gallery && is_array($gallery)): ?>
    <section class="listing_images">
        <div class="container">
            <?php
            $imgs = [];
            foreach ($gallery as $item) {
                $url = $alt = '';
                if (is_array($item)) { // array de ACF
                    $id  = $item['ID'] ?? 0;
                    $url = $item['url'] ?? ($id ? wp_get_attachment_image_url($id, 'full') : '');
                    $alt = $item['alt'] ?? ($id ? get_post_meta($id, '_wp_attachment_image_alt', true) : ($item['title'] ?? ''));
                } elseif (is_numeric($item)) { // ID
                    $id  = (int) $item;
                    $url = wp_get_attachment_image_url($id, 'full');
                    $alt = get_post_meta($id, '_wp_attachment_image_alt', true);
                } else { // string URL
                    $url = $item;
                    $alt = '';
                }
                if ($url) $imgs[] = ['url' => $url, 'alt' => $alt];
            }

            $total = count($imgs);
            if ($total):
                $first = $imgs[0];
            ?>

                <div style="display: none">
                    <?php foreach ($imgs as $n => $img): ?>
                        <input type="hidden" class="hidden_image_<?php echo intval($n) + 1; ?>" value="<?php echo esc_url($img['url']); ?>">
                    <?php endforeach; ?>
                </div>

                <div class="listing_images-main">
                    <img style="pointer-events: all;" data-index="0" class="wh-100 thumbnail-post" src="<?php echo esc_url($first['url']); ?>" alt="<?php echo esc_attr($first['alt'] ?: 'vehicle'); ?>">
                    <div id="openFullView" class="listing_images-counter p18" data-total="<?php echo $total; ?>">1/<?php echo $total; ?></div>
                    <div style="pointer-events: none;" id="openGrid" class="listing_images-grid">
                        <img src="<?php echo IMG; ?>/zoom-icon.svg" alt="icon">
                    </div>
                </div>

                <div class="listing_images-slider splide">
                    <div class="splide__track">
                        <ul class="splide__list">
                            <?php foreach ($imgs as $img): ?>
                                <li class="splide__slide">
                                    <img class="wh-100" src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr($img['alt'] ?: 'Vehicle Image'); ?>">
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php else: ?>
    <section class="thumbnail_big">
        <div class="thumbnail_big-container">
            <?php if (has_post_thumbnail($post_id)):
                $thumb_id  = get_post_thumbnail_id($post_id);
                $thumb_url = wp_get_attachment_image_url($thumb_id, 'full');
                $thumb_alt = get_post_meta($thumb_id, '_wp_attachment_image_alt', true);
                if ($thumb_alt === '') {
                    $thumb_alt = get_the_title($post_id);
                }
            ?>
                <div class="w-100">
                    <img class="w-100" src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($thumb_alt); ?>">
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<section class="listing_info">
    <div class="container">
        <h1><?php the_title(); ?></h1>
        <?php if ($short_text): ?>
            <p class="listing_info-subtitle p24"><?php echo $short_text; ?></p>
        <?php endif; ?>
        <div class="listing_divider"></div>
        <div class="listing_info-bid">

            <?php
            $upcoming_auctions_ids = [];
            $date_meta_key = 'auction_date';
            $now_mysql     = current_time('mysql');

            $future_auctions = get_posts([
                'post_type'           => 'auction',
                'post_status'         => 'publish',
                'fields'              => 'ids',
                'no_found_rows'       => true,
                'ignore_sticky_posts' => true,

                'meta_query' => [[
                    'key'     => $date_meta_key,
                    'value'   => $now_mysql,
                    'compare' => '>=',
                    'type'    => 'CHAR',
                ]],

                // Ordena por la fecha más próxima primero
                'meta_key'  => $date_meta_key,
                'orderby'   => 'meta_value',
                'order'     => 'ASC',
            ]);

            foreach ($future_auctions as $auction_id) {
                // Lee el sale_number (puedes usar get_field si prefieres)
                $sale_number = get_post_meta($auction_id, 'sale_number', true);
                if ($sale_number !== '' && is_numeric($sale_number)) {
                    $upcoming_auctions_ids[] = (int) $sale_number;
                }
            }

            // Limpieza: únicos y reindexados
            $upcoming_auctions_ids = array_values(array_unique($upcoming_auctions_ids));
            ?>
            <?php if ($status && strtolower($status) == 'allocated' && in_array(intval($auction_number), $upcoming_auctions_ids)): ?>
                <?php $placebid = get_field('lot_link'); ?>
                <?php if ($placebid): ?>
                    <div>
                        <div class="listing_info-bid-lot">
                            <div class="auction-bid">
                                <div class="auction-bid-value">
                                    <a href="<?php echo $placebid; ?>" target="_blank" class="btn-bid w-100" data-lot-id="62057" data-auctionid="552" data-session="622" data-lotnumber="1" data-hastelbid="False" data-istimed="0" data-reload="0" data-vatable="False" data-ga-cat="bidding" data-ga-act="submit" data-ga-lbl="/bid" data-ba-cat="" data-ba-lbl="" data-ba-cur="">Place Bid</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div></div>
                <?php endif; ?>
            <?php else: ?>
                <div></div>
            <?php endif; ?>

            <?php $estimate_html = ''; ?>

            <?php if ($status && !empty($status)): ?>

                <?php if (strtolower($status) == 'sold'): ?>

                    <?php
                    $amount  = $sold_price  ? (float) preg_replace('/[^\d.\-]/', '', (string) $sold_price)  : null;
                    if ($amount) {
                        $estimate_html = '£' . esc_html(number_format_i18n($amount, 0));
                    }
                    ?>

                    <?php if ($estimate_html): ?>
                        <div>
                            <p class="p17">Sold for</p>
                            <p class="gold-text"><?php echo $estimate_html; ?></p>
                        </div>
                    <?php endif; ?>

                <?php else: ?>

                    <?php
                    $low  = $estimate_low  ? (float) preg_replace('/[^\d.\-]/', '', (string) $estimate_low)  : null;
                    $high = $estimate_high ? (float) preg_replace('/[^\d.\-]/', '', (string) $estimate_high) : null;

                    if ($low && $high) {
                        // Si existen ambos
                        $estimate_html = '£' . esc_html(number_format_i18n($low, 0)) . ' - £' . esc_html(number_format_i18n($high, 0));
                    } elseif ($low) {
                        // Solo low
                        $estimate_html = '£' . esc_html(number_format_i18n($low, 0));
                    } elseif ($high) {
                        // Solo high
                        $estimate_html = '£' . esc_html(number_format_i18n($high, 0));
                    }
                    ?>

                    <?php if ($estimate_html): ?>
                        <div>
                            <p class="p17">Estimate</p>
                            <p class="gold-text"><?php echo $estimate_html; ?></p>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>

            <?php endif; ?>

        </div>
        <div class="listing_info-details">

            <div class="listing_info-details-tabs">
                <div class="faq_list">

                    <ul id="listingTab" class="accordionjs">
                        <li>
                            <div>
                                <h3>Lot Details</h3>
                                <p style="color:#8c6e47;font-family:GothamMedium;font-weight:300;">Read More</p>
                            </div>
                            <div>
                                <div class="description">
                                    <?php
                                    $desc = function_exists('get_field') ? get_field('description') : '';
                                    if ($desc) {
                                        echo apply_filters('the_content', $desc);
                                    }
                                    ?>
                                </div>
                            </div>
                        </li>
                    </ul>

                </div>
            </div>
        </div>

        <?php
        /**
         * Resuelve un usuario a partir de un valor ACF (User ID | WP_User | array).
         * Retorna WP_User o null.
         */
        if (!function_exists('hnh_resolve_user')) {
            function hnh_resolve_user($val): ?WP_User
            {
                if (empty($val)) return null;

                // Si viene un array (por si algún día activan "Select Multiple")
                if (is_array($val)) {
                    $val = reset($val);
                }

                $user_id = 0;

                if ($val instanceof WP_User) {
                    $user_id = (int) $val->ID;
                } elseif (is_object($val) && isset($val->ID)) {
                    // Por si viene algún objeto con propiedad ID
                    $user_id = (int) $val->ID;
                } else {
                    $user_id = (int) $val;
                }

                if ($user_id <= 0) return null;

                $u = get_user_by('id', $user_id);
                return ($u instanceof WP_User) ? $u : null;
            }
        }

        /** 1) assigned_to (User ID). Si está vacío, usamos contact_rep (User ID). */
        $user = hnh_resolve_user(get_field('assigned_to'));
        if (!$user) {
            $user = hnh_resolve_user(get_field('contact_rep'));
        }

        if ($user) :
            $user_id  = (int) $user->ID;

            // Nombre: si antes usabas get_the_title(), ahora sacamos display_name
            $name     = trim($user->display_name ?: ($user->first_name . ' ' . $user->last_name));
            if (!$name) $name = $user->user_login;

            // ACF en usuarios: pasar "user_{$id}" como segundo parámetro
            $email    = (string) get_field('team_email', 'user_' . $user_id);
            $phone    = (string) (get_field('team_phone', 'user_' . $user_id) ?: get_field('phone', 'user_' . $user_id));
            $position = (string) get_field('job_position', 'user_' . $user_id);

            // Imagen: si tienes un ACF tipo image para el usuario (ej: thumbnail_member / avatar)
            // Ajusta el nombre del campo si ya tienes uno.
            $img_url = '';
            $acf_img = get_field('thumbnail_member', 'user_' . $user_id); // <- cambia si tu campo se llama diferente

            if (is_array($acf_img) && !empty($acf_img['sizes']['medium'])) {
                $img_url = $acf_img['sizes']['medium'];
            } elseif (is_string($acf_img) && filter_var($acf_img, FILTER_VALIDATE_URL)) {
                $img_url = $acf_img;
            } else {
                // fallback: avatar WP
                $img_url = get_avatar_url($user_id, ['size' => 300]);
            }

            if (!$img_url && defined('IMG')) $img_url = IMG . '/face2.png';

            // Link al perfil: si tienes author page o una página /member/{nicename}/
            // Ajusta según tu routing.
            $profile_url = get_author_posts_url($user_id); // o home_url('/member/' . $user->user_nicename . '/');

            if ($name && ($email || $phone)) : ?>
                <div class="listing_info-contact" style="margin-bottom:0">
                    <div class="listing_info-contact-info">
                        <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($name); ?>">
                        <div>
                            <p class="listing_info-contact-subtitle">
                                If you would like to enquire further, please contact:
                            </p>
                            <p class="listing_info-contact-title"><?php echo esc_html($name); ?></p>

                            <?php if ($position): ?>
                                <p class="listing_info-contact-subtitle">- <?php echo esc_html($position); ?></p>
                            <?php endif; ?>

                            <div>
                                <?php if ($email): ?>
                                    <p class="listing_info-contact-text">
                                        Email: <span><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></span>
                                    </p>
                                <?php endif; ?>

                                <?php if ($phone): ?>
                                    <p class="listing_info-contact-text">
                                        Tel: <span><?php echo esc_html($phone); ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="listing_info-contact-btn w-100">
                        <a href="<?php echo esc_url($profile_url); ?>" class="listing_btn-white p14">
                            View Bio
                            <svg xmlns="http://www.w3.org/2000/svg" width="29" height="16" viewBox="0 0 29 16" fill="none">
                                <path d="M2.5 8H26.5M26.5 8L21.122 2M26.5 8L21.122 14" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-listing_info-contact"></div>
            <?php endif; ?>

        <?php else: ?>
            <div class="empty-listing_info-contact"></div>
        <?php endif; ?>

        <?php if ($vehicle_video): ?>
            <div class="listing_info-image w-100">
                <video class="w-100" autoplay playsinline muted loop>
                    <source src="<?php echo $vehicle_video; ?>">
                </video>
            </div>
        <?php endif; ?>

        <div class="listing_info-share" id="share">
            <p>Share:</p>
            <?php
            $post_url   = get_permalink();
            $post_title = get_the_title();
            ?>

            <ul class="social">
                <li>
                    <button class="button" data-sharer="linkedin" data-url="<?php echo $post_url; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 38 38" fill="none">
                            <g clip-path="url(#clip0_2705_12897)">
                                <path d="M19 37.5C29.2173 37.5 37.5 29.2173 37.5 19C37.5 8.78273 29.2173 0.5 19 0.5C8.78273 0.5 0.5 8.78273 0.5 19C0.5 29.2173 8.78273 37.5 19 37.5Z" stroke="#8C6E47" />
                                <path d="M12.0728 26.1599H14.4288V16.8259H12.0728V26.1599ZM19.4298 21.2409C19.4298 19.7689 20.1238 18.5879 21.7218 18.5879C23.2998 18.5879 23.8098 19.8599 23.8098 21.2409V26.1609H26.1518V20.1469C26.1518 18.0109 25.1168 16.5169 22.8608 16.5169C22.3215 16.5103 21.7896 16.6418 21.3155 16.8989C20.8415 17.1561 20.4412 17.5303 20.1528 17.9859L19.4298 19.3049V16.8249H17.1998V26.1589H19.4298V21.2409ZM20.2008 21.2409V26.9309H16.4288V16.0539H20.2008V16.7479C20.9299 16.0922 21.8792 15.7351 22.8598 15.7479C25.5418 15.7479 26.9228 17.5859 26.9228 20.1479V26.9309H23.0377V21.2409C23.0377 20.3129 22.8378 19.3599 21.7208 19.3599C20.5628 19.3589 20.2028 20.2029 20.2028 21.2409H20.2008ZM13.2358 14.7309C13.5289 14.7339 13.8162 14.6497 14.0614 14.4891C14.3065 14.3284 14.4985 14.0986 14.6127 13.8286C14.727 13.5587 14.7585 13.2609 14.7032 12.9731C14.6479 12.6852 14.5084 12.4203 14.3022 12.2119C14.0961 12.0035 13.8327 11.8611 13.5455 11.8027C13.2582 11.7443 12.9601 11.7725 12.689 11.8839C12.4178 11.9952 12.1859 12.1846 12.0226 12.428C11.8593 12.6714 11.772 12.9578 11.7718 13.251C11.7704 13.4445 11.8074 13.6364 11.8804 13.8156C11.9534 13.9949 12.0611 14.1579 12.1973 14.2954C12.3335 14.433 12.4956 14.5422 12.6741 14.6169C12.8527 14.6917 13.0442 14.7304 13.2378 14.7309H13.2358ZM13.2358 15.5019C12.7899 15.5051 12.3531 15.3758 11.9808 15.1304C11.6086 14.885 11.3176 14.5345 11.1447 14.1235C10.9719 13.7125 10.925 13.2594 11.0101 12.8217C11.0951 12.384 11.3082 11.9814 11.6224 11.665C11.9366 11.3486 12.3376 11.1326 12.7747 11.0444C13.2118 10.9563 13.6652 10.9999 14.0774 11.1698C14.4897 11.3397 14.8422 11.6282 15.0903 11.9987C15.3383 12.3692 15.4708 12.8051 15.4708 13.251C15.4724 13.8454 15.2382 14.4163 14.8196 14.8384C14.4011 15.2605 13.8322 15.4995 13.2378 15.503L13.2358 15.5019ZM11.3038 26.9309V16.0539H15.2037V26.9309H11.3038Z" fill="#8C6E47" />
                            </g>
                            <defs>
                                <clipPath id="clip0_2705_12897">
                                    <rect width="38" height="38" fill="white" />
                                </clipPath>
                            </defs>
                        </svg>
                    </button>
                </li>
                <li>
                    <button class="button" data-sharer="facebook" data-hashtag="hashtag" data-url="<?php echo $post_url; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 38 38" fill="none">
                            <g clip-path="url(#clip0_2705_12904)">
                                <path d="M19 37.5C29.2173 37.5 37.5 29.2173 37.5 19C37.5 8.78273 29.2173 0.5 19 0.5C8.78273 0.5 0.5 8.78273 0.5 19C0.5 29.2173 8.78273 37.5 19 37.5Z" stroke="#8C6E47" />
                                <path d="M16.788 27.667V19.496H15.634C15.4659 19.4957 15.3048 19.4288 15.186 19.31C15.0671 19.1912 15.0003 19.0301 15 18.862V16.275C15.0005 16.107 15.0675 15.9461 15.1863 15.8273C15.3051 15.7085 15.466 15.6415 15.634 15.641H16.788V13.561C16.7883 12.6165 17.1637 11.7107 17.8316 11.0429C18.4996 10.3751 19.4055 10 20.35 10H23.311C23.4791 10 23.6404 10.0668 23.7593 10.1857C23.8782 10.3046 23.945 10.4658 23.945 10.634V13.22C23.945 13.3881 23.8782 13.5494 23.7593 13.6683C23.6404 13.7872 23.4791 13.854 23.311 13.854H21.558C21.4614 13.8545 21.3688 13.8931 21.3005 13.9615C21.2321 14.0298 21.1935 14.1224 21.193 14.219V15.642H23.272C23.4401 15.6423 23.6012 15.7091 23.72 15.828C23.8389 15.9468 23.9057 16.1079 23.906 16.276C23.844 17.152 23.759 18.038 23.684 18.916C23.6705 19.0745 23.5981 19.2221 23.4811 19.3298C23.3641 19.4376 23.211 19.4976 23.052 19.498H21.192V27.669C21.1917 27.8371 21.1249 27.9982 21.006 28.117C20.8872 28.2359 20.7261 28.3027 20.558 28.303H17.421C17.2528 28.3025 17.0916 28.2352 16.9729 28.1159C16.8542 27.9967 16.7877 27.8352 16.788 27.667ZM17.617 27.474H20.365V18.667H22.876L23.062 16.467H20.362V14.22C20.362 13.9036 20.4877 13.6001 20.7114 13.3764C20.9351 13.1527 21.2386 13.027 21.555 13.027H23.114V10.827H20.349C19.6243 10.827 18.9293 11.1148 18.4168 11.6271C17.9043 12.1394 17.6163 12.8343 17.616 13.559V16.467H15.828V18.667H17.616L17.617 27.474Z" fill="#8C6E47" stroke="#8C6E47" stroke-width="0.1" />
                            </g>
                            <defs>
                                <clipPath id="clip0_2705_12904">
                                    <rect width="38" height="38" fill="white" />
                                </clipPath>
                            </defs>
                        </svg>
                    </button>
                </li>
                <li>
                    <button class="button" data-sharer="x" data-title="<?php echo $post_title; ?>" data-hashtags="vehicle" data-url="<?php echo $post_url; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 38 38" fill="none">
                            <g clip-path="url(#clip0_2705_12924)">
                                <path d="M19 37.5C29.2173 37.5 37.5 29.2173 37.5 19C37.5 8.78273 29.2173 0.5 19 0.5C8.78273 0.5 0.5 8.78273 0.5 19C0.5 29.2173 8.78273 37.5 19 37.5Z" stroke="#8C6E47" />
                                <path d="M20.9159 18.1983L27.1186 11H25.6488L20.263 17.2502L15.9614 11H11L17.5049 20.4514L11 28H12.4699L18.1574 21.3996L22.7002 28H27.6616L20.9156 18.1983H20.9159ZM18.9027 20.5347L18.2436 19.5936L12.9995 12.1047H15.2573L19.4893 18.1485L20.1483 19.0896L25.6494 26.9455H23.3917L18.9027 20.5351V20.5347Z" fill="#8C6E47" />
                            </g>
                            <defs>
                                <clipPath id="clip0_2705_12924">
                                    <rect width="38" height="38" fill="white" />
                                </clipPath>
                            </defs>
                        </svg>
                    </button>
                </li>
                <li>
                    <button class="button" data-sharer="email" data-title="<?php echo $post_title; ?>" data-url="<?php echo $post_url; ?>" data-subject="<?php echo $post_title; ?>" data-to="some@email.com">
                        <svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 38 38" fill="none">
                            <g clip-path="url(#clip0_1920_36754)">
                                <path d="M19 37.5C29.2173 37.5 37.5 29.2173 37.5 19C37.5 8.78273 29.2173 0.5 19 0.5C8.78273 0.5 0.5 8.78273 0.5 19C0.5 29.2173 8.78273 37.5 19 37.5Z" stroke="#8C6E47" />
                                <path d="M30 14.1875L20.5815 20.2437C20.2573 20.4493 19.8825 20.5583 19.5 20.5583C19.1175 20.5583 18.7427 20.4493 18.4185 20.2437L9 14.1875M11.1 11H27.9C29.0598 11 30 11.9514 30 13.125V25.875C30 27.0486 29.0598 28 27.9 28H11.1C9.9402 28 9 27.0486 9 25.875V13.125C9 11.9514 9.9402 11 11.1 11Z" stroke="#8C6E47" stroke-linecap="round" stroke-linejoin="round" />
                            </g>
                            <defs>
                                <clipPath id="clip0_1920_36754">
                                    <rect width="38" height="38" fill="white" />
                                </clipPath>
                            </defs>
                        </svg>
                    </button>
                </li>
            </ul>


        </div>

        <?php $type_of_vehicle = get_field('type_of_vehicle'); ?>
        <?php if ($status && strtolower($status) != 'sold'): ?>
            <div class="insurance insurance_share other_forms_mt">
                <div class="actions" style="justify-content: flex-start;">
                    <?php if ($type_of_vehicle == 'auction'): ?>
                        <a target="_blank" href="<?php echo esc_url(get_permalink(123872)); ?>?vehicle=<?php echo get_the_ID(); ?>" title="Telephone Bid">Telephone Bid</a>
                        <a target="_blank" href="<?php echo esc_url(get_permalink(123874)); ?>?vehicle=<?php echo get_the_ID(); ?>" title="Commision Bid">Commision Bid</a>
                        <a target="_blank" href="<?php echo esc_url(get_permalink(123876)); ?>?vehicle=<?php echo get_the_ID(); ?>" title="Request Condition Report">Request Condition Report</a>
                    <?php else: ?>
                        <a target="_blank" href="<?php echo esc_url(get_permalink(48)); ?>" title="Contact Us">Contact Us</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php
        $vehicle_notes = hnh_get_vehicle_footnote(get_the_ID());
        if ($vehicle_notes): ?>
            <div class="listing_info-notes">
                <div class="listing_info-notes-head">
                    <p>Notes for Intending Purchases</p>
                </div>
                <div class="listing_info-notes-body">
                    <?php echo $vehicle_notes; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-listing_info-contact"></div>
        <?php endif; ?>

    </div>
</section>

<?php get_template_part('inc/sections/grid-popup'); ?>
<?php get_template_part('inc/sections/fullview-popup'); ?>

<style>
    .listing_grid-item[data-fullview-index] {
        cursor: pointer;
    }

    .listing_grid.active .listing_grid-scroll-hint {
        display: flex;
    }

    .listing_grid-scroll-hint {
        display: none;
        position: fixed;
        right: 32px;
        bottom: 32px;
        z-index: 102;
        width: 48px;
        height: 76px;
        margin: 0;
        align-items: center;
        justify-content: center;
        pointer-events: none;
        padding: 14px;
        border-radius: 999px;
        background: rgb(238, 233, 226);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
        transition: opacity 0.3s ease;
    }

    .listing_grid-scroll-hint .mousey {
        width: 4px;
        padding: 12px 18px;
        height: 42px;
        border: 2px solid #8C6E47;
        border-radius: 25px;
        opacity: 1;
        box-sizing: content-box;
    }

    .listing_grid-scroll-hint .scroller {
        width: 5px;
        height: 12px;
        border-radius: 25%;
        background-color: #8C6E47;
        animation-name: listing-grid-scroll;
        animation-duration: 2.2s;
        animation-timing-function: cubic-bezier(.15, .41, .69, .94);
        animation-iteration-count: infinite;
    }

    @keyframes listing-grid-scroll {
        0% {
            opacity: 0;
            transform: translateY(0);
        }

        10% {
            opacity: 1;
            transform: translateY(0);
        }

        100% {
            opacity: 0.25;
            transform: translateY(18px);
        }
    }

    @media (max-width: 768px) {
        .listing_grid-scroll-hint {
            right: 20px;
            bottom: 20px;
            width: 40px;
            height: 64px;
            padding: 10px;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sharer.js@0.5.2/sharer.min.js"></script>
<?php get_footer(); ?>
<script>
    jQuery(document).ready(function($) {
        jQuery("#listingTab").accordionjs({
            closeAble: true,
            closeOther: true,
            slideSpeed: 150,
            activeIndex: 100
        });

        const fullView = document.querySelector('.listing_fullview');
        const grid = document.querySelector('.listing_grid');

        if (!fullView || !grid) {
            return;
        }

        function getFullViewSplide() {
            return window.hnhFullViewSplide || null;
        }

        function whenFullViewSplideReady(callback, attempts = 30) {
            const splide = getFullViewSplide();
            if (splide) {
                callback(splide);
                return;
            }
            if (attempts > 0) {
                setTimeout(() => whenFullViewSplideReady(callback, attempts - 1), 50);
            }
        }

        function normalizeImageUrl(url) {
            try {
                const parsed = new URL(url, window.location.href);
                return parsed.pathname;
            } catch (e) {
                return (url || '').split('?')[0];
            }
        }

        function findFullViewIndexByImageSrc(src) {
            const target = normalizeImageUrl(src);
            const slides = document.querySelectorAll('.listing_fullview-item img');

            for (let i = 0; i < slides.length; i++) {
                if (normalizeImageUrl(slides[i].src) === target) {
                    return i;
                }
            }

            return 0;
        }

        function openFullViewAt(slideIndex, clickedSrc) {
            const index = clickedSrc
                ? findFullViewIndexByImageSrc(clickedSrc)
                : slideIndex;

            whenFullViewSplideReady((splide) => {
                grid.classList.remove('active');
                fullView.classList.add('active');
                document.body.style.overflow = 'hidden';
                document.body.style.height = '100vh';

                const speed = splide.options.speed;
                splide.options.speed = 0;
                splide.go(index);
                splide.options.speed = speed;
            });
        }

        document.querySelectorAll('.listing_grid-item[data-fullview-index]').forEach((item) => {
            const openFromGrid = () => {
                const img = item.querySelector('img');
                const index = parseInt(item.dataset.fullviewIndex, 10);

                if (!Number.isNaN(index) && img) {
                    openFullViewAt(index, img.currentSrc || img.src);
                }
            };

            item.addEventListener('click', openFromGrid);
            item.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    openFromGrid();
                }
            });
        });
    });
</script>
