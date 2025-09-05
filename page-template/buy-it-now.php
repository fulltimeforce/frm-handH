<?php
/*
    Template name: buy-it-now
*/

get_header();

get_banner(
    'Homepage / classic auctions / Auction Results',
    get_the_post_thumbnail_url(get_the_ID(), 'full'),
    'Auction Results'
);

?>

<section class="auction_result-tab">
    <div class="container">
        <div>
            <a href="<?php echo esc_url(home_url('auction-results')); ?>">PAST AUCTIONS</a>
            <a class="active">Unsold Vehicles</a>
        </div>
    </div>
</section>

<section class="auction_vehicles">
    <div class="auction_vehicles-container">
        <div class="auction_vehicles-head">
            <h2>Buy It Now - The vehicles listed below are available post auction for purchase.</h2>
            <div class="content">
                <p>Vehicles unsold in the most recent auction are promoted here for 14 days post auction, you may make an offer using the provided form below or contact by call us on 01925 210035 or send an email to <a href="mailto:sales@HandH.co.uk">sales@HandH.co.uk</a>.</p>
            </div>
        </div>
        <?php echo hnh_render_buy_it_now_block(); ?>
    </div>
    <div class="advertise_container auction_result-form">
        <h2>Contact Sales Department</h2>
        <div class="advertise_form">
            <?php echo do_shortcode('[gravityform id="2" title="true" ajax="true"]'); ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>