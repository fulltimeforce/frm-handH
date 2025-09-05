<?php
/*
    Template name: vehicles-for-sale
*/

get_header();

get_banner('Homepage / Private Sales / Vehicles For Sale', '', 'Vehicles For Sale');

?>

<section class="auction_vehicles">
    <div class="auction_vehicles-container">
        <div class="auction_vehicles-head">
            <h2>The vehicles listed below are available for Private Sale and can be viewed by appointment only.</h2>
            <div class="content">
                <p>To submit your vehicle for Private Sale or make an offer please contact our <a href="<?php echo esc_url(home_url('our-showroom')); ?>" alt="Private Sales Showroom">Private Sales Showroom</a></p>
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