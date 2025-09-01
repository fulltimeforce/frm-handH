<?php
/*
    Template name: about
*/

get_header();

get_banner('Homepage / About / About H&H Classics', get_the_post_thumbnail_url(get_the_ID(), 'full'), 'About H&H Classics');

$heritage_title = get_field('heritage_title');
$heritage_htitle = get_field('heritage_high_title');
$heritage_text = get_field('heritage_text');
$heritage_btn1 = get_field('heritage_button1');
$heritage_btn2 = get_field('heritage_button2');
$timeline_title = get_field('title_timeline');
?>

<section class="heritage">
    <div class="heritage_container">
        <div class="tabs">
            <a href="#our-heritage" class="active">Our Heritage</a>
            <a href="#timelines">Timeline</a>
            <a href="#specialists">Our Specialists</a>
            <a href="#private-sales">Private Sales</a>
            <a href="#upcoming-auctions">Upcoming Auctions</a>
        </div>
        <div class="heritage_information" id="our-heritage">
            <div class="heritage_images">
                <img class="heritage_images-main" src="<?php echo IMG; ?>/about/4.png">
                <div class="heritage_images-slider">
                    <div id="heritage" class="splide">
                        <div class="splide__track">
                            <?php if (have_rows('heritage_images')): ?>
                                <ul class="splide__list">
                                    <?php while (have_rows('heritage_images')): the_row();
                                        $image = get_sub_field('heritage_image');
                                    ?>
                                        <?php if ($image): ?>
                                            <li class="splide__slide">
                                                <img src="<?php echo esc_url($image); ?>" alt="Heritage image">
                                            </li>
                                        <?php endif; ?>
                                    <?php endwhile; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="image_progress">
                        <div class="progress"></div>
                    </div>
                </div>
            </div>
            <div class="heritage_content">
                <div class="breadlines">
                    <p>Classic Vehicle Auctions</p>
                </div>
                <?php if ($heritage_title) : ?>
                    <h2><span><?php echo $heritage_htitle; ?></span><?php echo $heritage_title; ?></h2>
                <?php endif; ?>
                <?php if ($heritage_text) : ?>
                    <div class="content">
                        <?php echo $heritage_text; ?>
                    </div>
                <?php endif; ?>
                <div class="actions">
                    <a href="<?php echo esc_url($heritage_btn1['url']); ?>" class="permalink_border" target="<?php echo esc_attr($heritage_btn1['target'] ?: '_self'); ?>">
                        <?php echo ($heritage_btn1['title']); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                            <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                        </svg>
                    </a>
                    <a href="<?php echo esc_url($heritage_btn2['url']); ?>" class="permalink_border" target="<?php echo esc_attr($heritage_btn2['target'] ?: '_self'); ?>">
                        <?php echo ($heritage_btn2['title']); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                            <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$title = get_field('title_timeline');
?>
<style>
    .timeline .splide__list::before {
        width: calc(100% * 4.65);
    }
</style>
<section class="timeline" id="timelines">
    <div class="container">
        <?php if ($timeline_title) : ?>
            <div class="timeline_head">
                <h2><?php echo $timeline_title; ?></h2>
            </div>
        <?php endif; ?>
    </div>
    <div class="container_side">
        <div class="timeline_body">
            <div class="splide" role="group" id="timeline">
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
                    <?php if (have_rows('timeline')): ?>
                        <ul class="splide__list">
                            <?php $i = 0; ?>
                            <?php while (have_rows('timeline')): the_row();
                                $year       = get_sub_field('year');
                                $title_item = get_sub_field('title_item');
                                $image_item = get_sub_field('image_item');
                                $image_item2 = get_sub_field('image_item2');
                                $image_url  = is_array($image_item) ? $image_item['url'] : $image_item;
                                $image_url2  = is_array($image_item2) ? $image_item2['url'] : $image_item2;
                            ?>
                                <li class="splide__slide">
                                    <div class="timecard">
                                        <div class="timecard-grid">
                                            <?php if ($i % 2 == 0):  ?>
                                                <div>
                                                    <?php if ($image_url): ?>
                                                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title_item); ?>">
                                                    <?php endif; ?>
                                                    <div class="content">
                                                        <p><?php echo wp_kses_post($title_item); ?></p>
                                                    </div>
                                                    <div class="timecard-time">
                                                        <span><?php echo esc_html($year); ?></span>
                                                    </div>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="42" viewBox="0 0 30 42" fill="none">
                                                        <path d="M16.4142 0.585785C15.6332 -0.195263 14.3668 -0.195263 13.5858 0.585785L0.857865 13.3137C0.0768159 14.0948 0.0768159 15.3611 0.857865 16.1421C1.63891 16.9232 2.90524 16.9232 3.68629 16.1421L15 4.82843L26.3137 16.1421C27.0948 16.9232 28.3611 16.9232 29.1421 16.1421C29.9232 15.3611 29.9232 14.0948 29.1421 13.3137L16.4142 0.585785ZM15 42L17 42L17 2L15 2L13 2L13 42L15 42Z" fill="#8C6E47" />
                                                    </svg>
                                                </div>
                                                <div></div>
                                            <?php else: ?>
                                                <div>
                                                    <?php if ($image_url2): ?>
                                                        <img src="<?php echo esc_url($image_url2); ?>" alt="<?php echo esc_attr($title_item); ?>">
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <?php if ($image_url): ?>
                                                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title_item); ?>">
                                                    <?php endif; ?>
                                                    <div class="content">
                                                        <p><?php echo wp_kses_post($title_item); ?></p>
                                                    </div>
                                                    <div class="timecard-time">
                                                        <span><?php echo esc_html($year); ?></span>
                                                    </div>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="43" viewBox="0 0 30 43" fill="none">
                                                        <path d="M13.5858 42.3595C14.3668 43.1406 15.6332 43.1406 16.4142 42.3595L29.1421 29.6316C29.9232 28.8506 29.9232 27.5842 29.1421 26.8032C28.3611 26.0221 27.0948 26.0221 26.3137 26.8032L15 38.1169L3.68629 26.8032C2.90524 26.0221 1.63891 26.0221 0.857863 26.8032C0.0768144 27.5842 0.0768144 28.8506 0.857863 29.6316L13.5858 42.3595ZM15 0.945312L13 0.945312L13 40.9453L15 40.9453L17 40.9453L17 0.945313L15 0.945312Z" fill="#8C6E47" />
                                                    </svg>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </li>
                            <?php $i++;
                            endwhile; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$s_title = get_field('specialists_title');
