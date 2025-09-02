<?php
$argsAuction = array(
    'post_type'      => 'auction',
    'posts_per_page' => 6,
    'orderby'        => 'date',
    'order'          => 'ASC',
    'meta_query'     => array(
        array(
            'key'     => '_thumbnail_id',
            'compare' => 'EXISTS'
        )
    )
);

$auctions = new WP_Query($argsAuction);
?>

<?php if ($auctions->have_posts()): ?>
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
                        <?php
                        $count = 0;
                        $total = $auctions->post_count;
                        while ($auctions->have_posts()) :

                            $auctions->the_post();
                            global $post;

                            $lots_live        = get_field('lots_live');
                            $auction_date     = get_field('auction_date');
                            $auction_location = get_field('auction_location');
                            $auction_icon     = get_field('auction_icon');
                        ?>
                            <li class="splide__slide">
                                <div class="vehicle <?php echo $count === 0 ? 'active' : ''; ?>">

                                    <?php if ($auction_icon): ?>
                                        <img src="<?php echo esc_url($auction_icon); ?>" alt="<?php the_title(); ?>" class="vehicle-logo">
                                    <?php endif; ?>

                                    <div class="vehicle_bg">
                                        <?php
                                        $thumb_id = get_post_thumbnail_id(get_the_ID());
                                        if ($thumb_id) {
                                            echo wp_get_attachment_image($thumb_id, 'large');
                                        }
                                        ?>
                                    </div>

                                    <div class="w-100 vehicle_bottom">
                                        <div class="w-100 vehicle_content">
                                            <div class="vehicle-info">
                                                <h2>
                                                    <span>
                                                        <?php
                                                        $terms = get_the_terms(get_the_ID(), 'auction_category');
                                                        if ($terms && !is_wp_error($terms)) {
                                                            echo esc_html($terms[0]->name);
                                                        } else {
                                                            echo 'Uncategorized';
                                                        }
                                                        ?>
                                                    </span>
                                                    <?php the_title(); ?>
                                                </h2>
                                                <ul>
                                                    <?php if ($auction_date): ?>
                                                        <li>Date: <?php echo esc_html($auction_date); ?></li>
                                                    <?php endif; ?>
                                                    <?php if ($auction_location): ?>
                                                        <li>Location: <?php echo esc_html($auction_location); ?></li>
                                                    <?php endif; ?>
                                                </ul>
                                                <div class="flex">
                                                    <a href="<?php the_permalink(); ?>">View Now</a>
                                                    <a href="#">Venue Details</a>
                                                    <a href="#">Send me a reminder</a>
                                                </div>
                                                <?php if ($lots_live): ?>
                                                    <div class="lots_live">
                                                        <span class="dot"></span>
                                                        <p>Lots Live (<?php echo esc_html($lots_live); ?>)</p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="vehicle_title">
                                        <span>
                                            <?php
                                            $terms = get_the_terms(get_the_ID(), 'auction_category');
                                            if ($terms && !is_wp_error($terms)) {
                                                echo esc_html($terms[0]->name);
                                            } else {
                                                echo 'Uncategorized';
                                            }
                                            ?>
                                        </span>
                                        <h3><?php the_title(); ?></h3>
                                    </div>
                                </div>

                                <?php if (($count + 1) === $total) : ?>
                                    <div class="vehicle_final">
                                        <h3>Stay tuned for more classic auctions to come</h3>
                                        <img src="<?php echo IMG; ?>/path_car.svg">
                                    </div>
                                <?php endif; ?>
                            </li>
                        <?php
                            $count++;
                        endwhile;
                        wp_reset_postdata();
                        ?>
                    </ul>

                </div>
            </div>
        </div>
    </div>
<?php endif; ?>