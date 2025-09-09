<?php
/*
    Template name: brand
*/

get_header();

$title = get_field('brand_title');
$bg_image = get_field('brand_banner_image');

get_centered_banner(esc_url($bg_image), esc_html($title));

?>

<section class="discover discover_brands">
  <div class="container">
    <div class="discover_head title_watermark">
      <div class="watermark">
        <p>Vehicles For Sale</p>
      </div>
      <div class="breadlines">
        <p>Explore listings</p>
      </div>
      <h2>Vehicles For Sale</h2>

      <div class="discover_tabs_container">
        <div class="discover_tabs">
          <button class="discover_tab active" data-target="auction">Auction</button>
          <button class="discover_tab" data-target="private">Private Sales</button>
        </div>
        <div class="splide__arrows relative">
          <button class="splide__arrow splide__arrow--prev">
            <svg xmlns="http://www.w3.org/2000/svg" width="50" height="27" viewBox="0 0 50 26" fill="none">
              <path d="M0 13H48M48 13L36 1M48 13L36 25" stroke="#8C6E47" stroke-width="2" />
            </svg>
          </button>
          <button class="splide__arrow splide__arrow--next">
            <svg xmlns="http://www.w3.org/2000/svg" width="50" height="27" viewBox="0 0 50 26" fill="none">
              <path d="M0 13H48M48 13L36 1M48 13L36 25" stroke="#8C6E47" stroke-width="2" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <div class="discover_body discover_tab-content active" id="auction">
      <div id="splide-auction" class="splide">
        <div class="splide__track">
          <ul class="splide__list">
            <?php for ($i = 0; $i < 5; $i++): ?>
              <li class="splide__slide vehicle_card">
                <div class="vehicle_card-image">
                  <img src="<?php echo IMG; ?>/car.png" alt="Car">
                </div>
                <div class="vehicle_card-info">
                  <h3>1971 Austin Mini 850</h3>
                  <h4><span>Estimated at</span> £6,000 - £8,000</h4>
                  <ul>
                    <li><b>Registration No:</b> WUJ 67K</li>
                    <li><b>Chassis No:</b> XAD15498561</li>
                    <li><b>MOT:</b> Exempt</li>
                  </ul>
                </div>
              </li>
            <?php endfor; ?>
          </ul>
        </div>
      </div>
    </div>

    <div class="discover_body discover_tab-content" id="private">
      <div id="splide-private" class="splide">
        <div class="splide__track">
          <ul class="splide__list">
            <?php for ($i = 0; $i < 5; $i++): ?>
              <li class="splide__slide vehicle_card">
                <div class="vehicle_card-image">
                  <img src="<?php echo IMG; ?>/car2.png" alt="Car">
                </div>
                <div class="vehicle_card-info">
                  <h3>1965 Mini Cooper MK1</h3>
                  <h4>£55,000</h4>
                  <ul>
                    <li><b>Registration No:</b> GBP 77C</li>
                    <li><b>Chassis No:</b> XAD15478716</li>
                    <li><b>MOT:</b> Exempt</li>
                  </ul>
                </div>
              </li>
            <?php endfor; ?>
          </ul>
        </div>
      </div>
    </div>

  </div>
</section>

