<?php
// Get ACF fields
$tab_car_label = get_field('smm_tab_car_label');
$tab_motorcycle_label = get_field('smm_tab_motorcycle_label');
$make_select_placeholder = get_field('smm_make_select_placeholder');
$most_searched_title = get_field('smm_most_searched_title');
$car_makes = get_field('smm_car_makes');
$motorcycle_makes = get_field('smm_motorcycle_makes');

// Defaults
$tab_car_label = $tab_car_label ?: 'CAR MAKES';
$tab_motorcycle_label = $tab_motorcycle_label ?: 'MOTORCYCLE MAKES';
$make_select_placeholder = $make_select_placeholder ?: 'Select Make';
$most_searched_title = $most_searched_title ?: 'Most Searched';
$placeholder_image = '';
?>

<section class="search-make-section pblock160" id="search-make-section">
  <div class="search-make-section__container">
    <!-- Vehicle type tabs and dropdown -->
    <div class="search-make-section__header">
      <div class="search-make-section__tabs">
        <button class="search-make-section__tab search-make-section__tab--active" data-tab="car">
          <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M28.5 25.5H31.5C32.4 25.5 33 24.9 33 24V19.5C33 18.15 31.95 16.95 30.75 16.65C28.05 15.9 24 15 24 15C24 15 22.05 12.9 20.7 11.55C19.95 10.95 19.05 10.5 18 10.5H7.5C6.6 10.5 5.85 11.1 5.4 11.85L3.3 16.2C3.10137 16.7793 3 17.3876 3 18V24C3 24.9 3.6 25.5 4.5 25.5H7.5M28.5 25.5C28.5 27.1569 27.1569 28.5 25.5 28.5C23.8431 28.5 22.5 27.1569 22.5 25.5M28.5 25.5C28.5 23.8431 27.1569 22.5 25.5 22.5C23.8431 22.5 22.5 23.8431 22.5 25.5M7.5 25.5C7.5 27.1569 8.84315 28.5 10.5 28.5C12.1569 28.5 13.5 27.1569 13.5 25.5M7.5 25.5C7.5 23.8431 8.84315 22.5 10.5 22.5C12.1569 22.5 13.5 23.8431 13.5 25.5M13.5 25.5H22.5"
              stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          <span><?php echo esc_html($tab_car_label); ?></span>
        </button>
        <button class="search-make-section__tab" data-tab="motorcycle">
          <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M27 21L25.5 16.5M4.5 13.5L13.5 16.5C13.5 15.7044 13.8161 14.9413 14.3787 14.3787C14.9413 13.8161 15.7044 13.5 16.5 13.5H19.5C20.2461 13.5004 20.9652 13.7787 21.5171 14.2807C22.0691 14.7827 22.4141 15.4723 22.485 16.215M12 25.5H16.5C16.8978 25.5 17.2794 25.342 17.5607 25.0607C17.842 24.7794 18 24.3978 18 24C18 21.6131 18.9482 19.3239 20.636 17.636C22.3239 15.9482 24.6131 15 27 15C27.3978 15 27.7794 14.842 28.0607 14.5607C28.342 14.2794 28.5 13.8978 28.5 13.5V12.375C28.1838 10.4173 27.1052 8.66448 25.5 7.5M12 25.5C12 27.9853 9.98528 30 7.5 30C5.01472 30 3 27.9853 3 25.5C3 23.7585 3.98922 22.2481 5.4364 21.5C6.05452 21.1805 6.75618 21 7.5 21C9.98528 21 12 23.0147 12 25.5ZM33 25.5C33 27.9853 30.9853 30 28.5 30C26.0147 30 24 27.9853 24 25.5C24 23.0147 26.0147 21 28.5 21C30.9853 21 33 23.0147 33 25.5Z"
              stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          <span><?php echo esc_html($tab_motorcycle_label); ?></span>
        </button>
      </div>
      <!--  -->
      <div class="search-make-section__select-wrapper">
        <!-- Car Dropdown -->
        <div class="submenu_dropdown-item relative search-make-section__dropdown" data-dropdown="car">
          <button type="button" data-id="1" class="search-make-section__select">
            <p><?php echo esc_html($make_select_placeholder); ?></p>
            <svg class="search-make-section__select-arrow" width="14" height="9" viewBox="0 0 14 9" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path d="M12.7109 0.707031L6.71094 6.70703L0.710938 0.707031" stroke="#8C6E47" stroke-width="2" />
            </svg>
          </button>
          <?php render_makes_by_category('car'); ?>
        </div>

        <!-- Motorcycle Dropdown -->
        <div class="submenu_dropdown-item relative search-make-section__dropdown" data-dropdown="motorcycle"
          style="display:none;">
          <button type="button" data-id="2" class="search-make-section__select">
            <p><?php echo esc_html($make_select_placeholder); ?></p>
            <svg class="search-make-section__select-arrow" width="14" height="9" viewBox="0 0 14 9" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path d="M12.7109 0.707031L6.71094 6.70703L0.710938 0.707031" stroke="#8C6E47" stroke-width="2" />
            </svg>
          </button>
          <?php render_makes_by_category('motorcycle'); ?>
        </div>
      </div>
    </div>

    <!-- Divider -->
    <div class="search-make-section__divider"></div>

    <!-- Most Searched Section -->
    <div class="search-make-section__content">
      <h2 class="search-make-section__title"><?php echo esc_html($most_searched_title); ?></h2>

      <!-- Car Makes Grid -->
      <div class="search-make-section__grid search-make-section__grid--car" data-grid="car">
        <?php
        if ($car_makes):
          foreach ($car_makes as $make):
            $thumbnail_id = get_post_thumbnail_id($make->ID);
            $image_src = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'medium') : $placeholder_image;
            $make_title = get_the_title($make->ID);
            $make_url = get_permalink($make->ID);
            ?>
            <a href="<?php echo esc_url($make_url); ?>" class="search-make-section__card">
              <img src="<?php echo esc_url($image_src); ?>" alt="<?php echo esc_attr($make_title); ?>"
                class="search-make-section__card-bg" loading="lazy">
              <div class="search-make-section__card-content">
                <p class="search-make-section__card-title"><?php echo esc_html($make_title); ?></p>
              </div>
            </a>
          <?php endforeach;
        endif; ?>
      </div>

      <!-- Motorcycle Makes Grid -->
      <div class="search-make-section__grid search-make-section__grid--motorcycle" data-grid="motorcycle"
        style="display:none;">
        <?php
        if ($motorcycle_makes):
          foreach ($motorcycle_makes as $make):
            $thumbnail_id = get_post_thumbnail_id($make->ID);
            $image_src = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'medium') : $placeholder_image;
            $make_title = get_the_title($make->ID);
            $make_url = get_permalink($make->ID);
            ?>
            <a href="<?php echo esc_url($make_url); ?>" class="search-make-section__card">
              <img src="<?php echo esc_url($image_src); ?>" alt="<?php echo esc_attr($make_title); ?>"
                class="search-make-section__card-bg" loading="lazy">
              <div class="search-make-section__card-content">
                <p class="search-make-section__card-title"><?php echo esc_html($make_title); ?></p>
              </div>
            </a>
          <?php endforeach;
        endif; ?>
      </div>

    </div>
  </div>
