<?php

get_header();

$ID = get_the_ID();
$title = get_the_title();

get_centered_banner('', $title);

// ---------------------------------------------------------------------

$argsVehicle = [
    'post_type' => 'vehicles',
    'posts_per_page' => -1,
    'meta_query' => [
        [
            'key' => 'artist_maker_brand',
            'value' => $ID,
            'compare' => '=',
        ],
    ],
];

$vehicles = new WP_Query($argsVehicle);

$argsModels = [
    'post_type' => 'model',
    'posts_per_page' => -1, // todos
    'meta_query' => [
        'relation' => 'AND',
        [
            'key' => 'brand',
            'value' => $ID,
            'compare' => '=',
            'type' => 'NUMERIC',
        ],
        [
            'key' => 'visible_model_in_web',
            'value' => '1',
            'compare' => '=',
        ],
    ],
];

$models = new WP_Query($argsModels);
?>

<?php if ($vehicles->have_posts()): ?>
    <section class="vehicles_for_sale <?php if (!$models->have_posts()) {
        echo 'pblock160';
    } ?>">
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
                            <?php while ($vehicles->have_posts()):
                                $vehicles->the_post(); ?>
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

<?php if ($models->have_posts()): ?>
    <section class="select_model">
        <div class="select_model-container">
            <div class="splide" role="group" id="models">
                <div class="select_model-head">
                    <h3>Select Model</h3>
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
                </div>
                <div class="splide__track">
                    <ul class="splide__list">
                        <?php while ($models->have_posts()):
                            $models->the_post(); 
                            
                            ?>
                            <?php $model_id = get_the_ID(); ?>
                            <li class="splide__slide">
                                <a href="<?php the_permalink(); ?>" data-id="<?php echo $model_id; ?>"
                                    class="modelbox <?php echo $model_id == $ID ? 'local' : 'nolocal'; ?>">
                                    <?php if (has_post_thumbnail()): ?>
                                        <div class="modelbox-thumb">
                                            <?php
											$image_id = get_post_thumbnail_id();
											$image_url = wp_get_attachment_image_url($image_id, 'full');

											if ($image_url) :
											?>
												<img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
											<?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <h3>
                                        <?php the_title(); ?>
                                    </h3>
                                </a>
                            </li>
                        <?php endwhile; ?>
                        <?php
                        $min_items = 4;   // min
                        $max_fill = 10;  // max
                    
                        if ($model_count < $min_items && $model_count < $max_fill) {
                            $fill_count = $min_items - $model_count;
                            for ($i = 0; $i < $fill_count; $i++) {
                                echo '<li class="splide__slide">
                                    <div class="modelbox empty"></div>
                                </li>';
                            }
                        }
                        ?>
                        <?php wp_reset_postdata(); ?>
                    </ul>
                </div>
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
                    while (have_rows('faqs_vehicle')):
                        the_row();
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

<section class="cta">
    <div class="cta_bg">
        <img src="https://handh-s3.s3.us-west-2.amazonaws.com/uploads/2025/09/cta-models.jpg" alt="Banner">
    </div>
    <div class="container">
        <div class="cta_content">
            <h2>Trusted auctioneers of classic and collector motorcars and motorcycles since 1993</h2>
            <div class="cta_links">
                <?php if (!empty(get_field('first_link'))): ?>
                    <a href="<?php echo get_field('first_link')['url'] ?>"
                        alt="<?php echo get_field('first_link')['title'] ?>"><?php echo get_field('first_link')['title'] ?></a>
                <?php else: ?>
                    <a href="<?php echo esc_url(get_permalink(48)); ?>" alt="Contact Us Now">Contact Us Now</a>
                <?php endif; ?>

                <?php if (!empty(get_field('second_link'))): ?>
                    <a href="<?php echo get_field('second_link')['url'] ?>"
                        alt="<?php echo get_field('second_link')['title'] ?>"><?php echo get_field('second_link')['title'] ?></a>
                <?php else: ?>
                    <a href="<?php echo esc_url(get_permalink(50)); ?>" alt="Upcoming Auctions">Upcoming Auctions</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="upcoming pb160">
    <?php get_template_part('inc/sections/upcoming'); ?>
</section>

<?php get_footer(); ?>

<script>
    <?php if (have_rows('faqs_vehicle')): ?>
        $("#my-accordion").accordionjs({
            closeAble: true,
            closeOther: true,
            slideSpeed: 150,
            activeIndex: 100,
        });
    <?php endif; ?>
</script>