<?php if (have_rows('models')): ?>
  <section id="brandsCar" class="model-slider">
    <div class="container">

      <div id="splide-models" class="splide">
        <div class="model-slider__title_container">
          <h2 class="model-slider__title">Select Model</h2>
          <div class="splide__arrows relative">
            <button class="splide__arrow splide__arrow--prev">
              <svg xmlns="http://www.w3.org/2000/svg" width="50" height="27" viewBox="0 0 50 26" fill="none">
                <path d="M0 13H48M48 13L36 1M48 13L36 25" stroke="#8C6E47" stroke-width="2" />
              </svg>
            </button>
            <button class="splide__arrow splide__arrow--next">
              <svg xmlns="http://www.w3.org/2000/svg" width="50" height="27" viewBox="0 0 50 26" fill="none">
                <path d="M0 13H48M48 13L36 1M48 13L36 25" stroke="#8C6E47" stroke-width="2" />
              </svg>
            </button>
          </div>
        </div>
        <div class="splide__track">
          <ul class="splide__list">
            <?php $i = 0; ?>
            <?php while (have_rows('models')): the_row(); ?>
              <?php
              $model_name  = get_sub_field('model_name');
              $model_image = get_sub_field('model_image');
              ?>
              <li class="splide__slide model-card <?php echo $i === 0 ? 'active' : ''; ?>" data-target="model-<?php echo $i; ?>">
                <div class="model-card__image">
                  <?php if ($model_image): ?>
                    <img src="<?php echo esc_url($model_image['url']); ?>" alt="<?php echo esc_attr($model_name); ?>">
                  <?php endif; ?>
                </div>
                <div class="model-card__overlay">
                  <h3 class="model-card__title"><?php echo esc_html($model_name); ?></h3>
                </div>
              </li>
              <?php $i++; ?>
            <?php endwhile; ?>
          </ul>
        </div>
      </div>
    </div>
  </section>


  <section class="models-content brand-banner-section">
    <div class="container">
      <?php if (have_rows('models')): $j = 0; ?>
        <?php while (have_rows('models')): the_row(); ?>
          <?php
          $model_name = get_sub_field('model_name');
          $intro_title = get_sub_field('intro_title');
          $intro_desc = get_sub_field('intro_desc');
          $small_banner_img = get_sub_field('model_image');
          ?>
          <div class="model-content-item <?php echo $j === 0 ? 'active' : ''; ?>" id="model-<?php echo $j; ?>">
            <?php get_centered_banner(esc_url($small_banner_img['url']), esc_html($model_name), 'small') ?>
            <div class="model-content-item-intro">
              <h2><?php the_sub_field('intro_title'); ?></h2>
              <p><?php the_sub_field('intro_desc'); ?></p>
            </div>

            <div class="model-cards">
              <?php if (have_rows('cards')): ?>
                <?php while (have_rows('cards')): the_row();
                  $card_title = get_sub_field('card_title');
                  $card_text = get_sub_field('card_text');
                  $card_image = get_sub_field('card_image');
                  $full = get_sub_field('is_full_width'); ?>
                  <article class="mini-card <?php echo $full ? 'mini-card--full' : ''; ?>">
                    <?php if ($card_image): ?>
                      <div class="mini-card__image">
                        <img src="<?php echo esc_url($card_image['url']); ?>" alt="<?php echo esc_attr($card_title); ?>">
                      </div>
                    <?php endif; ?>
                    <div class="mini-card__content">
                      <h3><?php echo esc_html($card_title); ?></h3>
                      <p><?php echo esc_html($card_text); ?></p>
                    </div>
                  </article>
                <?php endwhile; ?>
              <?php endif; ?>
            </div>
            <?php
            $end_title = get_sub_field('ending_title');
            $end_subtext = get_sub_field('ending_subtext');
            $end_desc = get_sub_field('ending_desc');
            $end_btn_text = get_sub_field('ending_button_text');
            $end_btn_url = get_sub_field('ending_button_url');
            ?>

            <?php if ($end_title || $end_desc): ?>
              <div class="model-ending">
                <div class="model-ending-container">
                  <div class="container">

                    <div class="model-ending__top">
                      <?php if ($end_title): ?>
                        <h3 class="model-ending__title"><?php echo esc_html($end_title); ?></h3>
                      <?php endif; ?>
                      <?php if ($end_subtext): ?>
                        <p class="model-ending__subtext"><?php echo esc_html($end_subtext); ?></p>
                      <?php endif; ?>
                    </div>

                    <hr>

                    <?php if ($end_desc): ?>
                      <p class="model-ending__desc"><?php echo esc_html($end_desc); ?></p>
                    <?php endif; ?>

                    <?php if ($end_btn_text && $end_btn_url): ?>
                      <a href="<?php echo esc_url($end_btn_url); ?>" class="model-ending__btn">
                        <?php echo esc_html($end_btn_text); ?>
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endif; ?>
          </div>
        <?php $j++;
        endwhile; ?>
      <?php endif; ?>
    </div>
  </section>
