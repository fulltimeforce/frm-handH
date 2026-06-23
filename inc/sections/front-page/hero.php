<?php
$hero_bg = get_field('hero_background_video');
$hero_title = get_field('hero_title');
$hero_subtitle = get_field('hero_subtitle');
$hero_button1 = get_field('hero_button_1');
$hero_button2 = get_field('hero_button_2');
?>
<main class="hero relative">
  <div class="video__bg">
    <video autoplay playsinline muted loop>
      <source src="<?php echo $hero_bg; ?>">
    </video>
  </div>
  <div class="container">
    <div class="hero_content">
      <?php if ($hero_subtitle): ?>
        <div class="breadlines">
          <p><?php echo $hero_subtitle; ?></p>
        </div>
      <?php endif; ?>
      <?php if ($hero_title): ?>
        <?php echo $hero_title; ?>
      <?php endif; ?>
      <div class="hero_actions">
        <?php if ($hero_button1): ?>
          <a href="<?php echo esc_url($hero_button1['url']); ?>" target="<?php echo $hero_button1['target']; ?>">
          	<?php echo $hero_button1['title']; ?>
          </a>
        <?php endif; ?>

        <?php if ($hero_button2): ?>
          <a href="<?php echo esc_url($hero_button2); ?>">
            Upcoming Auctions
          </a>
        <?php endif; ?>
      </div>
      <a class="hero_scroll" href="#upcoming-auctions">
        <p>SCROLL</p>
        <div></div>
        <p>DOWN</p>
      </a>
    </div>
  </div>
</main>