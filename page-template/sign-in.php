<?php
/*
    Template name: sign-in
*/

get_header();

?>

<section class="myaccount_page">
    <div class="container">
        <?php echo do_shortcode('[woocommerce_my_account]'); ?>
    </div>
</section>

<?php get_footer(); ?>