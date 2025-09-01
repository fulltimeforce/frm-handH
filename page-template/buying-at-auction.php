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
  <div class="buying_at_auction_page-container">
    <?php
    $title   = get_field('how_to_buy_title');
    $content = get_field('how_to_buy_content');
    ?>
    <?php if ($title || $content): ?>
      <section class="how-to-buy">
        <h2><?php echo esc_html($title); ?></h2>
        <?php if ($content): ?>
          <div class="how-to-buy-content">
            <?php echo wp_kses_post($content); ?>
          </div>
        <?php endif; ?>
        <?php if (have_rows('how_to_buy_links')): ?>
          <div class="cta_links">
            <?php while (have_rows('how_to_buy_links')): the_row();
              $link = get_sub_field('how_to_buy_link');
              if ($link): ?>
                <a href="<?php echo esc_url($link['url']); ?>"
                  <?php if ($link['target']) echo 'target="' . esc_attr($link['target']) . '"'; ?>>
                  <?php echo esc_html($link['title']); ?>
                </a>
              <?php endif; ?>
            <?php endwhile; ?>
          </div>
        <?php endif; ?>

      </section>
    <?php endif; ?>
  </div>
</div>

<?php if (have_rows('auction_tabs')): ?>
  <section class="bid_online buy_rows" data-state="1">
    <div class="opportunities-buttons w-100">
      <?php while (have_rows('auction_tabs')): the_row(); ?>
        <button class="buy_button <?php echo get_row_index() == 1 ? 'active' : ''; ?>" data-id="<?php echo get_row_index(); ?>">
          <?php echo get_sub_field('tab_title') ?>
        </button>
      <?php endwhile; ?>
    </div>
    <div class="w-100">
      <?php while (have_rows('auction_tabs')): the_row(); ?>
        <div class="bid_online-container">
          <div class="bid_online-row">
            <div class="bid_online-image">
              <?php $img = get_sub_field('tab_image'); ?>
              <?php if ($img): ?>
                <img src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr($img['alt']); ?>">
              <?php endif; ?>
            </div>
            <div class="bid_online-content">
              <div class="w-100">
                <h3><?php echo get_sub_field('tab_heading'); ?></h3>
                <div class="content">
                  <?php echo get_sub_field('tab_content'); ?>
                </div>
                <?php if (have_rows('tab_cards')): ?>
                  <ul>
                    <?php while (have_rows('tab_cards')): the_row(); ?>
                      <li>
                        <h4><?php the_sub_field('card_title'); ?></h4>
                        <?php the_sub_field('card_content'); ?>
                      </li>
                    <?php endwhile; ?>
                  </ul>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </section>
<?php endif; ?>

<section class="contact_banners contact_banners-insurance">
  <div class="contact_banners-container">
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
          <h3><?php echo get_field('small_banner_title'); ?></h3>
        </div>
      </div>
      <?php
      $insurance_content = get_field('insurance_content');
      $insurance_link    = get_field('insurance_link');
      ?>
      <div class="headquarter-content">
        <div class="description">
          <p><?php echo wp_kses_post($insurance_content); ?></p>
        </div>
        <?php if ($insurance_link): ?>
          <div class="actions">
            <a href="<?php echo esc_url($insurance_link['url']); ?>"
              <?php if ($insurance_link['target']) echo 'target="' . esc_attr($insurance_link['target']) . '"'; ?>>
              <?php echo esc_html($insurance_link['title']); ?>
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php
  $selling_title     = get_field('selling_title');
  $selling_link      = get_field('selling_link');
  ?>

  <div class="contact_banners-container">
    <div class="thinking">
      <?php if ($selling_title): ?>
        <h3><?php echo esc_html($selling_title); ?></h3>
      <?php endif; ?>
      <?php if ($selling_link): ?>
        <div class="actions">
          <a href="<?php echo esc_url($selling_link['url']); ?>"
            <?php if ($selling_link['target']) echo 'target="' . esc_attr($selling_link['target']) . '"'; ?>>
            <?php echo esc_html($selling_link['title']); ?>
          </a>
        </div>
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
  document.addEventListener("DOMContentLoaded", () => {
    const section = document.querySelector("section.buy_rows");
    const buttons = document.querySelectorAll(".buy_button");

    buttons.forEach(button => {
      button.addEventListener("click", () => {
        buttons.forEach(btn => btn.classList.remove("active"));
        button.classList.add("active");
        const id = button.getAttribute("data-id");
        section.setAttribute("data-state", id);
      });
    });
  });
</script>