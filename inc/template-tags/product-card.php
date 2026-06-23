<?php
if (!defined('ABSPATH')) {
  exit;
}

function get_card_product($product_id)
{
  if (!$product_id) {
    return;
  }

  $product = wc_get_product($product_id);

  if (!$product) {
    return;
  }

  $image_src = IMG . '/placeholder.png';

  if (has_post_thumbnail($product_id)) {
    $thumb_id = get_post_thumbnail_id($product_id);
    $thumb_url = wp_get_attachment_image_src($thumb_id, 'woocommerce_thumbnail');
    $image_src = $thumb_url[0];
  }
  ?>
  <a href="<?php echo esc_url(get_permalink($product_id)); ?>" class="shop_product"
    data-id="<?php echo esc_attr($product_id); ?>">
    <div class="shop_product-image">
      <img src="<?php echo esc_url($image_src); ?>" alt="<?php echo esc_html(get_the_title($product_id)); ?>">
    </div>
    <div class="shop_product-info">
      <h3><?php echo esc_html(get_the_title($product_id)); ?></h3>
      <?php
      $excerpt = get_the_excerpt($product_id);
      if ($excerpt):
        ?>
        <div class="shop_product-description">
          <p><?php echo esc_html($excerpt); ?></p>
        </div>
      <?php endif; ?>
      <?php echo wp_kses_post($product->get_price_html()); ?>
    </div>
  </a>
  <?php
}
