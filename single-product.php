<?php
get_header();
$current_product_id = get_the_ID();

$product = wc_get_product($current_product_id);

$args = [
    'post_type'      => 'product',
    'posts_per_page' => 3,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
    'post__not_in'   => [$current_product_id],
];

$related_products = new WP_Query($args);
?>

<section class="sproduct_info <?php if (!$related_products->have_posts()){echo 'pb160px';} ?>">
    <div class="container">
        <?php wc_print_notices(); ?>
    </div>
    <div class="container">
        <div class="sproduct_info-grid">
            <div class="sproduct_info-gallery">
                <div class="splide" role="group" id="gallery">
                    <div class="splide__track">
                        <ul class="splide__list">
                            <?php

                            // Imagen destacada
                            if (has_post_thumbnail($current_product_id)) :
                                $thumb_id = get_post_thumbnail_id($current_product_id);
                                $thumb_url = wp_get_attachment_image_url($thumb_id, 'large');
                            ?>
                                <li class="splide__slide">
                                    <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr(get_the_title($current_product_id)); ?>">
                                </li>
                            <?php endif; ?>

                            <?php
                            // Galería
                            $gallery_image_ids = $product->get_gallery_image_ids();

                            if ($gallery_image_ids) :
                                foreach ($gallery_image_ids as $gallery_image_id) :
                                    $gallery_url = wp_get_attachment_image_url($gallery_image_id, 'large');
                            ?>
                                    <li class="splide__slide">
                                        <img src="<?php echo esc_url($gallery_url); ?>" alt="<?php echo esc_attr(get_the_title($current_product_id)); ?>">
                                    </li>
                            <?php
                                endforeach;
                            endif;
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div>
                <div class="w-100 sproduct_info-content">
                    <h1><?php echo esc_html(get_the_title()); ?></h1>
    
                    <div class="product-price">
                        <?php
                        echo wp_kses_post($product->get_price_html());
                        ?>
                    </div>
    
                    <div class="product-content">
                        <?php the_content(); ?>
                    </div>
                </div>

                <div class="add-to-cart-button">
                    <?php woocommerce_template_single_add_to_cart(); ?>
                </div>
            </div>

        </div>
    </div>
</section>

<?php if ($related_products->have_posts()) : ?>
    <section class="other_products">
        <div class="container">
            <h2>You May Also like</h2>
            <div class="other_products-grid">
                <?php while ($related_products->have_posts()) : $related_products->the_post(); ?>
                    <?php get_card_product(get_the_ID()); ?>
                    <?php get_card_product(get_the_ID()); ?>
                    <?php get_card_product(get_the_ID()); ?>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
<?php wp_reset_postdata(); ?>

<?php get_template_part('inc/sections/cta'); ?>

<section class="upcoming pb160">
    <?php get_template_part('inc/sections/upcoming'); ?>
</section>

<?php get_footer(); ?>