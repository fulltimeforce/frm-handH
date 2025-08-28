<?php
/*
    Template name: about
*/

get_header();

get_banner('Homepage / About / About H&H Classics', get_the_post_thumbnail_url(get_the_ID(), 'full'), 'About H&H Classics');

?>

<section class="heritage">
    <div class="heritage_container">
        <div class="tabs">
            <button type="button" class="active">Our Heritage</button>
            <button type="button">Timeline</button>
            <button type="button">Our Specialists</button>
            <button type="button">Private Sales</button>
            <button type="button">Upcoming Auctions</button>
        </div>
        <div class="heritage_information">
            <div class="heritage_images">
                <img class="heritage_images-main" src="<?php echo IMG; ?>/about/4.png">
                <div class="heritage_images-slider">
                    <div id="heritage" class="splide">
                        <div class="splide__track">
                            <ul class="splide__list">
                                <li class="splide__slide">
                                    <img src="<?php echo IMG; ?>/1.jpg" alt="Imagen 1">
                                </li>
                                <li class="splide__slide">
                                    <img src="<?php echo IMG; ?>/about/4.png" alt="Imagen 2">
                                </li>
                                <li class="splide__slide">
                                    <img src="<?php echo IMG; ?>/1.jpg" alt="Imagen 3">
                                </li>
                                <li class="splide__slide">
                                    <img src="<?php echo IMG; ?>/about/4.png" alt="Imagen 2">
                                </li>
                            </ul>
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
                <h2><span>Our Heritage:</span> A Legacy of Successes</h2>
                <div class="content">
                    <p>
                        Founded in 1993 by Simon Hope and Mark Hamilton, H&H Classics has become synonymous with
                        expertise and success in the world of classic and collector vehicles. Over the past 30 years,
                        we’ve sold more than 15,000 motorcars and motorcycles to collectors across the globe, achieving
                        record-breaking sales and global recognition.
                        <br><br>
                        Our achievements include selling a car for over $11 million—a 1960 Ferrari 250 GT SWB—and nearly
                        £2 million worth of motorcycles in a single day. With over 75,000 loyal clients globally, H&H
                        Classics has cemented its reputation for delivering excellence.
                    </p>
                </div>
                <div class="actions">
                    <a href="#" class="permalink_border">
                        Explore Upcoming Actions
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                            <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                        </svg>
                    </a>
                    <a href="#" class="permalink_border">
                        View Our Services
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
    width: calc(100% * 2.65);
}
</style>
<section class="timeline">
    <div class="container">
        <div class="timeline_head">
            <h2>A timeline of our journey over the past three decades</h2>
        </div>
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
                    <ul class="splide__list">
                        <li class="splide__slide">
                            <div class="timecard">
                                <div class="timecard-grid">
                                    <div>
                                        <img src="<?php echo IMG; ?>/logo.svg">
                                        <div class="content">
                                            <p><span>First Auction at <br>Pavilion Gardens, Buxton <br>H&H is
                                                    established by <br>Simon Hope</span></p>
                                        </div>
                                        <div class="timecard-time">
                                            <span>1993</span>
                                        </div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="42"
                                            viewBox="0 0 30 42" fill="none">
                                            <path
                                                d="M16.4142 0.585785C15.6332 -0.195263 14.3668 -0.195263 13.5858 0.585785L0.857865 13.3137C0.0768159 14.0948 0.0768159 15.3611 0.857865 16.1421C1.63891 16.9232 2.90524 16.9232 3.68629 16.1421L15 4.82843L26.3137 16.1421C27.0948 16.9232 28.3611 16.9232 29.1421 16.1421C29.9232 15.3611 29.9232 14.0948 29.1421 13.3137L16.4142 0.585785ZM15 42L17 42L17 2L15 2L13 2L13 42L15 42Z"
                                                fill="#8C6E47" />
                                        </svg>
                                    </div>
                                    <div></div>
                                </div>
                            </div>
                        </li>
                        <li class="splide__slide">
                            <div class="timecard">
                                <div class="timecard-grid">
                                    <div></div>
                                    <div>
                                        <img src="<?php echo IMG; ?>/slide1.png">
                                        <div class="content">
                                            <p>1968 Lotus 49 F1 Single-seater <br>(ex Rob Walker) <br>Sold: £367,500</p>
                                        </div>
                                        <div class="timecard-time">
                                            <span>1999</span>
                                        </div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="43"
                                            viewBox="0 0 30 43" fill="none">
                                            <path
                                                d="M13.5858 42.3595C14.3668 43.1406 15.6332 43.1406 16.4142 42.3595L29.1421 29.6316C29.9232 28.8506 29.9232 27.5842 29.1421 26.8032C28.3611 26.0221 27.0948 26.0221 26.3137 26.8032L15 38.1169L3.68629 26.8032C2.90524 26.0221 1.63891 26.0221 0.857863 26.8032C0.0768144 27.5842 0.0768144 28.8506 0.857863 29.6316L13.5858 42.3595ZM15 0.945312L13 0.945312L13 40.9453L15 40.9453L17 40.9453L17 0.945313L15 0.945312Z"
                                                fill="#8C6E47" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="splide__slide">
                            <div class="timecard">
                                <div class="timecard-grid">
                                    <div>
                                        <img src="<?php echo IMG; ?>/face1.png" class="round">
                                        <div class="content">
                                            <p>Damian Jones joined H&H</p>
                                        </div>
                                        <div class="timecard-time">
                                            <span>2005</span>
                                        </div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="42"
                                            viewBox="0 0 30 42" fill="none">
                                            <path
                                                d="M16.4142 0.585785C15.6332 -0.195263 14.3668 -0.195263 13.5858 0.585785L0.857865 13.3137C0.0768159 14.0948 0.0768159 15.3611 0.857865 16.1421C1.63891 16.9232 2.90524 16.9232 3.68629 16.1421L15 4.82843L26.3137 16.1421C27.0948 16.9232 28.3611 16.9232 29.1421 16.1421C29.9232 15.3611 29.9232 14.0948 29.1421 13.3137L16.4142 0.585785ZM15 42L17 42L17 2L15 2L13 2L13 42L15 42Z"
                                                fill="#8C6E47" />
                                        </svg>
                                    </div>
                                    <div></div>
                                </div>
                            </div>
                        </li>
                        <li class="splide__slide">
                            <div class="timecard">
                                <div class="timecard-grid">
                                    <div></div>
                                    <div>
                                        <img src="<?php echo IMG; ?>/circle1.png" class="round">
                                        <div class="content">
                                            <p>First Auction at <br>Imperial War Museum, Duxford</p>
                                        </div>
                                        <div class="timecard-time">
                                            <span>2007</span>
                                        </div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="43"
                                            viewBox="0 0 30 43" fill="none">
                                            <path
                                                d="M13.5858 42.3595C14.3668 43.1406 15.6332 43.1406 16.4142 42.3595L29.1421 29.6316C29.9232 28.8506 29.9232 27.5842 29.1421 26.8032C28.3611 26.0221 27.0948 26.0221 26.3137 26.8032L15 38.1169L3.68629 26.8032C2.90524 26.0221 1.63891 26.0221 0.857863 26.8032C0.0768144 27.5842 0.0768144 28.8506 0.857863 29.6316L13.5858 42.3595ZM15 0.945312L13 0.945312L13 40.9453L15 40.9453L17 40.9453L17 0.945313L15 0.945312Z"
                                                fill="#8C6E47" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="splide__slide">
                            <div class="timecard">
                                <div class="timecard-grid">
                                    <div>
                                        <img src="<?php echo IMG; ?>/slide2.png">
                                        <div class="content">
                                            <p>1929 Bentley 41/2 Litre <br>(ex Woolf Barnato) <br>Sold: 537,600
                                                <br>(resold: £874,00 in 2018)</p>
                                        </div>
                                        <div class="timecard-time">
                                            <span>2011</span>
                                        </div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="42"
                                            viewBox="0 0 30 42" fill="none">
                                            <path
                                                d="M16.4142 0.585785C15.6332 -0.195263 14.3668 -0.195263 13.5858 0.585785L0.857865 13.3137C0.0768159 14.0948 0.0768159 15.3611 0.857865 16.1421C1.63891 16.9232 2.90524 16.9232 3.68629 16.1421L15 4.82843L26.3137 16.1421C27.0948 16.9232 28.3611 16.9232 29.1421 16.1421C29.9232 15.3611 29.9232 14.0948 29.1421 13.3137L16.4142 0.585785ZM15 42L17 42L17 2L15 2L13 2L13 42L15 42Z"
                                                fill="#8C6E47" />
                                        </svg>
                                    </div>
                                    <div></div>
                                </div>
                            </div>
                        </li>
                        <li class="splide__slide">
                            <div class="timecard">
                                <div class="timecard-grid">
                                    <div>
                                        <img src="<?php echo IMG; ?>/face2.png" class="round">
                                    </div>
                                    <div>
                                        <img src="<?php echo IMG; ?>/slide2.png">
                                        <div class="content">
                                            <p><span>Colette Mckay joined H&H</span> <br>1922 Brough Superior SS80 'Old
                                                Bill' <br>Sold: £292,500</p>
                                        </div>
                                        <div class="timecard-time">
                                            <span>2012</span>
                                        </div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="43"
                                            viewBox="0 0 30 43" fill="none">
                                            <path
                                                d="M13.5858 42.3595C14.3668 43.1406 15.6332 43.1406 16.4142 42.3595L29.1421 29.6316C29.9232 28.8506 29.9232 27.5842 29.1421 26.8032C28.3611 26.0221 27.0948 26.0221 26.3137 26.8032L15 38.1169L3.68629 26.8032C2.90524 26.0221 1.63891 26.0221 0.857863 26.8032C0.0768144 27.5842 0.0768144 28.8506 0.857863 29.6316L13.5858 42.3595ZM15 0.945312L13 0.945312L13 40.9453L15 40.9453L17 40.9453L17 0.945313L15 0.945312Z"
                                                fill="#8C6E47" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="splide__slide">
                            <div class="timecard">
                                <div class="timecard-grid">
                                    <div>
                                        <img src="<?php echo IMG; ?>/slide3.png">
                                        <div class="content">
                                            <p>1960 Ferrari 250 GT SWB <br>1967 Ferrari 275 GTB/4 <br>Sold combined:
                                                <br>£9,758,320</p>
                                        </div>
                                        <div class="timecard-time">
                                            <span>2015</span>
                                        </div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="42"
                                            viewBox="0 0 30 42" fill="none">
                                            <path
                                                d="M16.4142 0.585785C15.6332 -0.195263 14.3668 -0.195263 13.5858 0.585785L0.857865 13.3137C0.0768159 14.0948 0.0768159 15.3611 0.857865 16.1421C1.63891 16.9232 2.90524 16.9232 3.68629 16.1421L15 4.82843L26.3137 16.1421C27.0948 16.9232 28.3611 16.9232 29.1421 16.1421C29.9232 15.3611 29.9232 14.0948 29.1421 13.3137L16.4142 0.585785ZM15 42L17 42L17 2L15 2L13 2L13 42L15 42Z"
                                                fill="#8C6E47" />
                                        </svg>
                                    </div>
                                    <div></div>
                                </div>
                            </div>
                        </li>
                        <li class="splide__slide">
                            <div class="timecard">
                                <div class="timecard-grid">
                                    <div></div>
                                    <div>
                                        <img src="<?php echo IMG; ?>/slide4.png">
                                        <div class="content">
                                            <p><span>First Auction at National Motorcycle Museum</span> <br>1964 Ferrari
                                                330GT Nembo Spider <br>Sold: £609,500 <br>1996 Subaru Impreza WRC '97
                                                (ex Colin McRae) <br>Sold: £235,750</p>
                                        </div>
                                        <div class="timecard-time">
                                            <span>2017</span>
                                        </div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="43"
                                            viewBox="0 0 30 43" fill="none">
                                            <path
                                                d="M13.5858 42.3595C14.3668 43.1406 15.6332 43.1406 16.4142 42.3595L29.1421 29.6316C29.9232 28.8506 29.9232 27.5842 29.1421 26.8032C28.3611 26.0221 27.0948 26.0221 26.3137 26.8032L15 38.1169L3.68629 26.8032C2.90524 26.0221 1.63891 26.0221 0.857863 26.8032C0.0768144 27.5842 0.0768144 28.8506 0.857863 29.6316L13.5858 42.3595ZM15 0.945312L13 0.945312L13 40.9453L15 40.9453L17 40.9453L17 0.945313L15 0.945312Z"
                                                fill="#8C6E47" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
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
<section class="meet_our_specialist">
    <div class="meet_our_specialist-container">
        <div class="meet_our_specialist-head">
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
        'posts_per_page' => -1, // todos, o cambia a 8 si quieres limitar
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
            $image        = get_the_post_thumbnail_url(get_the_ID(), 'medium'); // usa la imagen destacada
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
                                            As a qualified accounting technician, Colette joined H&H in 2012 and has
                                            been involved in all aspects of the business and industry.
                                            <br><br>
                                            From a young age, her father has been taking her to car meets, race meetings
                                            and shows. As an adrenaline junkie, she can still be found at the same shows
                                            and race meetings but now with her own sons.
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
<section class="tailored">
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
            </div>


        </div>
    </div>
</section>

<section class="upcoming">
    <?php get_template_part('inc/sections/upcoming'); ?>
    <div class="upcoming_container">
        <div class="upcoming_info">
            <div class="upcoming_info-title">
                <h2>With 83% sell-through rate across live and online sales</h2>
            </div>
            <div class="upcoming_info-content">
                <p>
                    H&H Classics connects collectors from around the world through live and online auctions. With
                    prestigious venues like the Imperial War Museum, Duxford, and the Pavilion Gardens, Buxton, and a
                    global network spanning over 20 countries, we ensure every lot receives maximum exposure.
                    <br><br>
                    Our auctions boast an impressive 83% sell-through rate, thanks to our beautifully curated luxury
                    catalogues and innovative online bidding platform. For every bidder in the room at our live events,
                    three are registered online, with successful sales often shipping thousands of miles to new owners.
                </p>
                <div class="upcoming_info-actions">
                    <a href="#" class="permalink_border">
                        Explore Upcoming Auctions
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                            <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                        </svg>
                    </a>
                    <a href="#" class="permalink_border">
                        View Our Services
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                            <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>