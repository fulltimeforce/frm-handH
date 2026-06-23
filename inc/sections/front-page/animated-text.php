<section class="animated_text" style="background-image: url('<?php echo esc_url(get_field('animated_text_bg')); ?>');">
  <div class="animated_text-overlay"></div>
  <div class="animated_text-container">
    <?php if (get_field('animated_text')): ?>
      <h2 class="animated_text-item">
        <?php echo get_field('animated_text'); ?>
      </h2>
    <?php endif; ?>
  </div>
</section>