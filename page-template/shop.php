<?php
/*
    Template name: shop
*/

get_header();

$args = array(
    'post_type'      => 'product',
    'post_status'    => 'publish',
    'posts_per_page' => 12,
    'orderby'        => 'date',
    'order'          => 'DESC',
);

$products = new WP_Query($args);

?>

<div class="shop_page">
    <div class="container">
        <div class="shop_page-title">
            <h1><?php echo get_the_title(); ?></h1>
        </div>
        <?php if ($products->have_posts()): ?>
            <div class="shop_page-grid">
                <?php
                while ($products->have_posts()) {
                    $products->the_post();
                    get_card_product(get_the_ID());
                }
                ?>
            </div>
        <?php else: ?>
            <div class="shop_page-empty">
                <p>No products found to display</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_template_part('inc/sections/cta'); ?>

<section class="upcoming pb160">
    <?php get_template_part('inc/sections/upcoming'); ?>
</section>

<?php get_footer(); ?>