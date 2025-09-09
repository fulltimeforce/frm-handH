<?php

get_header();

$title = get_the_title();

$vehicle_brand = get_field('brand');

if ($vehicle_brand) {
    $term = get_term($vehicle_brand, 'vehicle_brand');
    if (!is_wp_error($term) && $term) {
        $title = $term->name;
    }
}

get_centered_banner('', $title);

// ---------------------------------------------------------------------

$argsVehicle = [
    'post_type'      => 'vehicles',
    'posts_per_page' => 12,
    'tax_query'      => [
        [
            'taxonomy' => 'vehicle_brand',
            'field'    => 'term_id',
            'terms'    => $vehicle_brand,
        ],
    ],
];

$vehicles = new WP_Query($argsVehicle);

?>

<?php if ($vehicles->have_posts()): ?>
    <section class="vehicles_for_sale">
        <div class="container_upcoming">
            <div class="upcoming_head title_watermark">
                <div class="watermark">
                    <p>Vehicles For Sale</p>
                </div>
                <div class="breadlines">
                    <p>Explore listings</p>
                </div>
                <h2>Vehicles For Sale</h2>
            </div>
            <div class="upcoming_body">
                <div class="splide" role="group" id="vehiclesForSale">
                    <div class="splide__arrows">
                        <button class="splide__arrow splide__arrow--prev">
                            <svg xmlns="http://www.w3.org/2000/svg" width="50" height="26" viewBox="0 0 50 26" fill="none">
                                <path d="M0 13H48M48 13L36 1M48 13L36 25" stroke="#8C6E47" stroke-width="2" />
                            </svg>
                        </button>
                        <button class="splide__arrow splide__arrow--next">
                            <svg xmlns="http://www.w3.org/2000/svg" width="50" height="26" viewBox="0 0 50 26" fill="none">
                                <path d="M0 13H48M48 13L36 1M48 13L36 25" stroke="#8C6E47" stroke-width="2" />
                            </svg>
                        </button>
                    </div>
                    <div class="splide__track">
                        <ul class="splide__list">
                            <?php while ($vehicles->have_posts()) : $vehicles->the_post(); ?>
                                <li class="splide__slide">
                                    <?php hnh_render_vehicle_card(get_the_ID(), [], 2); ?>
                                </li>
                            <?php endwhile;
                            wp_reset_postdata(); ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php
$argsModels = [
    'post_type'      => 'model',
    'posts_per_page' => -1, // todos
    'meta_query'     => [
        [
            'key'     => 'brand',
            'value'   => $vehicle_brand,
            'compare' => '=',
            'type'    => 'NUMERIC',
        ],
    ],
];

$models = new WP_Query($argsModels);
?>

