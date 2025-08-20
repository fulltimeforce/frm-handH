<?php
/*
    Template name: vehicles-wanted
*/

get_header();

get_banner('Homepage / Private Sales / Vehicles Wanted', '', 'Vehicles Wanted');

?>

<section class="advertise">
    <div class="advertise_container">
        <h2>To advertise your desired vehicle or to discuss the vehicles listed, contact our Private Sales Department today.</h2>
        <div class="advertise_form">
            <?php echo do_shortcode('[gravityform id="2" title="true" ajax="true"]'); ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>