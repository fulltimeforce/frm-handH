<?php
$car_dropdown_label = get_field('car_dropdown_label') ?: 'Car Makes';
$motorcycle_dropdown_label = get_field('motorcycle_dropdown_label') ?: 'Motorcycle Makes'; 
?>
<section class="header notfixed pblock160">
  <div class="container">
    <div class="submenu_dropdown" data-state="1">
      <div class="submenu_dropdown-section">
        <div class="submenu_dropdown-item relative">
          <button type="button" data-id="1">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 36 36" fill="none">
              <path
                d="M28.5 25.5H31.5C32.4 25.5 33 24.9 33 24V19.5C33 18.15 31.95 16.95 30.75 16.65C28.05 15.9 24 15 24 15C24 15 22.05 12.9 20.7 11.55C19.95 10.95 19.05 10.5 18 10.5H7.5C6.6 10.5 5.85 11.1 5.4 11.85L3.3 16.2C3.10137 16.7793 3 17.3876 3 18V24C3 24.9 3.6 25.5 4.5 25.5H7.5M28.5 25.5C28.5 27.1569 27.1569 28.5 25.5 28.5C23.8431 28.5 22.5 27.1569 22.5 25.5M28.5 25.5C28.5 23.8431 27.1569 22.5 25.5 22.5C23.8431 22.5 22.5 23.8431 22.5 25.5M7.5 25.5C7.5 27.1569 8.84315 28.5 10.5 28.5C12.1569 28.5 13.5 27.1569 13.5 25.5M7.5 25.5C7.5 23.8431 8.84315 22.5 10.5 22.5C12.1569 22.5 13.5 23.8431 13.5 25.5M13.5 25.5H22.5"
                stroke="#8C6E47" stroke-width="1.125" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <p><?= esc_html($car_dropdown_label); ?></p>
            <svg xmlns="http://www.w3.org/2000/svg" class="arrow" viewBox="0 0 36 36" fill="none">
              <path d="M18 15L22 21L14 21L18 15Z" fill="black" />
            </svg>
          </button>
          <?php render_makes_by_category('car'); ?>
        </div>
        <div class="submenu_dropdown-item relative">
          <button type="button" data-id="2">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 36 36" fill="none">
              <path
                d="M18 26.25V21L13.5 16.5L19.5 12L22.5 16.5H25.5M33 26.25C33 29.1495 30.6495 31.5 27.75 31.5C24.8505 31.5 22.5 29.1495 22.5 26.25C22.5 23.3505 24.8505 21 27.75 21C30.6495 21 33 23.3505 33 26.25ZM13.5 26.25C13.5 29.1495 11.1495 31.5 8.25 31.5C5.35051 31.5 3 29.1495 3 26.25C3 23.3505 5.35051 21 8.25 21C11.1495 21 13.5 23.3505 13.5 26.25ZM24 7.5C24 8.32843 23.3284 9 22.5 9C21.6716 9 21 8.32843 21 7.5C21 6.67157 21.6716 6 22.5 6C23.3284 6 24 6.67157 24 7.5Z"
                stroke="#8C6E47" stroke-width="1.125" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <p><?= esc_html($motorcycle_dropdown_label); ?></p>
            <svg xmlns="http://www.w3.org/2000/svg" class="arrow" viewBox="0 0 36 36" fill="none">
              <path d="M18 15L22 21L14 21L18 15Z" fill="black" />
            </svg>
          </button>
          <?php render_makes_by_category('motorcycle'); ?>
        </div>
      </div>
      <div class="submenu_dropdown-content">

        <div class="submenu_content">
          <?php
          $makes = get_field('list_34'); // ACF Post Object
          
          if ($makes) {

            // Si es múltiple, lo convertimos en array siempre
            if (!is_array($makes)) {
              $makes = [$makes];
            }

            foreach ($makes as $make) {
              $make_id = $make->ID;
              $make_title = get_the_title($make_id);
              $make_link = get_permalink($make_id);
              ?>
              <a href="<?php echo esc_url($make_link); ?>" class="submenu-link small">
                <p>
                  <?php echo esc_html($make_title); ?>
                </p>
              </a>
              <?php
            }
          }
          ?>
        </div>

      </div>
    </div>
  </div>
</section>