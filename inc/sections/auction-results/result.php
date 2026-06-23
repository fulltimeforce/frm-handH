<?php
$past_auctions_label = get_field('past_auctions_label'); // Text
$still_available_label = get_field('still_available_label'); // Text
$still_available_url = get_field('still_available_page'); // Page Link
?>
<section class="auction_result-tab">
  <div class="container" style="border: none;">
    <div>
      <?php if (!empty($past_auctions_label)): ?>
        <a class="active" alt="<?= esc_attr($past_auctions_label); ?>">
          <?= esc_html($past_auctions_label); ?>
        </a>
      <?php endif; ?>
      <?php if (!empty($still_available_label)): ?>
        <a href="<?= esc_url($still_available_url); ?>" alt="<?= esc_attr($still_available_label); ?>">
          <?= esc_html($still_available_label); ?>
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>