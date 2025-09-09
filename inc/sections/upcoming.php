<?php
$today = current_time('mysql');

$argsAuction = array(
    'post_type'      => 'auction',
    'posts_per_page' => 6,
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
    'meta_key'       => 'auction_date', // muy importante para ordenar por este campo
    'meta_type'      => 'DATETIME',
    'meta_query'     => array(
        array(
            'key'     => 'auction_date',
            'value'   => $today,
            'compare' => '>',
            'type'    => 'DATETIME'
        )
    )
);

$auctions = new WP_Query($argsAuction);
?>

<?php if ($auctions->have_posts()): ?>
    <?php
    $count = 0;
    $total = $auctions->post_count;
    ?>
    <div class="container_upcoming">
        <div class="upcoming_head title_watermark">
            <div class="watermark">
                <p>Upcoming Auctions</p>
            </div>
            <div class="breadlines">
                <p>Explore</p>
            </div>
            <h2>Upcoming Auctions</h2>
        </div>
        <div class="upcoming_body">
            <div class="splide" role="group" id="upcoming">
                <div class="splide__arrows">
                    <button class="splide__arrow splide__arrow--prev">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="26" viewBox="0 0 50 26" fill="none">
                            <path d="M0 13H48M48 13L36 1M48 13L36 25" stroke="#8C6E47" stroke-width="2" />
                        </svg>
                    </button>
                    <button class="splide__arrow splide__arrow--next">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="26" viewBox="0 0 50 26" fill="none">
                            <path d="M0 13H48M48 13L36 1M48 13L36 25" stroke="#8C6E47" stroke-width="2" />
                        </svg>
                    </button>
                </div>
                <div class="splide__track">
                    <ul class="splide__list">
                        <?php while ($auctions->have_posts()) : ?>
                            <?php
                            $auctions->the_post();

                            $auction_id = get_the_ID();
                            $venue_id = get_field('template_venue', $auction_id);

                            $auction_date = get_field('auction_date', $auction_id);
                            $title = get_the_title($auction_id);
                            $permalink = get_permalink($auction_id);
                            // $lots = get_field('lots', $auction_id);

                            $ubication = get_field('slider_subtitle', $venue_id);

                            $venue_name = get_the_title($venue_id);
                            ?>
                            <li class="splide__slide">
                                <div class="vehicle <?php echo $count === 0 ? 'active' : ''; ?>">

                                    <?php if ($auction_icon): ?>
                                        <img src="<?php echo esc_url($auction_icon); ?>" alt="<?php echo $venue_name; ?>" class="vehicle-logo">
                                    <?php endif; ?>

                                    <div class="vehicle_bg">
                                        <?php
                                        if ($venue_id) {
                                            $thumb_id = get_post_thumbnail_id($venue_id);
                                            if ($thumb_id) {
                                                echo wp_get_attachment_image($thumb_id, 'large');
                                            }
                                        } else {
                                            $thumb_id = get_post_thumbnail_id($auction_id);
                                            if ($thumb_id) {
                                                echo wp_get_attachment_image($thumb_id, 'large');
                                            }
                                        }
                                        ?>
                                    </div>

                                    <div class="w-100 vehicle_bottom">
                                        <div class="w-100 vehicle_content">
                                            <div class="vehicle-info">
                                                <h2>
                                                    <?php if (!empty(get_field('sale_type'))): ?>
                                                        <span>
                                                            Classic <?php echo get_field('sale_type'); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php echo $venue_name; ?>
                                                </h2>
                                                <ul>
                                                    <?php if ($auction_date): ?>
                                                        <?php
                                                        $timestamp = strtotime($auction_date);
                                                        $formatted_date = date_i18n('jS M, Y - g:i a', $timestamp);
                                                        ?>
                                                        <li>Date: <?php echo $formatted_date; ?></li>
                                                    <?php endif; ?>

                                                    <?php if ($ubication): ?>
                                                        <li>Location: <?php echo $ubication; ?></li>
                                                    <?php endif; ?>
                                                </ul>
                                                <div class="flex">
                                                    <a href="<?php the_permalink(); ?>">View Auction</a>
                                                </div>
                                                <?php
                                                $auctionSaleNumber = get_field('sale_number', $auction_id);

                                                $total_vehicles = 0;
                                                if ($auctionSaleNumber !== '' && $auctionSaleNumber !== null) {
                                                    $q = new WP_Query([
                                                        'post_type'               => 'vehicles',
                                                        'post_status'             => 'publish',
                                                        'meta_query'              => [[
                                                            'key'     => 'auction_number_latest',
                                                            'value'   => (int) $auctionSaleNumber,
                                                            'compare' => '=',
                                                            'type'    => 'NUMERIC',
                                                        ]],
                                                        'fields'                   => 'ids',
                                                        'posts_per_page'          => 1,
                                                    ]);
                                                    $total_vehicles = (int) $q->found_posts;
                                                    wp_reset_postdata();
                                                }
                                                ?>
                                                <div class="lots_live">
                                                    <?php if (intval($total_vehicles) == 0): ?>
                                                        <span class="dot"></span>
                                                    <?php else: ?>
                                                        <span class="dot" style="background-color:#08aa2b"></span>
                                                    <?php endif; ?>
                                                    <p>Lots Live (<?php echo $total_vehicles; ?>)</p>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="vehicle_title">
                                        <?php if (!empty(get_field('sale_type'))): ?>
                                            <span>
                                                Classic <?php echo get_field('sale_type'); ?>
                                            </span>
                                        <?php endif; ?>
                                        <h3><?php echo $venue_name; ?></h3>
                                    </div>
                                </div>

                                <?php if (($count + 1) === $total) : ?>
                                    <div class="vehicle_final">
                                        <h3>Stay tuned for more classic auctions to come</h3>
                                        <img src="<?php echo IMG; ?>/path_car.svg">
                                    </div>
                                <?php endif; ?>
                            </li>
                            <?php $count++; ?>
                        <?php endwhile;
                        wp_reset_postdata(); ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php if (is_front_page()): ?>
        <?php
        $up_p = get_field('upcoming_text');
        $up_button1 = get_field('upcoming_button_1');
        $up_button2 = get_field('upcoming_button_2');
        ?>
        <div class="container">
            <div class="upcoming_foot">
                <?php if ($up_p): ?>
                    <div>
                        <p><?php echo $up_p; ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($up_button1): ?>
                    <a href="<?php echo esc_url($up_button1['url']); ?>" class="permalink" target="<?php echo esc_attr($up_button1['target'] ?: '_self'); ?>">
                        <?php echo ($up_button1['title']); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18" fill="none">
                            <path d="M9.5 4.55556V17M9.5 4.55556C9.5 3.61256 9.12072 2.70819 8.44558 2.0414C7.77045 1.3746 6.85478 1 5.9 1H1.4C1.16131 1 0.932387 1.09365 0.763604 1.26035C0.594821 1.42705 0.5 1.65314 0.5 1.88889V13.4444C0.5 13.6802 0.594821 13.9063 0.763604 14.073C0.932387 14.2397 1.16131 14.3333 1.4 14.3333H6.8C7.51608 14.3333 8.20284 14.6143 8.70919 15.1144C9.21554 15.6145 9.5 16.2928 9.5 17M9.5 4.55556C9.5 3.61256 9.87928 2.70819 10.5544 2.0414C11.2295 1.3746 12.1452 1 13.1 1H17.6C17.8387 1 18.0676 1.09365 18.2364 1.26035C18.4052 1.42705 18.5 1.65314 18.5 1.88889V13.4444C18.5 13.6802 18.4052 13.9063 18.2364 14.073C18.0676 14.2397 17.8387 14.3333 17.6 14.3333H12.2C11.4839 14.3333 10.7972 14.6143 10.2908 15.1144C9.78446 15.6145 9.5 16.2928 9.5 17" stroke="#8C6E47" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </a>
                <?php endif; ?>
            </div>
            <?php if ($up_button2): ?>
                <a href="<?php echo esc_url($up_button2['url']); ?>" class="permalink_border" target="<?php echo esc_attr($up_button2['target'] ?: '_self'); ?>">
                    <?php echo ($up_button2['title']); ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                        <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                    </svg>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

<?php endif; ?>