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
                            $lots = get_field('lots', $auction_id);

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
                                                <?php if ($lots): ?>
                                                    <div class="lots_live">
                                                        <?php if (intval($lots) == 0): ?>
                                                            <span class="dot"></span>
                                                        <?php else: ?>
                                                            <span class="dot" style="background-color:#08aa2b"></span>
                                                        <?php endif; ?>
                                                        <p>Lots Live (<?php echo $lots; ?>)</p>
                                                    </div>
                                                <?php endif; ?>
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
<?php endif; ?>