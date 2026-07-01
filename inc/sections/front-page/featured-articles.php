<?php


?>
<section class="featured_articles">
  <div class="container">
    <div class="featured_articles_head title_watermark">
      <div class="watermark">
        <p>Featured Artic</p>
      </div>
      <div class="breadlines">
        <p>News and Insights</p>
      </div>
      <h2>Featured Articles</h2>
    </div>
    <div class="featured_articles-body">
      <?php
      $args = array(
        'post_type' => 'post',
        'posts_per_page' => 9,
        'orderby' => 'date',
        'order' => 'DESC',
      );
      $query = new WP_Query($args);

      if ($query->have_posts() && $query->found_posts > 9):
        $count = 1;
        while ($query->have_posts()):
          $query->the_post();
          if ($count === 1) {
            $size_class = 'big';
          } elseif ($count >= 2 && $count <= 5) {
            $size_class = 'medium';
          } else {
            $size_class = 'small';
          }
      ?>
          <article class="new <?php echo $size_class; ?>" data-nro="<?php echo $count; ?>">
            <?php if ($size_class !== 'small' && has_post_thumbnail()): ?>
              <div class="new_image">
                <a href="<?php the_permalink(); ?>" title="Post" alt="Post">
                  <?php
                  $thumb_id = get_post_thumbnail_id();

                  if ($thumb_id) {

                    $title = get_the_title();

                    echo wp_get_attachment_image(
                      $thumb_id,
                      'full',
                      false,
                      [
                        'alt'      => esc_attr($title),
                        'title'    => esc_attr($title),
                        'loading'  => 'lazy',
                        'decoding' => 'async',
                      ]
                    );
                  }
                  ?>

                </a>
              </div>
            <?php endif; ?>
            <a class="new_content" href="<?php the_permalink(); ?>" title="Post" alt="Post">
              <div class="w-100">
                <span>
                  <?php echo get_the_date('d/m/y'); ?>
                </span>
                <h3>
                  <?php
                  $title = get_the_title();
                  if ($size_class === 'medium') {
                    echo mb_strimwidth($title, 0, 70, '...');
                  } elseif ($size_class === 'small') {
                    echo mb_strimwidth($title, 0, 56, '...');
                  } else {
                    echo $title;
                  }
                  ?>
                </h3>
              </div>
              <?php
              /*$short_desc = get_field('post_short_description');
              if ($short_desc) {
                  if ($size_class === 'big') {
                      echo '<p>' . mb_strimwidth($short_desc, 0, 225, '...') . '</p>';
                  } elseif ($size_class === 'medium') {
                      echo '<p>' . mb_strimwidth($short_desc, 0, 100, '...') . '</p>';
                  } elseif ($size_class === 'small') {
                      echo '<p>' . mb_strimwidth($short_desc, 0, 112, '...') . '</p>';
                  }
              }*/
              ?>
              <span href="<?php the_permalink(); ?>">Read More >></span>
            </a>
          </article>
        <?php
          $count++;
        endwhile;
        wp_reset_postdata();
      else:
        ?>
        <article class="new big" data-nro="1">
          <div class="new_image">
            <img src="<?php echo IMG; ?>/new1.png" alt="Demo" loading="lazy" decoding="async">
          </div>
          <div class="new_content">
            <span>22/09/2018</span>
            <h3>Actor, Sir Michael Caine’s first car, heads to auction...</h3>
            <a href="#">Read More >></a>
          </div>
        </article>

        <?php for ($i = 2; $i < 6; $i++): ?>
          <article class="new medium" data-nro="<?php echo $i; ?>">
            <div class="new_image">
              <img src="<?php echo IMG; ?>/new1.png" alt="Demo" loading="lazy" decoding="async">
            </div>
            <div class="new_content">
              <span>22/09/2018</span>
              <h3>Eric Clapton's 2004 Ferrari 612 Scaglietti F1</h3>
              <a href="#">Read More >></a>
            </div>
          </article>
        <?php endfor; ?>

        <?php for ($i = 6; $i < 10; $i++): ?>
          <article class="new small" data-nro="<?php echo $i; ?>">
            <div class="new_content">
              <span>22/09/2018</span>
              <h3>Eric Clapton's 2004 Ferrari 612 Scaglietti F1</h3>
              <a href="#">Read More >></a>
            </div>
          </article>
        <?php endfor; ?>
      <?php endif; ?>
    </div>
  </div>
  <a href="<?php echo esc_url(get_permalink(120)); ?>" class="permalink" alt="View All Articles" title="View All Articles">
    View All Articles
    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
      <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
    </svg>
  </a>
  </div>
</section>