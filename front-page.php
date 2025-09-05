<?php
/*
    Template name: home
*/

get_header();

//set fields
$hero_bg = get_field('hero_background_video');
$hero_title = get_field('hero_title');
$hero_subtitle = get_field('hero_subtitle');
$hero_button1 = get_field('hero_button_1');
$hero_button2 = get_field('hero_button_2');

$upcoming_text = get_field('upcoming_text');
$upcoming_button1 = get_field('upcoming_button_1');
$upcoming_button2 = get_field('upcoming_button_2');

$why_video = get_field('whychoose_video');
$why_img = get_field('whychoose_image');
$why_subtitle = get_field('whychoose_subtitle');
$why_title = get_field('whychoose_title');
$why_text = get_field('whychoose_text');
$why_stats = get_field('stats');
$why_p = get_field('whychoose_p');
$why_button1 = get_field('whychoose_button1');
$why_button2 = get_field('whychoose_button2');

$successes_subtitle = get_field('success_subtitle');
$successes_title = get_field('success_title');
$successes_text = get_field('success_text');

$testimonials_subtitle = get_field('testimonials_subtitle');
$testimonials_title = get_field('testimonials_title');
$testimonials_items = get_field('testimonials_items');
?>

<main class="hero relative">
    <div class="video__bg">
        <video autoplay playsinline muted loop>
            <source src="<?php echo $hero_bg; ?>">
        </video>
    </div>
    <div class="container">
        <div class="hero_content">
            <?php if ($hero_subtitle): ?>
                <div class="breadlines">
                    <p><?php echo $hero_subtitle; ?></p>
                </div>
            <?php endif; ?>
            <?php if ($hero_title): ?>
                <?php echo $hero_title; ?>
            <?php endif; ?>
            <div class="hero_actions">
                <?php if ($hero_button1): ?>
                    <a href="<?php echo esc_url($hero_button1['url']); ?>" target="<?php echo esc_attr($hero_button1['target'] ?: '_self'); ?>">
                        <?php echo esc_html($hero_button1['title']); ?>
                    </a>
                <?php endif; ?>

                <?php if ($hero_button2): ?>
                    <a href="<?php echo esc_url($hero_button2['url']); ?>" target="<?php echo esc_attr($hero_button2['target'] ?: '_self'); ?>">
                        <?php echo esc_html($hero_button2['title']); ?>
                    </a>
                <?php endif; ?>
            </div>
            <a class="hero_scroll" href="#upcoming-auctions">
                <p>SCROLL</p>
                <div></div>
                <p>DOWN</p>
            </a>
        </div>
    </div>
</main>

<section class="upcoming" id="upcoming-auctions">
    <?php get_template_part('inc/sections/upcoming'); ?>
</section>

<?php if ($why_video): ?>
    <section class="banner_car">
        <video autoplay loop muted>
            <source src="<?php echo $why_video; ?>">
        </video>
    </section>
<?php endif; ?>