$stext =  get_field('specialists_text');
$s_link = get_field('specialists_btn');
?>
<section class="meet_our_specialist" id="specialists">
    <div class="meet_our_specialist-container">
        <div class="meet_our_specialist-head title_watermark">
            <div class="watermark"><p>Meet Our Specialists</p></div>
            <div class="breadlines">
                <p>Classic Vehicle Experts</p>
            </div>
            <h2>Meet Our Specialists</h2>
        </div>
        <div class="meet_our_specialist-slider">
            <div class="splide" role="group" id="specialist">
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
                            'post_type'      => 'team',
                            'posts_per_page' => -1,
                            'orderby'        => 'menu_order',
                            'order'          => 'ASC'
                        );
                        $team_query = new WP_Query($args);

                        if ($team_query->have_posts()):
                            while ($team_query->have_posts()): $team_query->the_post();
                                $job_position = get_field('job_position');
                                $team_email   = get_field('team_email');
                                $team_phone   = get_field('team_phone');
                                $content      = get_the_content();
                                $image        = get_the_post_thumbnail_url(get_the_ID(), 'medium_large');
                        ?>
                                <li class="splide__slide">
                                    <div class="specialist_card">
                                        <div class="specialist_card-front">
                                            <div class="specialist_card-image">
                                                <?php if ($image): ?>
                                                    <img src="<?php echo esc_url($image); ?>" alt="<?php the_title_attribute(); ?>">
                                                <?php else: ?>
                                                    <img src="<?php echo IMG; ?>/member.png" alt="<?php the_title_attribute(); ?>">
                                                <?php endif; ?>
                                            </div>
                                            <div class="specialist_card-info specialist_card-toggle">
                                                <div>
                                                    <p><?php the_title(); ?></p>
                                                    <?php if ($job_position): ?>
                                                        <span><?php echo esc_html($job_position); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <button type="button" class="card_toggle">
                                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M0 8.99943L18 8.99943M8.99969 0L8.99969 18" stroke="#F5F2EE"
                                                            stroke-width="2" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="specialist_card-back">
                                            <div class="specialist_card-toggle">
                                                <div>
                                                    <p><?php the_title(); ?></p>
                                                    <?php if ($job_position): ?>
                                                        <span><?php echo esc_html($job_position); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <button type="button" class="card_toggle minus">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="2"
                                                        viewBox="0 0 18 2" fill="none">
                                                        <path d="M0 1L18 0.999999" stroke="#F5F2EE" stroke-width="2" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="specialist_card-content">
                                                <ul>
                                                    <?php if ($team_email): ?>
                                                        <li>Email: <a href="mailto:<?php echo antispambot($team_email); ?>"><?php echo antispambot($team_email); ?></a></li>
                                                    <?php endif; ?>
                                                    <?php if ($team_phone): ?>
                                                        <li>Tel: <a href="tel:<?php echo preg_replace('/\D+/', '', $team_phone); ?>"><?php echo esc_html($team_phone); ?></a></li>
                                                    <?php endif; ?>
                                                </ul>
                                                <?php if ($content): ?>
                                                    <p>
                                                        <?php
                                                        $clean = preg_replace('/<\/(h3|p)>/i', '<br><br>', $content);
                                                        $clean = strip_tags($clean, '<br>');
                                                        $clean = preg_replace('/(<br>\s*)+/', '<br><br>', $clean);
                                                        $pos = strpos($clean, '<br><br>', strpos($clean, '<br><br>') + 1);
                                                        if ($pos !== false && $pos <= 342) {
                                                            $excerpt = substr($clean, 0, $pos);
                                                        } else {
                                                            $excerpt = mb_substr($clean, 0, 342);
                                                            if (mb_strlen(strip_tags($clean)) > 342 && mb_substr(trim($excerpt), -1) !== '.') {
                                                                $excerpt .= '...';
                                                            }
                                                        }
                                                        echo $excerpt;
                                                        ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                            <a href="<?php the_permalink(); ?>">Read More</a>
                                        </div>
                                    </div>
                                </li>
                        <?php
                            endwhile;
                            wp_reset_postdata();
                        endif;
                        ?>
                    </ul>

                </div>
            </div>
        </div>
    </div>
    <div class="meet_our_specialist-foot">
        <?php if (!empty($s_title)): ?>
            <div class="meet_our_specialist-title">
                <h2><?php echo $s_title; ?></h2>
            </div>
        <?php endif; ?>
        <?php if (!empty($stext)): ?>
            <div class="meet_our_specialist-content">
                <?php echo $stext; ?>
                <a href="<?php echo $s_link['url']; ?>" class="permalink_border">
                    <?php echo $s_link['title']; ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                        <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                    </svg>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php get_template_part('inc/sections/cta'); ?>

