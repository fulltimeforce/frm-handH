<?php
$why_video = get_field('whychoose_video');

if ($why_video && !empty(get_field('whychoose_poster_video'))): ?>
  <section class="banner_car">
    <img src="<?php echo get_field('whychoose_poster_video')['url'] ?>"
      title="<?php echo get_field('whychoose_poster_video')['title'] ?>"
      alt="<?php echo get_field('whychoose_poster_video')['alt'] ?>"
      width="<?php echo get_field('whychoose_poster_video')['width'] ?>"
      height="<?php echo get_field('whychoose_poster_video')['height'] ?>" loading="lazy">
  </section>
<?php endif; ?>