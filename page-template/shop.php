<?php
/*
    Template name: shop
*/

get_header();

?>

<div class="shop_page">
    <div class="container">
        <div class="shop_page-title">
            <h1><?php echo get_the_title(); ?></h1>
        </div>
        <div class="shop_page-grid">

        </div>
    </div>
</div>

<section class="upcoming">
    <?php get_template_part('inc/sections/upcoming'); ?>
</section>

<?php get_footer(); ?>