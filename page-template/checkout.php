<?php
/*
    Template name: checkout
*/

get_header();

?>

<section class="checkout_page">
    <div class="container">
        <div class="checkout_page-title">
            <h1>Checkout</h1>
        </div>
        <div class="checkout_page-body">
            <?php echo do_shortcode('[woocommerce_checkout]'); ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>