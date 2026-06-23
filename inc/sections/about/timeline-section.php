<?php
$timeline_title = get_field('title_timeline');
?>

<section class="timeline" id="timelines">
  <div class="container">
    <?php if ($timeline_title): ?>
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
              <?php while (have_rows('timeline')):
                the_row();
                $year = get_sub_field('year');
                $title_item = get_sub_field('title_item');
                $image_item = get_sub_field('image_item');
                $image_item2 = get_sub_field('image_item2');
                $image_url = is_array($image_item) ? $image_item['url'] : $image_item;
                $image_url2 = is_array($image_item2) ? $image_item2['url'] : $image_item2;
                ?>
                <li class="splide__slide">
                  <div class="timecard">
                    <div class="timecard-grid">
                      <?php if ($i % 2 == 0): ?>
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
                            <path
                              d="M16.4142 0.585785C15.6332 -0.195263 14.3668 -0.195263 13.5858 0.585785L0.857865 13.3137C0.0768159 14.0948 0.0768159 15.3611 0.857865 16.1421C1.63891 16.9232 2.90524 16.9232 3.68629 16.1421L15 4.82843L26.3137 16.1421C27.0948 16.9232 28.3611 16.9232 29.1421 16.1421C29.9232 15.3611 29.9232 14.0948 29.1421 13.3137L16.4142 0.585785ZM15 42L17 42L17 2L15 2L13 2L13 42L15 42Z"
                              fill="#8C6E47" />
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
                            <path
                              d="M13.5858 42.3595C14.3668 43.1406 15.6332 43.1406 16.4142 42.3595L29.1421 29.6316C29.9232 28.8506 29.9232 27.5842 29.1421 26.8032C28.3611 26.0221 27.0948 26.0221 26.3137 26.8032L15 38.1169L3.68629 26.8032C2.90524 26.0221 1.63891 26.0221 0.857863 26.8032C0.0768144 27.5842 0.0768144 28.8506 0.857863 29.6316L13.5858 42.3595ZM15 0.945312L13 0.945312L13 40.9453L15 40.9453L17 40.9453L17 0.945313L15 0.945312Z"
                              fill="#8C6E47" />
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