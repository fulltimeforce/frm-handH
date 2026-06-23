<?php
/*
    Template name: upcoming-auctions
*/
get_header();

$banner_breadcrumb = get_field("ua_banner_breadcrumb") ?: 'Homepage / classic auctions / Upcoming Auctions';

get_banner($banner_breadcrumb);
$upcoming_auctions = hnh_get_upcoming_auctions_query();
?>
<section class="auction_list">
    <div class="auction_list-container">
        <?php if ($upcoming_auctions->have_posts()): ?>
            <div class="w-100">
                <?php while ($upcoming_auctions->have_posts()): ?>
                    <?php
                    $upcoming_auctions->the_post();
                    $auction_id = get_the_ID();
                    $venue_id = (int) get_field('template_venue', $auction_id);
                    hnh_render_auction_card(
                        $auction_id,
                        $venue_id
                    ); ?>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php get_footer(); ?>