<section class="why_choose_us">
    <div class="container">
        <div class="why_choose_us-info">
            <div class="content">
                <?php if ($why_subtitle): ?>
                    <div class="breadlines">
                        <p><?php echo $why_subtitle; ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($why_title): ?>
                    <h2><?php echo $why_title; ?></h2>
                <?php endif; ?>
                <?php if ($why_text): ?>
                    <p><?php echo $why_text; ?></p>
                <?php endif; ?>
            </div>
            <?php if (have_rows('whychoose_images')): ?>
                <div class="image">
                    <div class="splide" id="whychooseSplide" aria-label="Why Choose Us Images">
                        <div class="splide__track">
                            <ul class="splide__list">
                                <?php while (have_rows('whychoose_images')): the_row();
                                    $image = get_sub_field('whychoose_image');
                                    if ($image): ?>
                                        <li class="splide__slide">
                                            <img src="<?php echo esc_url($image); ?>" alt="">
                                        </li>
                                    <?php endif; ?>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="image_progress">
                        <div class="progress"></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php if (have_rows('stats')): ?>
            <div class="why_choose_us-stats">
                <?php while (have_rows('stats')): the_row(); ?>
                    <?php
                    $number = get_sub_field('stats_number');
                    $text   = get_sub_field('stats_text');
                    ?>
                    <div>
                        <?php if ($number): ?>
                            <h3 class="stat_number" data-target="<?php echo esc_attr($number); ?>"><?php echo esc_html($number); ?></h3>
                        <?php endif; ?>

                        <?php if ($text): ?>
                            <p><?php echo esc_html($text); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="container">
        <div class="upcoming_foot">
            <?php if ($why_p): ?>
                <div>
                    <p><?php echo $why_p; ?></p>
                </div>
            <?php endif; ?>
            <?php if ($why_button1): ?>
                <a href="<?php echo esc_url($why_button1['url']); ?>" class="permalink" target="<?php echo esc_attr($why_button1['target'] ?: '_self'); ?>">
                    <?php echo ($why_button1['title']); ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18" fill="none">
                        <path d="M9.5 4.55556V17M9.5 4.55556C9.5 3.61256 9.12072 2.70819 8.44558 2.0414C7.77045 1.3746 6.85478 1 5.9 1H1.4C1.16131 1 0.932387 1.09365 0.763604 1.26035C0.594821 1.42705 0.5 1.65314 0.5 1.88889V13.4444C0.5 13.6802 0.594821 13.9063 0.763604 14.073C0.932387 14.2397 1.16131 14.3333 1.4 14.3333H6.8C7.51608 14.3333 8.20284 14.6143 8.70919 15.1144C9.21554 15.6145 9.5 16.2928 9.5 17M9.5 4.55556C9.5 3.61256 9.87928 2.70819 10.5544 2.0414C11.2295 1.3746 12.1452 1 13.1 1H17.6C17.8387 1 18.0676 1.09365 18.2364 1.26035C18.4052 1.42705 18.5 1.65314 18.5 1.88889V13.4444C18.5 13.6802 18.4052 13.9063 18.2364 14.073C18.0676 14.2397 17.8387 14.3333 17.6 14.3333H12.2C11.4839 14.3333 10.7972 14.6143 10.2908 15.1144C9.78446 15.6145 9.5 16.2928 9.5 17" stroke="#8C6E47" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </a>
            <?php endif; ?>
        </div>
        <?php if ($why_button2): ?>
            <a href="<?php echo esc_url($why_button2['url']); ?>" class="permalink_border" target="<?php echo esc_attr($why_button2['target'] ?: '_self'); ?>">
                <?php echo ($why_button2['title']); ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                    <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                </svg>
            </a>
        <?php endif; ?>
    </div>
</section>

<section class="animated_text" style="background-image: url('<?php echo esc_url(get_field('animated_text_bg')); ?>');">
    <div class="animated_text-overlay"></div>
    <div class="animated_text-container">
        <?php if (get_field('animated_text')): ?>
            <h2 class="animated_text-item"><?php echo get_field('animated_text'); ?></h2>
        <?php endif; ?>
    </div>
</section>

