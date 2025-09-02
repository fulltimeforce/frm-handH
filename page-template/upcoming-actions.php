<?php
/*
    Template name: upcoming-auctions
*/

get_header();

get_banner('Homepage / classic auctions / Upcoming Auctions');

$today = current_time('mysql');

$argsAuction = array(
    'post_type'      => 'auction',
    'posts_per_page' => 6,
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
    'meta_key'       => 'auction_date',
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

<section class="auction_list">
    <div class="auction_list-container">
        <?php if ($auctions->have_posts()): ?>
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
                ?>
                <div class="auction">
                    <div class="auction_thumb">
                        <div class="thumb">
                            <?php if (has_post_thumbnail($auction_id)): ?>
                                <img src="<?php echo esc_url(get_the_post_thumbnail_url($auction_id, 'large')); ?>" alt="<?php echo esc_attr(get_the_title($auction_id)); ?>">
                            <?php else: ?>
                                <img src="<?php echo IMG; ?>/auction1.png" alt="Default Auction Image">
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="auction_info">
                        <h2><?php echo $title; ?></h2>
                        <div class="content">
                            <ul>
                                <?php if ($auction_date): ?>
                                    <?php
                                    $date_obj = new DateTime($auction_date);
                                    $formatted_date = $date_obj->format('l, F jS, Y');
                                    ?>
                                    <li>Date: <b><?php echo $formatted_date; ?></b></li>
                                <?php endif; ?>

                                <?php if ($ubication): ?>
                                    <li>Location: <b><?php echo $ubication; ?></b></li>
                                <?php endif; ?>

                                <?php if ($lots): ?>
                                    <li>View Lots: <b><?php echo $lots; ?></b></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <div class="content">
                            <p><b>Classic Motorcars:</b> An auction of classic, collector and performance motorcars to be held in the beautiful surrounds of the Pavilion Gardens, Buxton, Derbyshire.</p>
                        </div>
                        <a alt="Venue Details" href="<?php echo get_permalink($venue_id); ?>">
                            Venue Details
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="12" viewBox="0 0 22 12" fill="none">
                                <path d="M0.25 6H20.25M20.25 6L15.25 1M20.25 6L15.25 11" stroke="#8C6E47" stroke-width="1.20833" />
                            </svg>
                        </a>
                    </div>
                    <div class="auction_actions">
                        <ul>
                            <li><a href="<?php echo $permalink; ?>" alt="View Upcoming Lots">View Upcoming Lots</a></li>
                            <li><a href="">Consign Your Classic</a></li>
                            <li><a href="">Watch Live</a></li>
                            <li><a href="">Learn how to bid</a></li>
                            <li><a href="">View E-Catalogue</a></li>
                        </ul>
                    </div>
                    <div class="auction_keytimes">
                        <div class="auction_keytimes-grid">
                            <div class="w-100">
                                <p>KEY TIMES:</p>
                            </div>
                            <div class="w-100">
                                <p>Viewing:</p>
                                <p>Tuesday, February 11th: From 12:00 PM (Noon) Wednesday, February 12th: From 9:00 AM</p>
                            </div>
                            <div class="w-100">
                                <p>Sale Time:</p>
                                <p>Wednesday, February 12th: From 12:00 PM (Noon)</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>