<?php endif; ?>

<section class="history-tabs">
  <div class="container">
    <div class="history-tabs__titles">
      <?php if (have_rows('history_tabs')): $i = 0; ?>
        <?php while (have_rows('history_tabs')): the_row();
          $title = get_sub_field('tab_title'); ?>
          <button class="history-tab-title <?php echo $i === 0 ? 'active' : ''; ?>" data-target="history-tab-<?php echo $i; ?>">
            <?php echo esc_html($title); ?>
          </button>
        <?php $i++;
        endwhile; ?>
      <?php endif; ?>
    </div>

    <div class="history-tabs__content">
      <?php if (have_rows('history_tabs')): $j = 0; ?>
        <?php while (have_rows('history_tabs')): the_row();
          $content = get_sub_field('tab_content'); ?>
          <div class="history-tab-content <?php echo $j === 0 ? 'active' : ''; ?>" id="history-tab-<?php echo $j; ?>">
            <p><?php echo esc_html($content); ?></p>
          </div>
        <?php $j++;
        endwhile; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="history-feature">
  <div class="container history-feature__grid">
    <div class="history-feature__image">
      <?php $image = get_field('section_image'); ?>
      <?php if ($image): ?>
        <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>">
      <?php endif; ?>
      <h3 class="history-feature__title"><?php the_field('section_title'); ?></h3>
    </div>
    <div class="history-feature__content">
      <p><?php the_field('section_desc'); ?></p>
      <?php if (get_field('button_text') && get_field('button_url')): ?>
        <a href="<?php the_field('button_url'); ?>" class="history-feature__btn">
          <?php the_field('button_text'); ?>
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php get_template_part('inc/sections/cta'); ?>

<section class="upcoming pb160" id="upcoming-auctions">
  <?php get_template_part('inc/sections/upcoming'); ?>
</section>

<?php get_footer(); ?>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const tabs = document.querySelectorAll(".discover_tab");
    const contents = document.querySelectorAll(".discover_tab-content");

    tabs.forEach(tab => {
      tab.addEventListener("click", () => {
        tabs.forEach(t => t.classList.remove("active"));
        contents.forEach(c => c.classList.remove("active"));

        tab.classList.add("active");
        document.getElementById(tab.dataset.target).classList.add("active");
      });
    });
  });

  document.addEventListener("DOMContentLoaded", function() {
    const modelCards = document.querySelectorAll('.model-card');
    const modelContents = document.querySelectorAll('.model-content-item');

    modelCards.forEach(card => {
      card.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');

        modelContents.forEach(content => content.classList.remove('active'));
        modelCards.forEach(c => c.classList.remove('active'));

        this.classList.add('active');
        document.getElementById(targetId).classList.add('active');
      });
    });
  });

  document.addEventListener("DOMContentLoaded", function() {
    const tabButtons = document.querySelectorAll(".history-tab-title");
    const tabContents = document.querySelectorAll(".history-tab-content");
    const contentBox = document.querySelector(".history-tabs__content");

    function moveArrowTo(tab) {
      const rect = tab.getBoundingClientRect();
      const containerRect = contentBox.getBoundingClientRect();
      const center = rect.left + rect.width / 2 - containerRect.left;
      contentBox.style.setProperty("--arrow-left", `${center}px`);
    }

    tabButtons.forEach((btn, index) => {
      btn.addEventListener("click", function() {
        const target = this.getAttribute("data-target");

        tabButtons.forEach(b => b.classList.remove("active"));
        this.classList.add("active");

        tabContents.forEach(c => c.classList.remove("active"));
        document.getElementById(target).classList.add("active");

        moveArrowTo(this);
      });

      if (index === 0 && btn.classList.contains("active")) {
        moveArrowTo(btn);
      }
    });

    window.addEventListener("resize", () => {
      const activeTab = document.querySelector(".history-tab-title.active");
      if (activeTab) moveArrowTo(activeTab);
    });
  });
</script>