<?php if (have_rows('slider_top') || have_rows('slider_bottom')): ?>
    <section class="our_successes">
        <div class="container">
            <div class="our_successes-head">
                <?php if ($successes_subtitle): ?>
                    <div class="breadlines">
                        <p><?php echo $successes_subtitle; ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($successes_title): ?>
                    <h2><?php echo $successes_title; ?></h2>
                <?php endif; ?>
                <?php if ($successes_text): ?>
                    <p><?php echo $successes_text; ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="our_successes-body">
            <?php if (have_rows('slider_top')): ?>
                <div class="w-100">
                    <div class="splide" role="group" id="text1">
                        <div class="splide__track">
                            <ul class="splide__list">
                                <?php for ($i = 0; $i < 5; $i++): ?>
                                    <li class="splide__slide">
                                        <h3>2024 Motorcar Highlights</h3>
                                    </li>
                                    <li class="splide__slide">
                                        <h3>•</h3>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="splide" role="group" id="cars1">
                        <div class="splide__track">
                            <ul class="splide__list">
                                <?php while (have_rows('slider_top')): the_row(); ?>
                                    <li class="splide__slide">
                                        <div class="car_card">
                                            <div class="car_card-flex">
                                                <div class="car_card-image">
                                                    <div class="car_card-thumb">
                                                        <img src="<?php echo get_sub_field('image_vehicle_1')['url'] ?>"
                                                            alt="<?php echo get_sub_field('image_vehicle_1')['alt'] ?>"
                                                            width="<?php echo get_sub_field('image_vehicle_1')['width'] ?>"
                                                            height="<?php echo get_sub_field('image_vehicle_1')['height'] ?>"
                                                            loading="lazy">

                                                        <?php if (!empty(get_sub_field('link_vehicle'))): ?>
                                                            <div class="permalink">
                                                                <a href="<?php echo get_sub_field('link_vehicle'); ?>">View</a>
                                                            </div>
                                                        <?php endif; ?>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="42" height="30" viewBox="0 0 42 30" fill="none">
                                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M42.2512 0C24.5511 4.20263 9.49137 15.2238 0 30.1354H42.2512V0Z" fill="#D3C7B6" />
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="car_card-info">
                                                    <div class="car_card-content">
                                                        <?php
                                                        $raw = get_sub_field('date_vehicle');
                                                        if ($raw) {
                                                            echo '<p>' . esc_html(date_i18n('jS M, Y', strtotime($raw))) . '</p>';
                                                        }
                                                        ?>
                                                        <h3><?php echo get_sub_field('vehicle_name'); ?></h3>
                                                    </div>
                                                    <div class="car_card-price">
                                                        <h4>
                                                            <span>Sold for</span>
                                                            £<?php echo get_sub_field('sold_for'); ?>
                                                        </h4>
                                                        <p>(including buyers premium)</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (have_rows('slider_bottom')): ?>
                <div class="w-100">
                    <div class="splide" role="group" id="text2">
                        <div class="splide__track">
                            <ul class="splide__list">
                                <?php for ($i = 0; $i < 5; $i++): ?>
                                    <li class="splide__slide">
                                        <h3>2024 Motorcar Highlights</h3>
                                    </li>
                                    <li class="splide__slide">
                                        <h3>•</h3>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="splide" role="group" id="cars2">
                        <div class="splide__track">
                            <ul class="splide__list">
                                <?php while (have_rows('slider_bottom')): the_row(); ?>
                                    <li class="splide__slide">
                                        <div class="car_card">
                                            <div class="car_card-flex">
                                                <div class="car_card-image">
                                                    <div class="car_card-thumb">
                                                        <img src="<?php echo get_sub_field('image_vehicle')['url'] ?>"
                                                            alt="<?php echo get_sub_field('image_vehicle')['alt'] ?>"
                                                            width="<?php echo get_sub_field('image_vehicle')['width'] ?>"
                                                            height="<?php echo get_sub_field('image_vehicle')['height'] ?>"
                                                            loading="lazy">

                                                        <?php if (!empty(get_sub_field('link_vehicle'))): ?>
                                                            <div class="permalink">
                                                                <a href="<?php echo get_sub_field('link_vehicle'); ?>">View</a>
                                                            </div>
                                                        <?php endif; ?>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="42" height="30" viewBox="0 0 42 30" fill="none">
                                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M42.2512 0C24.5511 4.20263 9.49137 15.2238 0 30.1354H42.2512V0Z" fill="#D3C7B6" />
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="car_card-info">
                                                    <div class="car_card-content">
                                                        <?php
                                                        $raw = get_sub_field('date_vehicle');
                                                        if ($raw) {
                                                            echo '<p>' . esc_html(date_i18n('jS M, Y', strtotime($raw))) . '</p>';
                                                        }
                                                        ?>
                                                        <h3><?php echo get_sub_field('vehicle_name'); ?></h3>
                                                    </div>
                                                    <div class="car_card-price">
                                                        <h4>
                                                            <span>Sold for</span>
                                                            £<?php echo get_sub_field('sold_for'); ?>
                                                        </h4>
                                                        <p>(including buyers premium)</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<section class="discover" data-state="1">
    <div class="container">
        <div class="discover_head title_watermark">
            <div class="watermark">
                <p>Vehicles For Sale</p>
            </div>
            <div class="breadlines">
                <p>Discover</p>
            </div>
            <h2>Vehicles For Sale</h2>
        </div>

        <div class="opportunities-buttons w-100" style="max-width: 100%;padding: 0;">
            <button class="scroll_opportunity active" data-id="1">Classic Motorcars</button>
            <button class="scroll_opportunity" data-id="2">Classic Motorcycles</button>
            <button class="scroll_opportunity" data-id="3">Vintage Scooters</button>
        </div>

        <div class="discover_body">
            <?php
            // Query 1: vehicle_category = 27
            $args_1 = [
                'post_type'           => 'vehicles',
                'posts_per_page'      => 6,
                'post_status'         => 'publish',
                'no_found_rows'       => true,
                'ignore_sticky_posts' => true,
                'tax_query'           => [
                    [
                        'taxonomy' => 'vehicle_category',
                        'field'    => 'term_id',
                        'terms'    => [27],
                    ],
                ],
                'meta_query'          => [
                    [
                        'key'     => '_thumbnail_id',
                        'compare' => 'EXISTS',
                    ],
                ],
                'orderby'             => 'date',
                'order'               => 'DESC',
            ];
            $q1 = new WP_Query($args_1);
            ?>
            <div class="vehicles_grid" id="vehicle_type_1">
                <?php if ($q1->have_posts()): ?>
                    <?php while ($q1->have_posts()): $q1->the_post(); ?>
                        <?php hnh_render_vehicle_card(get_the_ID()); ?>
                    <?php endwhile;
                    wp_reset_postdata(); ?>
                <?php endif; ?>
            </div>

            <?php
            // Query 2: vehicle_category = 22
            $args_2 = [
                'post_type'           => 'vehicles',
                'posts_per_page'      => 6,
                'post_status'         => 'publish',
                'no_found_rows'       => true,
                'ignore_sticky_posts' => true,
                'tax_query'           => [
                    [
                        'taxonomy' => 'vehicle_category',
                        'field'    => 'term_id',
                        'terms'    => [22],
                    ],
                ],
                'meta_query'          => [
                    [
                        'key'     => '_thumbnail_id',
                        'compare' => 'EXISTS',
                    ],
                ],
                'orderby'             => 'date',
                'order'               => 'DESC',
            ];
            $q2 = new WP_Query($args_2);
            ?>
            <div class="vehicles_grid" id="vehicle_type_2">
                <?php if ($q2->have_posts()): ?>
                    <?php while ($q2->have_posts()): $q2->the_post(); ?>
                        <?php hnh_render_vehicle_card(get_the_ID()); ?>
                    <?php endwhile;
                    wp_reset_postdata(); ?>
                <?php endif; ?>
            </div>

            <?php
            // Query 3: vehicle_category = 36
            $args_3 = [
                'post_type'           => 'vehicles',
                'posts_per_page'      => 6,
                'post_status'         => 'publish',
                'no_found_rows'       => true,
                'ignore_sticky_posts' => true,
                'tax_query'           => [
                    [
                        'taxonomy' => 'vehicle_category',
                        'field'    => 'term_id',
                        'terms'    => [36],
                    ],
                ],
                'meta_query'          => [
                    [
                        'key'     => '_thumbnail_id',
                        'compare' => 'EXISTS',
                    ],
                ],
                'orderby'             => 'date',
                'order'               => 'DESC',
            ];
            $q3 = new WP_Query($args_3);
            ?>
            <div class="vehicles_grid" id="vehicle_type_3">
                <?php if ($q3->have_posts()): ?>
                    <?php while ($q3->have_posts()): $q3->the_post(); ?>
                        <?php hnh_render_vehicle_card(get_the_ID()); ?>
                    <?php endwhile;
                    wp_reset_postdata(); ?>
                <?php endif; ?>
            </div>

            <a href="<?php echo esc_url(home_url('vehicles-for-sale')); ?>" class="permalink" alt="View All Vehicles">
                View All Vehicles
                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                    <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                </svg>
            </a>
        </div>
    </div>
