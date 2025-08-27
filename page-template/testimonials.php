<?php
/*
    Template name: testimonials
*/

get_header();

//get fields
$video_testimonial = get_field('testimonial_video');
$video_title = get_field('testimonial_video_title');

get_banner('Homepage / About / Testimonials', get_the_post_thumbnail_url(get_the_ID(), 'full'), 'Testimonials');

?>

<section class="testimonials_page">
    <div class="container">
        <h2>Trustpilot Reviews</h2>
        <div class="splide trustpilot_reviews" aria-label="Trustpilot Reviews">
            <div class="splide__track">
                <ul class="splide__list">
                <?php if( have_rows('testimonial_reviews') ): ?>
                <?php while( have_rows('testimonial_reviews') ): the_row(); 
                    $stars = get_sub_field('review_stars');
                    $title = get_sub_field('review_title');
                    $text = get_sub_field('review_text');
                    $name = get_sub_field('review_name');
                ?>
                    <li class="splide__slide">
                        <div class="trustpilot_reviews-item">
                            <div class="trustpilot_reviews-stars">
                                <?php for( $s = 1; $s <= $stars; $s++ ): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                        <path d="M8.76224 0.731762C8.83707 0.501435 9.16293 0.501435 9.23776 0.731763L11.189 6.73708C11.2225 6.84009 11.3185 6.90983 11.4268 6.90983H17.7411C17.9833 6.90983 18.084 7.21973 17.8881 7.36208L12.7797 11.0736C12.692 11.1372 12.6554 11.2501 12.6888 11.3531L14.6401 17.3584C14.7149 17.5887 14.4513 17.7803 14.2554 17.6379L9.14695 13.9264C9.05932 13.8628 8.94068 13.8628 8.85305 13.9264L3.74462 17.6379C3.54869 17.7803 3.28507 17.5887 3.35991 17.3584L5.31116 11.3531C5.34463 11.2501 5.30796 11.1372 5.22034 11.0736L0.11191 7.36208C-0.0840186 7.21973 0.0166752 6.90983 0.258856 6.90983H6.57322C6.68153 6.90983 6.77752 6.84009 6.81099 6.73708L8.76224 0.731762Z" fill="#8C6E47"/>
                                    </svg>
                                <?php endfor; ?>
                            </div>
                            <p class="trustpilot_reviews-subtitle p24"><?php echo esc_html($title); ?></p>
                            <div class="trustpilot_reviews-text">
                                <p><?php echo $text; ?></p>
                            </div>
                            <div class="comment_author">
                                <div class="comment_photo"><?php echo esc_html(substr($name, 0, 2)); ?></div>
                                <span><?php echo esc_html($name); ?></span>
                            </div>

                        </div>
                    </li>
                <?php endwhile; ?>
            <?php endif; ?>
                </ul>
            </div>
        </div>
        <div class="trustpilot_actions">
            <?php 
            $button1 = get_field('review_button1');
            $button2 = get_field('review_button2');
            ?>

            <?php if( $button1 ): ?>
                <a href="<?php echo esc_url($button1['url']); ?>" target="<?php echo esc_attr($button1['target'] ?: '_self'); ?>">
                    <?php echo esc_html($button1['title']); ?>
                </a>
            <?php endif; ?>

            <?php if( $button2 ): ?>
                <a href="<?php echo esc_url($button2['url']); ?>" target="<?php echo esc_attr($button2['target'] ?: '_self'); ?>">
                    <?php echo esc_html($button2['title']); ?>
                </a>
            <?php endif; ?>
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
        $posts_per_page = isset($_GET['posts_per_page']) ? intval($_GET['posts_per_page']) : 12;
        $paged = get_query_var('paged') ? get_query_var('paged') : 1;

        $args = array(
            'post_type'      => 'testimonials',
            'posts_per_page' => $posts_per_page,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'paged'          => $paged,
        );

        $testimonials = new WP_Query($args);
        $total_items = $testimonials->found_posts;
        ?>

        <?php if ( $testimonials->have_posts() ) : ?>
            <?php while ( $testimonials->have_posts() ) : $testimonials->the_post(); 
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
                                    <path d="M8.76224 0.731762C8.83707 0.501435 9.16293 0.501435 9.23776 0.731763L11.189 6.73708C11.2225 6.84009 11.3185 6.90983 11.4268 6.90983H17.7411C17.9833 6.90983 18.084 7.21973 17.8881 7.36208L12.7797 11.0736C12.692 11.1372 12.6554 11.2501 12.6888 11.3531L14.6401 17.3584C14.7149 17.5887 14.4513 17.7803 14.2554 17.6379L9.14695 13.9264C9.05932 13.8628 8.94068 13.8628 8.85305 13.9264L3.74462 17.6379C3.54869 17.7803 3.28507 17.5887 3.35991 17.3584L5.31116 11.3531C5.34463 11.2501 5.30796 11.1372 5.22034 11.0736L0.11191 7.36208C-0.0840186 7.21973 0.0166752 6.90983 0.258856 6.90983H6.57322C6.68153 6.90983 6.77752 6.84009 6.81099 6.73708L8.76224 0.731762Z" fill="#8C6E47"/>
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
            <?php endwhile; wp_reset_postdata(); ?>
        <?php endif; ?>

        <div class="testimonials_pagination">
            <?php
            echo paginate_links( array(
                'total'   => $testimonials->max_num_pages,
                'current' => $paged,
                'mid_size'=> 2,
                'prev_text' => __('<svg xmlns="http://www.w3.org/2000/svg" width="19" height="14" viewBox="0 0 19 14" fill="none">
  <path d="M19 7L1.00049 7M1.00049 7L7.00049 13M1.00049 7L7.0005 0.999999" stroke="#8C6E47"/>
</svg>'),
                    'next_text' => __('<svg xmlns="http://www.w3.org/2000/svg" width="19" height="14" viewBox="0 0 19 14" fill="none">
  <path d="M-7.15494e-08 7L17.9995 7M17.9995 7L11.9995 1M17.9995 7L11.9995 13" stroke="#8C6E47"/>
</svg>'),
            ));
            ?>
        </div>

        <div class="testimonials_summary">
            <?php $options = [12, 24, 32]; ?>
            <form method="get" id="posts_per_page_form">
                <label for="posts_per_page_select">Showing </label>
                <select name="posts_per_page" id="posts_per_page_select" onchange="this.form.submit()">
                    <?php foreach($options as $opt): ?>
                        <option value="<?php echo $opt; ?>" <?php selected($posts_per_page, $opt); ?>><?php echo $opt; ?></option>
                    <?php endforeach; ?>
                </select>
                <span>items out of <?php echo $total_items; ?></span>
            </form>
        </div>

    </div>
</section>

<?php get_template_part('inc/sections/cta'); ?>
<section class="upcoming" id="upcoming-auctions">
    <?php get_template_part('inc/sections/upcoming'); ?>
</section>
<?php get_footer(); ?>