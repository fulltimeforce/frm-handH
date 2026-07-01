<?php
$why_subtitle = get_field('whychoose_subtitle');
$why_title = get_field('whychoose_title');
$why_text = get_field('whychoose_text');
$why_p = get_field('whychoose_p');

$why_button2 = get_field('whychoose_button1');
$why_button1 = get_field('whychoose_button2');
$why_button2_text = get_field('whychoose_button1_text');
$why_button1_text = get_field('whychoose_button2_text');
?>
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
                <?php while (have_rows('whychoose_images')):
                  the_row();
                  $image = get_sub_field('whychoose_image');
                  if ($image): ?>
                    <li class="splide__slide">
                      <img src="<?php echo esc_url($image); ?>" alt="Image" title="Image" loading="lazy" decoding="async">
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
        <?php while (have_rows('stats')):
          the_row(); ?>
          <?php
          $number = get_sub_field('stats_number');
          $text = get_sub_field('stats_text');
          $content = get_sub_field('stats_description');
          ?>
          <div>
            <?php if ($number): ?>
              <h3 class="stat_number" data-target="<?php echo esc_attr($number); ?>"><?php echo esc_html($number); ?>
              </h3>
            <?php endif; ?>

            <?php if ($text): ?>
              <p><?php echo esc_html($text); ?></p>
            <?php endif; ?>

            <?php if ($content): ?>
              <span><?php echo $content; ?></span>
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
      <?php if ($why_button1 && !empty($why_button1_text)): ?>
        <a href="<?php echo esc_url($why_button1); ?>" class="permalink">
          <?php echo $why_button1_text; ?>
          <svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18" fill="none">
            <path
              d="M9.5 4.55556V17M9.5 4.55556C9.5 3.61256 9.12072 2.70819 8.44558 2.0414C7.77045 1.3746 6.85478 1 5.9 1H1.4C1.16131 1 0.932387 1.09365 0.763604 1.26035C0.594821 1.42705 0.5 1.65314 0.5 1.88889V13.4444C0.5 13.6802 0.594821 13.9063 0.763604 14.073C0.932387 14.2397 1.16131 14.3333 1.4 14.3333H6.8C7.51608 14.3333 8.20284 14.6143 8.70919 15.1144C9.21554 15.6145 9.5 16.2928 9.5 17M9.5 4.55556C9.5 3.61256 9.87928 2.70819 10.5544 2.0414C11.2295 1.3746 12.1452 1 13.1 1H17.6C17.8387 1 18.0676 1.09365 18.2364 1.26035C18.4052 1.42705 18.5 1.65314 18.5 1.88889V13.4444C18.5 13.6802 18.4052 13.9063 18.2364 14.073C18.0676 14.2397 17.8387 14.3333 17.6 14.3333H12.2C11.4839 14.3333 10.7972 14.6143 10.2908 15.1144C9.78446 15.6145 9.5 16.2928 9.5 17"
              stroke="#8C6E47" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </a>
      <?php endif; ?>
    </div>
    <?php if ($why_button2 && !empty($why_button2_text)): ?>
      <a href="<?php echo esc_url($why_button2); ?>" class="permalink_border">
        <?php echo $why_button2_text; ?>
        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
          <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
        </svg>
      </a>
    <?php endif; ?>
  </div>
</section>