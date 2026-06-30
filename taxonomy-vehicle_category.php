<?php

get_header();

$term = get_queried_object();

$banner_title = $term instanceof WP_Term
    ? get_field('banner_title_vehicle_section', 'vehicle_category_' . $term->term_id)
    : '';

$title = !empty($banner_title)
    ? $banner_title
    : ($term instanceof WP_Term ? $term->name : get_the_title());


get_centered_banner('', $title);

// ---------------------------------------------------------------------

$argsVehicle = [
    'post_type'      => 'vehicles',
    'posts_per_page' => 15,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
    'tax_query'      => [
        [
            'taxonomy'         => 'vehicle_category',
            'field'            => 'term_id',
            'terms'            => [(int) $term->term_id],
            'include_children' => false,
        ],
    ],
];

$vehicles = new WP_Query($argsVehicle);

?>

<?php if ($vehicles->have_posts()): ?>
    <section class="vehicles_for_sale pblock160">
        <div class="container_upcoming">
            <div class="upcoming_head title_watermark">
                <div class="watermark">
                    <?php if (!empty(get_field('title_vehicle_section', 'vehicle_category_' . $term->term_id))): ?>
                        <p><?php echo get_field('title_vehicle_section', 'vehicle_category_' . $term->term_id); ?></p>
                    <?php else: ?>
                        <p>Vehicles For Sale</p>
                    <?php endif; ?>
                </div>
                <div class="breadlines">
                    <p>Explore listings</p>
                </div>
                <?php if (!empty(get_field('title_vehicle_section', 'vehicle_category_' . $term->term_id))): ?>
                    <h2><?php echo get_field('title_vehicle_section', 'vehicle_category_' . $term->term_id); ?></h2>
                <?php else: ?>
                    <h2>Vehicles For Sale</h2>
                <?php endif; ?>
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
