<?php
$cta_background_image = get_field('cta_background_image'); // 
$cta_heading = get_field('cta_heading'); // Text Area
$cta_first_link = get_field('cta_first_link'); // Group [title(text), link(Page Link)]
$cta_second_link = get_field('cta_second_link'); // Group [title(text), link(Page Link)]
?>

<section class="cta">
  <div class="cta_bg">
    <?php if (!empty($cta_background_image) && !empty($cta_background_image["url"])): ?>
      <img src="<?= esc_url($cta_background_image["url"]); ?>" alt="Banner">
    <?php endif; ?>
  </div>
  <div class="container">
    <div class="cta_content">
      <?php if (!empty($cta_heading)): ?>
        <h2><?= $cta_heading ?></h2>
      <?php endif; ?>
      <div class="cta_links">
        <?php if (!empty($cta_first_link)): ?>
          <a href="<?= $cta_first_link['link'] ?: '#0' ?>" alt="<?= $cta_first_link['title'] ?>">
            <?= $cta_first_link['title'] ?>
          </a>
        <?php endif; ?>

        <?php if (!empty($cta_second_link)): ?>
          <a href="<?= $cta_second_link['link'] ?: '#0' ?>" alt="<?= $cta_second_link['title'] ?>">
            <?= $cta_second_link['title'] ?>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>