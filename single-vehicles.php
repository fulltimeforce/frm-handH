<?php

get_header();

$current_lot = get_field('lot_number_latest');
$auction = get_field('auction_latest');
$auction_number = get_field('auction_number_latest');
$estimate_high = get_field('estimate_high');
$estimate_low = get_field('estimate_low');
$short_text = get_field('title_sub');

$vehicle_video = '';

?>

<section class="listing_head">
    <div class="container">
        <div class="listing_head-col">
            <div>
                <a href="/vehicles-for-sale" class="listing_btn-white p14"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M2.60156 8H2.60756M2.60156 14H2.60756M2.60156 2H2.60756M5.60156 8H13.4016M5.60156 14H13.4016M5.60156 2H13.4016" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Return to Auction List</a>
                <p class="p20">
                    <?php
                    $date_raw = get_field('auction_date_latest'); // "2025-12-18 18:56"

                    if ($date_raw) {
                        $dt = DateTime::createFromFormat('Y-m-d H:i', $date_raw);
                        if ($dt) {
                            echo $dt->format('jS M, Y g:i');
                            // Resultado: "18th Dec, 2025 6:56"
                        } else {
                            echo esc_html($date_raw); // fallback
                        }
                    }
                    ?>
                </p>
            </div>
            <?php if ($auction): ?>
                <p class="listing_head-title"><?php echo $auction; ?></p>
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
                <?php
                $prev_post = get_previous_post();
                $next_post = get_next_post();
                ?>

                <?php if ($prev_post) : ?>
                    <a href="<?php echo get_permalink($prev_post->ID); ?>" class="listing_btn-brown p14">
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
                        Previous Lot
                    </a>
                <?php endif; ?>


                <?php if ($next_post) : ?>
                    <a href="<?php echo get_permalink($next_post->ID); ?>" class="listing_btn-brown p14">
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

                <?php if (NOT_APPEAR): ?>
                    <a class="listing_btn-white p14">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M7.68266 1.97392C7.71187 1.9149 7.757 1.86522 7.81295 1.83048C7.8689 1.79575 7.93344 1.77734 7.9993 1.77734C8.06515 1.77734 8.12969 1.79575 8.18564 1.83048C8.24159 1.86522 8.28672 1.9149 8.31593 1.97392L9.85579 5.09297C9.95724 5.29827 10.107 5.47588 10.2922 5.61056C10.4774 5.74525 10.6925 5.83298 10.919 5.86624L14.3627 6.37019C14.428 6.37965 14.4893 6.40717 14.5397 6.44965C14.5901 6.49213 14.6277 6.54787 14.648 6.61057C14.6684 6.67327 14.6709 6.74043 14.6551 6.80444C14.6393 6.86846 14.6059 6.92678 14.5587 6.9728L12.0683 9.39792C11.904 9.55797 11.7811 9.75554 11.7102 9.97362C11.6392 10.1917 11.6223 10.4238 11.661 10.6498L12.2489 14.0762C12.2604 14.1414 12.2534 14.2085 12.2286 14.2699C12.2038 14.3313 12.1622 14.3845 12.1086 14.4235C12.055 14.4624 11.9916 14.4855 11.9255 14.4901C11.8594 14.4947 11.7934 14.4806 11.735 14.4495L8.65657 12.8309C8.45373 12.7244 8.22806 12.6688 7.99896 12.6688C7.76986 12.6688 7.54419 12.7244 7.34135 12.8309L4.26363 14.4495C4.20519 14.4804 4.13924 14.4943 4.07328 14.4896C4.00733 14.4849 3.94401 14.4618 3.89053 14.4229C3.83705 14.3841 3.79556 14.3309 3.77078 14.2696C3.746 14.2083 3.73892 14.1413 3.75035 14.0762L4.33763 10.6505C4.37642 10.4243 4.35961 10.1921 4.28866 9.9739C4.2177 9.75568 4.09472 9.55801 3.93033 9.39792L1.43989 6.97347C1.39229 6.9275 1.35856 6.86908 1.34254 6.80487C1.32652 6.74066 1.32886 6.67324 1.34928 6.6103C1.36971 6.54735 1.4074 6.49141 1.45807 6.44884C1.50874 6.40627 1.57035 6.37879 1.63587 6.36953L5.07889 5.86624C5.30571 5.83324 5.52111 5.74562 5.70656 5.61092C5.89201 5.47622 6.04194 5.29847 6.14346 5.09297L7.68266 1.97392Z" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Watchlist
                    </a>
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

