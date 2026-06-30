<?php
$successes_subtitle = get_field('success_subtitle');
$successes_title = get_field('success_title');
$successes_text = get_field('success_text');
?>
<?php if (have_rows('slider_top') || have_rows('slider_bottom')): ?>
  <section class="our_successes">
    <div class="container">
      <div class="our_successes-head">
        <?php if ($successes_subtitle): ?>
          <div class="breadlines">
            <p>
              <?php echo $successes_subtitle; ?>
            </p>
          </div>
        <?php endif; ?>
        <?php if ($successes_title): ?>
          <h2>
            <?php echo $successes_title; ?>
          </h2>
        <?php endif; ?>
        <?php if ($successes_text): ?>
          <p>
            <?php echo $successes_text; ?>
          </p>
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
                <?php while (have_rows('slider_top')):
                  the_row(); ?>
                  <li class="splide__slide">
                    <div class="car_card">
                      <div class="car_card-flex">
                        <div class="car_card-image">
                          <div class="car_card-thumb">
                            <img src="<?php echo get_sub_field('image_vehicle_1')['url'] ?>" alt="<?php echo get_sub_field('vehicle_name'); ?>" width="<?php echo get_sub_field('image_vehicle_1')['width'] ?>" height="<?php echo get_sub_field('image_vehicle_1')['height'] ?>" loading="lazy" title="<?php echo get_sub_field('vehicle_name'); ?>">

                            <?php if (!empty(get_sub_field('link_vehicle'))): ?>
                              <div class="permalink">
                                <a href="<?php echo get_sub_field('link_vehicle'); ?>">View</a>
                              </div>
                            <?php endif; ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="42" height="30" viewBox="0 0 42 30" fill="none">
                              <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M42.2512 0C24.5511 4.20263 9.49137 15.2238 0 30.1354H42.2512V0Z" fill="#D3C7B6" />
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
                            <h3>
                              <?php echo get_sub_field('vehicle_name'); ?>
                            </h3>
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
                    <h3>2024 Motorcycle Highlights</h3>
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
                <?php while (have_rows('slider_bottom')):
                  the_row(); ?>
                  <li class="splide__slide">
                    <div class="car_card">
                      <div class="car_card-flex">
                        <div class="car_card-image">
                          <div class="car_card-thumb">
                            <img src="<?php echo get_sub_field('image_vehicle')['url'] ?>" alt="<?php echo get_sub_field('vehicle_name'); ?>" width="<?php echo get_sub_field('image_vehicle')['width'] ?>" height="<?php echo get_sub_field('image_vehicle')['height'] ?>" loading="lazy" title="<?php echo get_sub_field('vehicle_name'); ?>">

                            <?php if (!empty(get_sub_field('link_vehicle'))): ?>
                              <div class="permalink">
                                <a href="<?php echo get_sub_field('link_vehicle'); ?>">View</a>
                              </div>
                            <?php endif; ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="42" height="30" viewBox="0 0 42 30" fill="none">
                              <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M42.2512 0C24.5511 4.20263 9.49137 15.2238 0 30.1354H42.2512V0Z" fill="#D3C7B6" />
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
                            <h3>
                              <?php echo get_sub_field('vehicle_name'); ?>
                            </h3>
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