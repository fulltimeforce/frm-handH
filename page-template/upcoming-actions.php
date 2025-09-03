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

$upcoming_auctions = new WP_Query($argsAuction);
?>

<section class="auction_list">
    <div class="auction_list-container">
        <?php if ($upcoming_auctions->have_posts()): ?>
            <div class="w-100">
                <?php while ($upcoming_auctions->have_posts()) : ?>
                    <?php
                    $upcoming_auctions->the_post();

                    $auction_id = get_the_ID();
                    $venue_id = (int) get_field('template_venue', $auction_id);
                    hnh_render_auction_card($auction_id, $venue_id);
                    ?>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>