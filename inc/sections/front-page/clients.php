<?php
$testimonials_subtitle = get_field('testimonials_subtitle');
$testimonials_title = get_field('testimonials_title');

?>
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
          <button class="splide__arrow splide__arrow--prev" title="Prev">
            <svg xmlns="http://www.w3.org/2000/svg" width="50" height="26" viewBox="0 0 50 26" fill="none">
              <path d="M0 13H48M48 13L36 1M48 13L36 25" stroke="#8C6E47" stroke-width="2" />
            </svg>
          </button>
          <button class="splide__arrow splide__arrow--next" title="Next">
            <svg xmlns="http://www.w3.org/2000/svg" width="50" height="26" viewBox="0 0 50 26" fill="none">
              <path d="M0 13H48M48 13L36 1M48 13L36 25" stroke="#8C6E47" stroke-width="2" />
            </svg>
          </button>
        </div>
        <div class="splide__track">
          <ul class="splide__list">
            <?php
            $args = array(
              'post_type' => 'testimonials',
              'posts_per_page' => 6,
              'orderby' => 'date',
              'order' => 'DESC'
            );
            $query = new WP_Query($args);

            if ($query->have_posts()): ?>
              <?php while ($query->have_posts()):
                $query->the_post(); ?>
                <?php
                $name = get_field('testimonials_name');
                $stars = get_field('testimonials_stars'); // número de estrellas
                $title = get_the_title();
                $text = get_the_content();
                ?>
                <li class="splide__slide">
                  <div class="comment">
                    <div class="comment_info">
                      <div class="stars">
                        <?php for ($s = 0; $s < intval($stars); $s++): ?>
                          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                            <path
                              d="M8.76224 0.731762C8.83707 0.501435 9.16293 0.501435 9.23776 0.731763L11.189 6.73708C11.2225 6.84009 11.3185 6.90983 11.4268 6.90983H17.7411C17.9833 6.90983 18.084 7.21973 17.8881 7.36208L12.7797 11.0736C12.692 11.1372 12.6554 11.2501 12.6888 11.3531L14.6401 17.3584C14.7149 17.5887 14.4513 17.7803 14.2554 17.6379L9.14695 13.9264C9.05932 13.8628 8.94068 13.8628 8.85305 13.9264L3.74462 17.6379C3.54869 17.7803 3.28507 17.5887 3.35991 17.3584L5.31116 11.3531C5.34463 11.2501 5.30796 11.1372 5.22034 11.0736L0.11191 7.36208C-0.0840186 7.21973 0.0166752 6.90983 0.258856 6.90983H6.57322C6.68153 6.90983 6.77752 6.84009 6.81099 6.73708L8.76224 0.731762Z"
                              fill="#8C6E47" />
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