<?php
$gallery = get_field('gallery_vehicle');
if ($gallery && is_array($gallery)): ?>
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
                    <img class="wh-100 thumbnail-post" src="<?php echo esc_url($first['url']); ?>" alt="<?php echo esc_attr($first['alt'] ?: 'vehicle'); ?>">
                    <div id="openFullView" class="listing_images-counter p18" data-total="<?php echo $total; ?>">1/<?php echo $total; ?></div>
                    <div id="openGrid" class="listing_images-grid">
                        <img src="<?php echo IMG; ?>/grid-icon.svg" alt="icon">
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
            $status = get_field('status');

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
                <?php endif; ?>
            <?php endif; ?>

            <?php
            $estimate_html = '';
            if ($estimate_low && $estimate_high) {
                $low  = (float) preg_replace('/[^\d.\-]/', '', (string) $estimate_low);
                $high = (float) preg_replace('/[^\d.\-]/', '', (string) $estimate_high);
                $estimate_html = '£' . esc_html(number_format_i18n($low, 0)) . ' - £' . esc_html(number_format_i18n($high, 0));
            }
            if ($estimate_html):
            ?>
                <div>
                    <p class="p17">Estimate</p>
                    <p class="gold-text"><?php echo $estimate_html; ?></p>
                </div>
            <?php endif; ?>
        </div>

        <?php
        // 1) Toma el nombre desde los ACF de texto
        $member_name = trim((string)(get_field('assigned_to') ?: get_field('contact_rep') ?: ''));

        if ($member_name !== ''):

            // 2) Helper para localizar el CPT 'team' por nombre
            if (!function_exists('hnh_find_team_by_name')) {
                function hnh_find_team_by_name($name)
                {
                    $name = trim((string)$name);
                    if ($name === '') return null;

                    // a) Coincidencia exacta por título
                    $p = get_page_by_title($name, OBJECT, 'team');
                    if ($p instanceof WP_Post && $p->post_status === 'publish') return $p;

                    // b) Por slug (por si el título exacto no coincide pero el slug sí)
                    $slug = sanitize_title($name);
                    $p = get_page_by_path($slug, OBJECT, 'team');
                    if ($p instanceof WP_Post && $p->post_status === 'publish') return $p;

                    // c) Búsqueda de respaldo
                    $q = new WP_Query([
                        'post_type'           => 'team',
                        'post_status'         => 'publish',
                        'posts_per_page'      => 1,
                        'no_found_rows'       => true,
                        'ignore_sticky_posts' => true,
                        's'                   => $name,
                        'orderby'             => 'title',
                        'order'               => 'ASC',
                        'fields'              => 'ids',
                    ]);
                    if (!empty($q->posts)) return get_post($q->posts[0]);

                    return null;
                }
            }

            // 3) Busca el miembro y pinta sus datos
            $team_post = hnh_find_team_by_name($member_name);

            if ($team_post) :
                $member_id = (int) $team_post->ID;

                $name     = get_the_title($member_id);
                $email    = (string) get_field('team_email', $member_id);
                $phone    = (string) (get_field('team_phone', $member_id) ?: get_field('phone', $member_id));
                $position = (string) get_field('job_position', $member_id);

                // Imagen destacada con fallback
                $img_url  = get_the_post_thumbnail_url($member_id, 'medium');
                if (!$img_url && defined('IMG')) $img_url = IMG . '/face2.png';

                if ($name && ($email || $phone)) : ?>
                    <div class="listing_info-contact">
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
                            <a href="<?php echo esc_url(get_permalink($member_id)); ?>" class="listing_btn-white p14">
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
        <?php else: ?>
            <div class="empty-listing_info-contact"></div>
        <?php endif; ?>

        <div class="listing_info-details">

            <div class="listing_info-details-tabs">
                <div class="faq_list">

                    <ul id="listingTab" class="accordionjs">
                        <li>
                            <div>
                                <h3>Lot Details</h3>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                    <path d="M0 8.99943L18 8.99943M8.99969 0L8.99969 18" stroke="#8C6E47" stroke-width="2" />
                                </svg>
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

        <?php
        $vehicle_notes = get_field('footnote');
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
    });
</script>