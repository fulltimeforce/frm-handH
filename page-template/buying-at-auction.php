<?php
/*
  Template name: buying at auction
*/

get_header();

$bg_image = get_field('buying_at_auction_hero_image');
$subtitle = get_field('buying_at_auction_hero_subtitle');
$text = get_field('buying_at_auction_hero_text');
$button = get_field('buying_at_auction_hero_button');

get_banner('Homepage / Classic Auctions / Buying at auction', esc_url($bg_image), 'Buying at auction');
?>

<div class="buying_at_auction_page">
  <div class="container">
    <?php 
      $title   = get_field('how_to_buy_title');
      $content = get_field('how_to_buy_content');
    ?>
    <?php if( $title || $content ): ?>
      <section class="how-to-buy">
        <h2><?php echo esc_html($title); ?></h2>
        <?php if($content): ?>
          <div class="how-to-buy-content">
            <?php echo wp_kses_post($content); ?>
          </div>
        <?php endif; ?>
        <?php if( have_rows('how_to_buy_links') ): ?>
          <div class="cta_links">
            <?php while( have_rows('how_to_buy_links') ): the_row(); 
              $link = get_sub_field('how_to_buy_link'); 
              if( $link ): ?>
                <a href="<?php echo esc_url( $link['url'] ); ?>" 
                  <?php if( $link['target'] ) echo 'target="'. esc_attr( $link['target'] ) .'"'; ?>>
                  <?php echo esc_html( $link['title'] ); ?>
                </a>
              <?php endif; ?>
            <?php endwhile; ?>
          </div>
        <?php endif; ?>

      </section>
    <?php endif; ?>

    <section class="auction-tabs">
      <div class="auction-tabs-container">

        <?php if (have_rows('auction_tabs')): ?>
          <!-- Tabs Nav -->
          <ul class="auction-tabs-nav">
            <?php $i = 0; while (have_rows('auction_tabs')): the_row(); ?>
              <li class="<?php echo $i === 0 ? 'active' : ''; ?>" data-tab="tab-<?php echo $i; ?>">
                <?php the_sub_field('tab_title'); ?>
              </li>
            <?php $i++; endwhile; ?>
          </ul>

          <!-- Tabs Content -->
          <div class="auction-tabs-content">
            <?php 
              if (have_rows('auction_tabs')): 
                $i = 0; 
                while (have_rows('auction_tabs')): the_row(); 
            ?>
              <div class="tab-panel <?php echo $i === 0 ? 'active' : ''; ?>" id="tab-<?php echo $i; ?>">
                <div class="auction-grid">
                  <div class="auction-image">
                    <?php $img = get_sub_field('tab_image'); ?>
                    <?php if ($img): ?>
                      <img src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr($img['alt']); ?>">
                    <?php endif; ?>
                  </div>
                  <div class="auction-info hide-scrollbar">
                    <h2><?php the_sub_field('tab_heading'); ?></h2>
                    <div class="auction-text">
                      <?php the_sub_field('tab_content'); ?>
                    </div>

                    <?php if (have_rows('tab_cards')): ?>
                      <div class="auction-cards">
                        <?php while (have_rows('tab_cards')): the_row(); ?>
                          <div class="auction-card">
                            <h3><?php the_sub_field('card_title'); ?></h3>
                            <div class="auction-card-content">
                              <?php the_sub_field('card_content'); ?>
                            </div>
                          </div>
                        <?php endwhile; ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php $i++; endwhile; endif; ?>
          </div>
        <?php endif; ?>

      </div>
    </section>

    <section class="insurance">
      <div class="insurance_banners">
        <div class="insurance_banners-container">
          <div class="headquarter">
            <div class="headquarter-image">
              <div>
                <img
                  src="<?php echo get_field('small_banner_img')['url'] ?>"
                  title="<?php echo get_field('small_banner_img')['title'] ?>"
                  alt="<?php echo get_field('small_banner_img')['alt'] ?>"
                  width="<?php echo get_field('small_banner_img')['width'] ?>"
                  height="<?php echo get_field('small_banner_img')['height'] ?>"
                  loading="lazy">
                <h2><?php echo get_field('small_banner_title'); ?></h2>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php 
        $insurance_content = get_field('insurance_content');
        $insurance_link    = get_field('insurance_link');
        $selling_title     = get_field('selling_title');
        $selling_link      = get_field('selling_link');
      ?>
      <div>
      <p><?php echo wp_kses_post($insurance_content); ?></p>
      <?php if( $insurance_link ): ?>
        <div class="cta_links">
          <a href="<?php echo esc_url( $insurance_link['url'] ); ?>" 
            <?php if( $insurance_link['target'] ) echo 'target="'. esc_attr( $insurance_link['target'] ) .'"'; ?>>
            <?php echo esc_html( $insurance_link['title'] ); ?>
          </a>
        </div>
      <?php endif; ?>
      <hr/>
      <?php if( $selling_title ): ?>
        <h3><?php echo esc_html($selling_title); ?></h3>
      <?php endif; ?>
      <?php if( $selling_link ): ?>
        <div class="cta_links">
          <a href="<?php echo esc_url( $selling_link['url'] ); ?>" 
            <?php if( $selling_link['target'] ) echo 'target="'. esc_attr( $selling_link['target'] ) .'"'; ?>>
            <?php echo esc_html( $selling_link['title'] ); ?>
          </a>
        </div>
      <?php endif; ?>
    </section>
  </div>
</div>

<?php get_template_part('inc/sections/cta'); ?>

<div class="buying_at_auction_page">
  <div class="container">
      
    <section class="upcoming" id="upcoming-auctions">
      <?php get_template_part('inc/sections/upcoming'); ?>
    </section>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const tabs = document.querySelectorAll(".auction-tabs-nav li");
  const panels = document.querySelectorAll(".tab-panel");

  tabs.forEach(tab => {
    tab.addEventListener("click", () => {
      const target = tab.getAttribute("data-tab");

      // remove active
      tabs.forEach(t => t.classList.remove("active"));
      panels.forEach(p => p.classList.remove("active"));

      // add active
      tab.classList.add("active");
      document.getElementById(target).classList.add("active");
    });
  });
});
</script>

<?php get_footer(); ?>