<?php
$titleb = get_field('title_private_sales');
$descriptionb = get_field('description_private_sales');
$linkb = get_field('link_private_sales');
$imagesb = get_field('images_pv');
?>
<section class="tailored" id="private-sales">
    <div class="tailored_container">
        <div class="tailored-flex">
            <div class="tailored_info">
                <div class="tailored_info-box">
                    <div class="tailored_info-box-ss">
                        <div class="breadlines">
                            <p>Tailored for Every Client</p>
                        </div>
                        <?php if (!empty($titleb)): ?>
                            <h2><?php echo $titleb; ?></h2>
                        <?php endif; ?>

                        <?php if (!empty($descriptionb)): ?>
                            <div class="tailored_info-content">
                                <?php echo $descriptionb; ?>
                            </div>
                        <?php endif; ?>


                        <a href="<?php echo $linkb['url']; ?>" class="permalink_border" alt="<?php echo $linkb['title']; ?>">
                            <?php echo $linkb['title']; ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                                <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                            </svg>
                        </a>
                    </div>

                </div>
            </div>


            <div class="tailored_images">
                <div class="imagelider-wrapper">
                    <div class="imagelider">
                        <?php if (have_rows('images_pv')): ?>
                            <?php while (have_rows('images_pv')): the_row(); ?>
                                <?php
                                $imagepv = get_sub_field('image_pv');
                                if ($imagepv):
                                ?>
                                    <div class="slide">
                                        <img src="<?php echo esc_url($imagepv['url']); ?>" alt="<?php echo esc_attr($imagepv['alt']); ?>">
                                    </div>
                                <?php endif; ?>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="imagelider-slide">
                    <div class="splide" id="mobile-slide">
                        <div class="splide__track">
                            <ul class="splide__list">
                                <?php if (have_rows('images_pv')): ?>
                                    <?php while (have_rows('images_pv')): the_row(); ?>
                                        <?php
                                        $imagepv = get_sub_field('image_pv');
                                        if ($imagepv):
                                        ?>
                                            <li class="splide__slide">
                                                <img src="<?php echo esc_url($imagepv['url']); ?>" alt="<?php echo esc_attr($imagepv['alt']); ?>">
                                            </li>
                                        <?php endif; ?>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
$auct_title = get_field('about_auction_title');
$auct_text = get_field('about_auction_text');
$auct_btn1 = get_field('about_auction_btn1');
$auct_btn2 = get_field('about_auction_btn2');
?>
<section class="upcoming" id="upcoming-auctions">
    <?php get_template_part('inc/sections/upcoming'); ?>
    <div class="upcoming_container">
        <div class="upcoming_info">
            <?php if ($auct_title): ?>
                <div class="upcoming_info-title">
                    <h2><?php echo $auct_title; ?></h2>
                </div>
            <?php endif; ?>
            <div class="upcoming_info-content">
                <?php if ($auct_text): ?>
                    <div>
                        <?php echo $auct_text; ?>
                    </div>
                <?php endif; ?>
                <div class="upcoming_info-actions">
                    <?php if ($auct_btn1): ?>
                        <a href="<?php echo esc_url($auct_btn1['url']); ?>" class="permalink_border" target="<?php echo esc_attr($auct_btn1['target'] ?: '_self'); ?>">
                            <?php echo esc_html($auct_btn1['title']); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                                <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                            </svg>
                        </a>
                    <?php endif; ?>
                    <?php if ($auct_btn2): ?>
                        <a href="<?php echo esc_url($auct_btn2['url']); ?>" class="permalink_border" target="<?php echo esc_attr($auct_btn2['target'] ?: '_self'); ?>">
                            <?php echo esc_html($auct_btn2['title']); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                                <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>