</section>

<?php get_template_part('inc/sections/request-register'); ?>

<section class="clients">
    <div class="container_side">
        <div class="clients_head title_watermark">
            <?php if ($testimonials_title): ?>
                <div class="watermark">
                    <p><?php echo $testimonials_title; ?></p>
                </div>
            <?php endif; ?>
            <?php if ($testimonials_subtitle): ?>
                <div class="breadlines">
                    <p><?php echo $testimonials_subtitle; ?></p>
                </div>
            <?php endif; ?>
            <?php if ($testimonials_title): ?>
                <h2><?php echo $testimonials_title; ?></h2>
            <?php endif; ?>
        </div>
        <div class="clients_body">
            <div class="splide" role="group" id="clients">
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
                        <?php
                        $args = array(
                            'post_type'      => 'testimonials',
                            'posts_per_page' => 6,
                            'orderby'        => 'date',
                            'order'          => 'DESC'
                        );
                        $query = new WP_Query($args);

                        if ($query->have_posts()): ?>
                            <?php while ($query->have_posts()): $query->the_post(); ?>
                                <?php
                                $name  = get_field('testimonials_name');
                                $stars = get_field('testimonials_stars'); // número de estrellas
                                $title = get_the_title();
                                $text  = get_the_content();
                                ?>
                                <li class="splide__slide">
                                    <div class="comment">
                                        <div class="comment_info">
                                            <div class="stars">
                                                <?php for ($s = 0; $s < intval($stars); $s++): ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                                        <path d="M8.76224 0.731762C8.83707 0.501435 9.16293 0.501435 9.23776 0.731763L11.189 6.73708C11.2225 6.84009 11.3185 6.90983 11.4268 6.90983H17.7411C17.9833 6.90983 18.084 7.21973 17.8881 7.36208L12.7797 11.0736C12.692 11.1372 12.6554 11.2501 12.6888 11.3531L14.6401 17.3584C14.7149 17.5887 14.4513 17.7803 14.2554 17.6379L9.14695 13.9264C9.05932 13.8628 8.94068 13.8628 8.85305 13.9264L3.74462 17.6379C3.54869 17.7803 3.28507 17.5887 3.35991 17.3584L5.31116 11.3531C5.34463 11.2501 5.30796 11.1372 5.22034 11.0736L0.11191 7.36208C-0.0840186 7.21973 0.0166752 6.90983 0.258856 6.90983H6.57322C6.68153 6.90983 6.77752 6.84009 6.81099 6.73708L8.76224 0.731762Z" fill="#8C6E47" />
                                                    </svg>
                                                <?php endfor; ?>
                                            </div>
                                            <div class="comment_title">
                                                <h3><?php echo esc_html($title); ?></h3>
                                            </div>
                                            <div class="comment_description">
                                                <?php echo wp_kses_post($text); ?>
                                            </div>
                                        </div>
                                        <div class="comment_author">
                                            <div class="comment_photo">
                                                <?php
                                                $parts = explode(' ', trim($name));
                                                if (count($parts) >= 2) {
                                                    echo strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
                                                } else {
                                                    echo strtoupper(substr($parts[0], 0, 1));
                                                }
                                                ?>
                                            </div>
                                            <span><?php echo esc_html($name); ?></span>
                                        </div>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                            <?php wp_reset_postdata(); ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<?php get_template_part('inc/sections/celebrating'); ?>
