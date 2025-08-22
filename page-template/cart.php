<?php
/*
    Template name: cart
*/

get_header();

?>

<section class="cart_page">
    <div class="cart_page-container">
        <div class="cart_page-title">
            <h1>Shopping Cart</h1>
        </div>
        <div class="cart_page-body">
            <?php echo do_shortcode('[woocommerce_cart]'); ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>