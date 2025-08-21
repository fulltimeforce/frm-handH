<?php
/*
    Template name: testimonials
*/

get_header();

//get fields
$video_testimonial = get_field('testimonial_video');
$video_title = get_field('testimonial_video_title');

get_banner('Homepage / About / Testimonials');

?>

<section class="testimonials_page">
    <div class="container">
        <h2>Trustpilot Reviews</h2>
        <div class="splide trustpilot_reviews" aria-label="Trustpilot Reviews">
            <div class="splide__track">
                <ul class="splide__list">
                <?php for ($i = 1; $i <= 5; $i++) : ?>
                    <li class="splide__slide">
                    <div class="trustpilot_reviews-item">
                        <div class="trustpilot_reviews-stars">
                        <?php for ($s = 1; $s <= 4; $s++) : ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                            <path d="M8.76224 0.731762C8.83707 0.501435 9.16293 0.501435 9.23776 0.731763L11.189 6.73708C11.2225 6.84009 11.3185 6.90983 11.4268 6.90983H17.7411C17.9833 6.90983 18.084 7.21973 17.8881 7.36208L12.7797 11.0736C12.692 11.1372 12.6554 11.2501 12.6888 11.3531L14.6401 17.3584C14.7149 17.5887 14.4513 17.7803 14.2554 17.6379L9.14695 13.9264C9.05932 13.8628 8.94068 13.8628 8.85305 13.9264L3.74462 17.6379C3.54869 17.7803 3.28507 17.5887 3.35991 17.3584L5.31116 11.3531C5.34463 11.2501 5.30796 11.1372 5.22034 11.0736L0.11191 7.36208C-0.0840186 7.21973 0.0166752 6.90983 0.258856 6.90983H6.57322C6.68153 6.90983 6.77752 6.84009 6.81099 6.73708L8.76224 0.731762Z"
                                    fill="#8C6E47" />
                            </svg>
                        <?php endfor; ?>
                        </div>

                        <p class="trustpilot_reviews-subtitle p24">Excellent Result</p>

                        <div class="trustpilot_reviews-text">
                        <p>
                            “I recently sold my Jaguar XJ at H and H auctions. Baljit handled the marketing and was
                            extremely helpful throughout the process. He took no end of excellent photographs of the car and
                            his approach was the same as if it had been a top end Rolls Royce. I achieved double the price I
                            was offered by dealers, an excellent result!”
                        </p>
                        </div>

                        <div class="comment_author">
                        <div class="comment_photo">RA</div>
                        <span>Robert Anderson</span>
                        </div>
                    </div>
                    </li>
                <?php endfor; ?>
                </ul>
            </div>
        </div>
        <div class="trustpilot_actions">
            <a>View All Trustpilot Reviews</a>
            <a>View Welcome Booklet</a>
        </div>
        <div class="testimonials_video w-100">
            <div class="video">
                <video autoplay playsinline muted loop>
                    <source src="<?php echo $video_testimonial; ?>">
                </video>
            </div>
            <p class="p24"><?php echo $video_title; ?></p>
        </div>
    </div>
</section>
<section class="testimonials_list">
    <div class="container">
        <?php
        $args = array(
            'post_type'      => 'testimonials',
            'posts_per_page' => 12,
            'orderby'        => 'date',
            'order'          => 'DESC',
        );
        $testimonials = new WP_Query($args);
        if ( $testimonials->have_posts() ) :
            while ( $testimonials->have_posts() ) : $testimonials->the_post();
                $name  = get_field('testimonials_name');
                $stars = get_field('testimonials_stars');
                $initials = '';
                if ( $name ) {
                    $words = explode(' ', $name);
                    foreach ($words as $w) {
                        $initials .= strtoupper(mb_substr($w, 0, 1));
                    }
                }
                ?>
        <div class="testimonials_list-item">
            <div class="testimonials_list-icon w-100">
                <p class="p24"><?php echo esc_html($initials); ?></p>
            </div>
            <div class="testimonials_list-info">

                <div class="testimonials_list-star">
                    <?php if ($stars) : ?>
                    <?php for ($i = 0; $i < intval($stars); $i++) : ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path
                            d="M8.76224 0.731762C8.83707 0.501435 9.16293 0.501435 9.23776 0.731763L11.189 6.73708C11.2225 6.84009 11.3185 6.90983 11.4268 6.90983H17.7411C17.9833 6.90983 18.084 7.21973 17.8881 7.36208L12.7797 11.0736C12.692 11.1372 12.6554 11.2501 12.6888 11.3531L14.6401 17.3584C14.7149 17.5887 14.4513 17.7803 14.2554 17.6379L9.14695 13.9264C9.05932 13.8628 8.94068 13.8628 8.85305 13.9264L3.74462 17.6379C3.54869 17.7803 3.28507 17.5887 3.35991 17.3584L5.31116 11.3531C5.34463 11.2501 5.30796 11.1372 5.22034 11.0736L0.11191 7.36208C-0.0840186 7.21973 0.0166752 6.90983 0.258856 6.90983H6.57322C6.68153 6.90983 6.77752 6.84009 6.81099 6.73708L8.76224 0.731762Z"
                            fill="#8C6E47" />
                    </svg>
                    <?php endfor; ?>
                    <?php endif; ?>
                </div>

                <p class="p24"><?php the_title(); ?></p>

                <div class="testimonials_list-text">
                    <p><?php the_content(); ?></p>
                </div>
                <div class="testimonials_list-date">
                    <p class="p24">- <?php echo esc_html($name); ?></p>
                    <p class="p14">(<?php echo strtoupper(get_the_date('F Y')); ?>)</p>
                </div>
            </div>
        </div>
        <?php endwhile;
            wp_reset_postdata();
        endif;
        ?>
    </div>
</section>
<?php get_template_part('inc/sections/cta'); ?>
<section class="upcoming" id="upcoming-auctions">
    <?php get_template_part('inc/sections/upcoming'); ?>
</section>
<?php get_footer(); ?>