<?php get_template_part('inc/sections/events'); ?>

<section class="featured_articles">
    <div class="container">
        <div class="featured_articles_head title_watermark">
            <div class="watermark">
                <p>Featured Artic</p>
            </div>
            <div class="breadlines">
                <p>News and Insights</p>
            </div>
            <h2>Featured Articles</h2>
        </div>
        <div class="featured_articles-body">
            <?php
            $args = array(
                'post_type'      => 'post',
                'posts_per_page' => 9,
            );
            $query = new WP_Query($args);

            if ($query->have_posts() && $query->found_posts > 9) :
                $count = 1;
                while ($query->have_posts()) : $query->the_post();
                    if ($count === 1) {
                        $size_class = 'big';
                    } elseif ($count >= 2 && $count <= 5) {
                        $size_class = 'medium';
                    } else {
                        $size_class = 'small';
                    }
            ?>
                    <article class="new <?php echo $size_class; ?>" data-nro="<?php echo $count; ?>">
                        <?php if ($size_class !== 'small' && has_post_thumbnail()) : ?>
                            <div class="new_image">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('medium'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        <a class="new_content" href="<?php the_permalink(); ?>">
                            <span><?php echo get_the_date('d/m/y'); ?></span>
                            <h3>
                                <?php
                                $title = get_the_title();
                                if ($size_class === 'medium') {
                                    echo mb_strimwidth($title, 0, 70, '...');
                                } elseif ($size_class === 'small') {
                                    echo mb_strimwidth($title, 0, 56, '...');
                                } else {
                                    echo $title;
                                }
                                ?>
                            </h3>
                            <?php
                            $short_desc = get_field('post_short_description');

                            if ($short_desc) {
                                if ($size_class === 'big') {
                                    echo '<p>' . mb_strimwidth($short_desc, 0, 225, '...') . '</p>';
                                } elseif ($size_class === 'medium') {
                                    echo '<p>' . mb_strimwidth($short_desc, 0, 100, '...') . '</p>';
                                } elseif ($size_class === 'small') {
                                    echo '<p>' . mb_strimwidth($short_desc, 0, 112, '...') . '</p>';
                                }
                            }
                            ?>
                            <span href="<?php the_permalink(); ?>">Read More >></span>
                        </a>
                    </article>
                <?php
                    $count++;
                endwhile;
                wp_reset_postdata();
            else :
                ?>
                <article class="new big" data-nro="1">
                    <div class="new_image">
                        <img src="<?php echo IMG; ?>/new1.png" alt="Demo">
                    </div>
                    <div class="new_content">
                        <span>22/09/2018</span>
                        <h3>Actor, Sir Michael Caine’s first car, heads to auction...</h3>
                        <a href="#">Read More >></a>
                    </div>
                </article>

                <?php for ($i = 2; $i < 6; $i++): ?>
                    <article class="new medium" data-nro="<?php echo $i; ?>">
                        <div class="new_image">
                            <img src="<?php echo IMG; ?>/new1.png" alt="Demo">
                        </div>
                        <div class="new_content">
                            <span>22/09/2018</span>
                            <h3>Eric Clapton's 2004 Ferrari 612 Scaglietti F1</h3>
                            <a href="#">Read More >></a>
                        </div>
                    </article>
                <?php endfor; ?>

                <?php for ($i = 6; $i < 10; $i++): ?>
                    <article class="new small" data-nro="<?php echo $i; ?>">
                        <div class="new_content">
                            <span>22/09/2018</span>
                            <h3>Eric Clapton's 2004 Ferrari 612 Scaglietti F1</h3>
                            <a href="#">Read More >></a>
                        </div>
                    </article>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
    </div>

    <a href="<?php echo esc_url(home_url('news-and-insights')) ?>" class="permalink" alt="View All Articles">
        View All Articles
        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
            <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
        </svg>
    </a>
    </div>
</section>

<script>
    function animateNumbers() {
        const items = document.querySelectorAll(".stat_number");
        const values = [];

        items.forEach(item => {
            let rawText = item.textContent.trim();
            let number = parseFloat(rawText.replace(/[^0-9.]/g, "")) || 0;
            values.push({
                el: item,
                number,
                rawText
            });
        });

        const max = Math.max(...values.map(v => v.number));
        const duration = 2000;
        const start = performance.now();

        function update(now) {
            const progress = Math.min((now - start) / duration, 1);

            values.forEach(v => {
                let current = Math.floor(v.number * progress);
                let formatted = current.toLocaleString();

                if (/[a-zA-Z%+$]/.test(v.rawText)) {
                    let suffix = v.rawText.replace(/[0-9.,]/g, "");
                    v.el.textContent = formatted + suffix;
                } else {
                    v.el.textContent = formatted;
                }
            });

            if (progress < 1) {
                requestAnimationFrame(update);
            }
        }

        requestAnimationFrame(update);
    }
    animateNumbers();
</script>

<?php get_footer(); ?>