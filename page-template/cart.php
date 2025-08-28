<?php
/*
    Template name: cart
*/

get_header();

?>

<section class="cart_page">
    <div class="container">
        <div class="cart_page-title">
            <h1>Shopping Cart</h1>
        </div>
    </div>
    <?php echo do_shortcode('[woocommerce_cart]'); ?>
</section>

<?php get_footer(); ?>