<?php if ($models->have_posts()) : ?>
    <section class="select_model">
        <div class="select_model-container">
            <div class="splide" role="group" id="models">
                <div class="splide__arrows">
                    <button class="splide__arrow splide__arrow--prev">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="26" viewBox="0 0 50 26" fill="none">
                            <path d="M0 13H48M48 13L36 1M48 13L36 25" stroke="#8C6E47" stroke-width="2" />
                        </svg>
                    </button>
                    <button class="splide__arrow splide__arrow--next">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="26" viewBox="0 0 50 26" fill="none">
                            <path d="M0 13H48M48 13L36 1M48 13L36 25" stroke="#8C6E47" stroke-width="2" />
                        </svg>
                    </button>
                </div>
                <div class="splide__track">
                    <ul class="splide__list">
                        <?php while ($models->have_posts()) : $models->the_post(); ?>
                            <li class="splide__slide">
                                <a href="<?php the_permalink(); ?>" class="modelbox">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <div class="modelbox-thumb">
                                            <?php the_post_thumbnail('full'); ?>
                                        </div>
                                    <?php endif; ?>
                                    <h3>
                                        <?php the_title(); ?>
                                    </h3>
                                </a>
                            </li>
                        <?php endwhile;
                        wp_reset_postdata(); ?>
                    </ul>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if (has_post_thumbnail()): ?>
    <section class="vehicle_banner">
        <div class="vehicle_banner-container">
            <div class="vehicle_banner-box">
                <img src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'full')); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
                <h2>
                    <?php echo esc_html(get_the_title()); ?>
                </h2>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if (have_rows('frames')): ?>
    <section class="paintings">
        <div class="paintings_container">
            <div class="paintings_row">
                <?php while (have_rows('frames')): the_row(); ?>
                    <div class="painting_box">
                        <?php if (!empty(get_sub_field('image_frame'))): ?>
                            <div class="painting_box-image">
                                <img
                                    src="<?php echo get_sub_field('image_frame')['url'] ?>"
                                    title="<?php echo get_sub_field('image_frame')['title'] ?>"
                                    alt="<?php echo get_sub_field('image_frame')['alt'] ?>"
                                    width="<?php echo get_sub_field('image_frame')['width'] ?>"
                                    height="<?php echo get_sub_field('image_frame')['height'] ?>"
                                    loading="lazy">
                            </div>
                        <?php endif; ?>
                        <div class="painting_box-title">
                            <h2>
                                <?php if (!empty(get_sub_field('icon_frame'))): ?>
                                    <img
                                        src="<?php echo get_sub_field('icon_frame')['url'] ?>"
                                        title="<?php echo get_sub_field('icon_frame')['title'] ?>"
                                        alt="<?php echo get_sub_field('icon_frame')['alt'] ?>"
                                        width="<?php echo get_sub_field('icon_frame')['width'] ?>"
                                        height="<?php echo get_sub_field('icon_frame')['height'] ?>"
                                        loading="lazy">
                                <?php endif; ?>
                                <?php echo get_sub_field('title_frame'); ?>
                            </h2>
                        </div>
                        <div class="painting_box-content">
                            <p><?php echo get_sub_field('content_frame'); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if (have_rows('frames_2')): ?>
    <section class="paintings">
        <div class="paintings_container">
            <div class="paintings_row">
                <?php while (have_rows('frames_2')): the_row(); ?>
                    <div class="painting_box">
                        <?php if (!empty(get_sub_field('image_frame2'))): ?>
                            <div class="painting_box-image">
                                <img
                                    src="<?php echo get_sub_field('image_frame2')['url'] ?>"
                                    title="<?php echo get_sub_field('image_frame2')['title'] ?>"
                                    alt="<?php echo get_sub_field('image_frame2')['alt'] ?>"
                                    width="<?php echo get_sub_field('image_frame2')['width'] ?>"
                                    height="<?php echo get_sub_field('image_frame2')['height'] ?>"
                                    loading="lazy">
                            </div>
                        <?php endif; ?>
                        <div class="painting_box-title">
                            <h2>
                                <?php if (!empty(get_sub_field('icon_frame2'))): ?>
                                    <img
                                        src="<?php echo get_sub_field('icon_frame2')['url'] ?>"
                                        title="<?php echo get_sub_field('icon_frame2')['title'] ?>"
                                        alt="<?php echo get_sub_field('icon_frame2')['alt'] ?>"
                                        width="<?php echo get_sub_field('icon_frame2')['width'] ?>"
                                        height="<?php echo get_sub_field('icon_frame2')['height'] ?>"
                                        loading="lazy">
                                <?php endif; ?>
                                <?php echo get_sub_field('title_frame2'); ?>
                            </h2>
                        </div>
                        <div class="painting_box-content">
                            <p><?php echo get_sub_field('content_frame2'); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if (have_rows('faqs_vehicle')): ?>
    <section class="faq faq_in_vehicle">
        <div class="faq_container">
            <div class="faq_subtitle">
                <h2>
                    <?php
                    $title_faq = get_field('title_faq_vehicle');
                    echo $title_faq ? esc_html($title_faq) : get_the_title();
                    ?>
                </h2>
            </div>
            <div class="faq_list">
                <ul id="my-accordion" class="accordionjs">
                    <?php
                    $count = 1; // Iniciar contador
                    while (have_rows('faqs_vehicle')): the_row();
                    ?>
                        <li>
                            <div>
                                <h3>
                                    <?php echo str_pad($count, 2, '0', STR_PAD_LEFT) . '. ' . get_sub_field('question_vehicle');
                                    $count++; ?>
                                </h3>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                    <path d="M0 8.99943L18 8.99943M8.99969 0L8.99969 18" stroke="#8C6E47" stroke-width="2" />
                                </svg>
                            </div>
                            <div>
                                <div class="description">
                                    <?php
                                    if (!empty(get_sub_field('answer_vehicle'))) {
                                        echo get_sub_field('answer_vehicle');
                                    }
                                    ?>
                                </div>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if (!empty(get_field('image_conclusion')) || !empty(get_field('content_conclusion'))): ?>
    <section class="conclusion">
        <div class="conclusion_container">
            <div class="conclusion_box">
                <?php if (!empty(get_field('image_conclusion'))): ?>
                    <img
                        src="<?php echo get_field('image_conclusion')['url'] ?>"
                        title="<?php echo get_field('image_conclusion')['title'] ?>"
                        alt="<?php echo get_field('image_conclusion')['alt'] ?>"
                        width="<?php echo get_field('image_conclusion')['width'] ?>"
                        height="<?php echo get_field('image_conclusion')['height'] ?>"
                        loading="lazy">
                <?php endif; ?>
                <h2>Conclusion</h2>

                <?php if (!empty(get_field('content_conclusion'))): ?>
                    <div class="w-100">
                        <p><?php echo get_field('content_conclusion'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php get_template_part('inc/sections/cta-single-product'); ?>

<section class="upcoming pb160">
    <?php get_template_part('inc/sections/upcoming'); ?>
</section>

<?php get_footer(); ?>

<?php if (have_rows('faqs_vehicle')): ?>
<script>
    $("#my-accordion").accordionjs({
        closeAble: true,
        closeOther: true,
        slideSpeed: 150,
        activeIndex: 100,
    });
</script>
<?php endif; ?>