</section>


<script>
  (function () {
    const tabs = document.querySelectorAll('.search-make-section__tab');
    const grids = document.querySelectorAll('[data-grid]');
    const dropdowns = document.querySelectorAll('[data-dropdown]');

    tabs.forEach(tab => {
      tab.addEventListener('click', function () {
        // Remove active class from all tabs
        tabs.forEach(t => t.classList.remove('search-make-section__tab--active'));
        // Add active class to clicked tab
        this.classList.add('search-make-section__tab--active');

        // Get tab type (car or motorcycle)
        const tabType = this.getAttribute('data-tab');

        // Show/hide grids
        grids.forEach(grid => {
          if (grid.getAttribute('data-grid') === tabType) {
            grid.style.display = 'grid';
          } else {
            grid.style.display = 'none';
          }
        });

        // Show/hide dropdowns
        dropdowns.forEach(dropdown => {
          if (dropdown.getAttribute('data-dropdown') === tabType) {
            dropdown.style.display = 'block';
          } else {
            dropdown.style.display = 'none';
          }
        });
      });
    });
  })();
</script>

<style>
  .search-make-section {
    width: 100%;
  }

  .search-make-section__container {
    max-width: 1428px;
    margin-inline: auto;
    width: 100%;
    position: relative;
    left: 56px;
  }

  .search-make-section__header {
    display: flex;
    gap: 18px;
    align-items: flex-start;
    width: 100%;
  }

  .search-make-section__tabs {
    display: flex;
    width: 704px;
    border: 1px solid #8c6e47;
    background: white;
    padding: 4px;
    gap: 4px;
  }

  .search-make-section__tab {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    flex: 1;
    padding: 8px 20px;
    background: #f5f2ee;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }

  .search-make-section__tab:not(.search-make-section__tab--active):hover {
    background-color: #D3C7B6;
  }

  .search-make-section__tab svg {
    width: 36px;
    height: 36px;
    aspect-ratio: 1/1;
  }

  .search-make-section__tab path {
    stroke: #8c6e47;
  }

  .search-make-section__tab--active path {
    stroke: white;
  }

  .search-make-section__tab--active {
    background: #8c6e47;
    color: white;
  }

  .search-make-section__tab-icon {
    width: 36px;
    height: 36px;
    flex-shrink: 0;
  }

  .search-make-section__tab span {
    font-family: 'GothamMedium';
    font-size: 16px;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    line-height: 1.15;
    margin-top: 3px;
  }

  .search-make-section__tab:not(.search-make-section__tab--active) span {
    font-family: 'GothamLight';
    opacity: 0.8;
    color: black;
  }

  .search-make-section__select-wrapper {
    position: relative;
    flex: 1;
    background: white;
    display: flex;
    align-items: center;
  }

  .search-make-section__select-wrapper .submenu_dropdown-item {
    z-index: 5;
    position: relative;
    flex: 1;
  }

  .search-make-section__dropdown {
    width: 100%;
  }

  .search-make-section__select-wrapper .submenu_dropdown-listing {
    list-style: none;
  }

  .search-make-section__select {
    width: 100%;
    padding: 20px 21px;
    border: none;
    background: transparent;
    font-family: GothamBook;
    font-size: 17px;
    letter-spacing: 1.7px;
    color: black;
    opacity: 0.8;
    appearance: none;
    cursor: pointer;
    padding-right: 50px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border: 1px solid #8c6e47;
  }

  .search-make-section__select-arrow {
    position: absolute;
    right: 21px;
    pointer-events: none;
    width: 14px;
    height: 9px;
  }

  .search-make-section__divider {
    width: 100%;
    height: 1px;
    background: #8c6e47;
    margin: 64px 0;
  }

  .search-make-section__content {
    display: flex;
    flex-direction: column;
    gap: 40px;
  }

  .search-make-section__title {
    font-family: 'CormorantGaramond';
    font-weight: 300;
    font-size: 60px;
    line-height: 1;
    letter-spacing: -1.8px;
    text-align: center;
    margin: 0;
    color: black;
  }

  .search-make-section__grid {
    display: grid;
    grid-template-columns: repeat(5, 268px);
    gap: 22px;
  }

  .search-make-section__card {
    position: relative;
    width: 268px;
    height: 134px;
    border-radius: 4px;
    overflow: hidden;
    padding: 8px;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s ease;
  }

  .search-make-section__card-bg {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    opacity: 0.2;
    border-radius: 4px;
  }

  .search-make-section__card::before {
    content: '';
    position: absolute;
    inset: 0;
    background-color: #181d24;
    border-radius: 4px;
    z-index: -1;
  }

  .search-make-section__card-content {
    position: relative;
    width: 100%;
    height: 100%;
    border: 2px solid white;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 18px;
    overflow: hidden;
  }

  .search-make-section__card-title {
    font-family: GoudyTitlingSemiBold;
    font-size: 20px;
    line-height: 1.15;
    color: white;
    text-align: center;
    margin: 0;
  }

  .search-make-section__select-wrapper .submenu-link.small {
    align-items: center;
    border: .0520833333vw solid rgba(140, 110, 71, 0);
    display: flex;
    flex-wrap: wrap;
    gap: .8333333333vw;
    justify-content: flex-start;
    padding-inline: .8333333333vw;
    max-width: 100%;
    padding-block: 1.0416666667vw;
    flex: 1;
    background: transparent;
    position: relative;
    transition: all .3s ease;
  }

  .search-make-section__select-wrapper .submenu-link:is(:hover, :focus) {
    background: #f5f5f5;
    border-color: #8c6e47;
  }

  .search-make-section__select-wrapper .submenu-link p {
    color: #000;
    font-family: GothamLight;
    font-weight: 300;
  }

  /* Relativo al viewport */
  /* @media (min-width: 1420px) { */
  @media (min-width: 1201px) {
    .search-make-section {}

    .search-make-section__container {
      max-width: calc(1428 / 1920 * 100vw);
      left: calc(56 / 1920 * 100vw);
    }

    .search-make-section__header {
      gap: calc(18 / 1920 * 100vw);
    }

    .search-make-section__tabs {
      width: calc(704 / 1920 * 100vw);
      padding: calc(4 / 1920 * 100vw);
      gap: calc(4 / 1920 * 100vw);
    }

    .search-make-section__tab {
      gap: calc(12 / 1920 * 100vw);
      padding: calc(8 / 1920 * 100vw) calc(20 / 1920 * 100vw);
    }

    .search-make-section__tab svg {
      width: calc(36 / 1920 * 100vw);
      height: calc(36 / 1920 * 100vw);
      aspect-ratio: 1/1;
    }

    .search-make-section__tab-icon {
      width: calc(36 / 1920 * 100vw);
      height: calc(36 / 1920 * 100vw);
    }

    .search-make-section__tab span {
      font-size: calc(16 / 1920 * 100vw);
      letter-spacing: calc(0.8 / 1920 * 100vw);
      margin-top: calc(3 / 1920 * 100vw);
    }

    .search-make-section__tab:not(.search-make-section__tab--active) span {}

    .search-make-section__select {
      padding: calc(20 / 1920 * 100vw) calc(21 / 1920 * 100vw);
      font-size: calc(17 / 1920 * 100vw);
      letter-spacing: calc(1.7 / 1920 * 100vw);
      padding-right: calc(50 / 1920 * 100vw);
      border: calc(1 / 1920 * 100vw) solid #8c6e47;
    }

    .search-make-section__select-arrow {
      right: calc(21 / 1920 * 100vw);
      width: calc(14 / 1920 * 100vw);
      height: calc(9 / 1920 * 100vw);
    }

    .search-make-section__divider {
      margin: calc(64 / 1920 * 100vw) 0;
    }

    .search-make-section__content {
      gap: calc(40 / 1920 * 100vw);
    }

    .search-make-section__title {
      font-size: calc(60 / 1920 * 100vw);
      letter-spacing: calc(-1.8 / 1920 * 100vw);
    }

    .search-make-section__grid {
      grid-template-columns: repeat(5, calc(268 / 1920 * 100vw));
      gap: calc(22 / 1920 * 100vw);
    }

    .search-make-section__card {
      width: calc(268 / 1920 * 100vw);
      height: calc(134 / 1920 * 100vw);
      border-radius: calc(4 / 1920 * 100vw);
      padding: calc(8 / 1920 * 100vw);
    }

    .search-make-section__card-bg {
      border-radius: calc(4 / 1920 * 100vw);
    }

    .search-make-section__card::before {
      border-radius: calc(4 / 1920 * 100vw);
    }

    .search-make-section__card-content {
      padding: calc(18 / 1920 * 100vw);
    }

    .search-make-section__card-title {
      font-size: calc(20 / 1920 * 100vw);
    }

    /* HERE */
    .search-make-section__select-wrapper .submenu_dropdown-listing {
      max-height: 20.833vw;
      padding: 1.0416666667vw;
    }

    .search-make-section__select-wrapper .submenu-link.small {
      gap: .8333333333vw;
      padding-inline: .8333333333vw;
      padding-block: 1.0416666667vw;
      border: .0520833333vw solid rgba(140, 110, 71, 0);
    }

    .search-make-section__select-wrapper .submenu-link.small {
      font-size: calc(16 / 1920 * 100vw);
    }

    .search-make-section__select-wrapper .submenu-link:is(:hover, :focus) {
      border-color: #8c6e47;
    }
  }

  /* Responsive */
  /* @media (max-width: 1419px) { */
  @media (max-width: 1200px) {
    .search-make-section {}

    .search-make-section__header {
      flex-direction: column;
      align-items: center;
    }

    .search-make-section__container {
      position: static;
      padding-left: 11%;
      padding-right: 4%;
    }

    .search-make-section__tabs {
      width: 100%;
      max-width: 600px;
    }

    .search-make-section__select-wrapper {
      width: 100%;
      max-width: 600px;
    }

    .search-make-section__grid {
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      justify-items: center;
    }
  }

  @media (max-width: 1024px) {
    .search-make-section__container {
      padding-left: 4%;
    }
  }

  @media (max-width: 768px) {
    .search-make-section__tabs {
      flex-direction: column;
    }

    .search-make-section {}

    .search-make-section__tab span {
      font-size: 14px;
    }

    .search-make-section__tab-icon {
      width: 28px;
      height: 28px;
    }

    .search-make-section__title {
      font-size: 40px;
    }

    .search-make-section__grid {
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }

    .search-make-section__card {
      width: 100%;
      max-width: 268px;
    }

    .search-make-section__tab svg {
      width: 25px;
      height: 25px;
    }

    .search-make-section__select {
      padding: 10px 20px;
    }

    .search-make-section__select-wrapper .submenu-link.small {
      padding: 13px 10px;
    }

    .search-make-section__divider {
      margin: 48px 0;
    }


  }
</style>