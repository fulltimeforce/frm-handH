<?php
/*
  Template name: pavilion
*/

get_header();

$bg_image = get_field('pavilion_hero_image');
$subtitle = get_field('pavilion_hero_subtitle');
$text = get_field('pavilion_hero_text');
$button = get_field('pavilion_hero_button');

get_banner('Homepage / Classic Auctions / Pavilion Gardens', esc_url($bg_image), 'Pavillion Gardens');
?>

<main class="pavilion_page">
  <div class="container">

    <section id="pavilionGardens" class="custom-carousel">
      <div id="pavilionSlider" class="splide">
        <div class="splide__track">
          <ul class="splide__list">
            <?php if( have_rows('pavilion_slider') ): ?>
              <?php while( have_rows('pavilion_slider') ): the_row(); 
                $image = get_sub_field('pavilion_slider_image'); 
                if($image): ?>
                  <li class="splide__slide">
                    <div class="slide-wrapper">
                      <div class="slide-image-container">
                        <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>">
                      </div>
                      <!-- Progress bar -->
                      <div class="my-progress">
                        <div class="my-progress-bar"></div>
                      </div>
                    </div>
                  </li>
                <?php endif; ?>
              <?php endwhile; ?>
            <?php endif; ?>
          </ul>
        </div>

        <div class="splide__arrows custom-arrows">
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
      </div>

      <h2>ST. JOHN’S ROAD, BUXTON, DERBYSHIRE SK17 6BE</h2>
    </section>

    <section class="auctions">
      <div class="auctions_container">
        <h2 class="auctions_container_title">Auctions combining historic charm and architectural beauty</h2>
        <div class="auctions_container_content">
          <p>The Pavilion Gardens is a beautiful historic venue which superbly showcases the Victorian splendour of Buxton. Situated in the heart of this historic spa town the Pavilion Gardens is a wonderful example of the heritage that runs through the town.  H&H Classics, Europe’s oldest continuously trading classic car & bike auction house held its first auction at The Pavilion gardens in 1993.</p>
          <p>Motorcars are displayed in the stunning Octagon Hall and undercover in our marquees prominently displayed within the grounds of this beautiful venue.</p>
          <a class="link_btn" href="/">
            <span>Learn How to Bid</span>
            <img src="<?php echo IMG; ?>/arrow.svg">
          </a>
        </div>
      </div>
    </section>

    <section class="consign">
      <h2>Consign with H&H</h2>
      <p>To consign your classic motorcar into an auction at the Pavilion Gardens please call us on 01925 210035, email sales@HandH.co.uk or fill out the form on the Value My Classic page. All our valuations for sale are complimentary and without obligation.</p>
      <a href="">https://www.handh.co.uk/consign</a>
      <p>Bidding is available live at the auction venue, online, by telephone and commission. For details please call us on 01925 210035 or send an email to sales@HandH.co.uk</p>
    </section>

    <?php get_template_part('inc/sections/cta'); ?>

    <section class="how-to-get">
      <h2>How to get to the Pavilion Gardens </h2>
      <h3>By Car from M1</h3>
      <p>Leave the M1 at junction 29 (Chesterfield) and take the A617, the A619 and then the A6020 through the Peak District.</p>
      <h3>By Car from M6</h3>
      <p>Leave the M6 at junction 19 (Knutsford) and take the A537 through Macclesfield. Sat Nav’s will take you to this entrance using the postcode SK17 6BE</p>
    </section>
      
    <section class="upcoming" id="upcoming-auctions">
      <?php get_template_part('inc/sections/upcoming'); ?>
  </section>
  </div>
</main>

<?php get_footer(); ?>

