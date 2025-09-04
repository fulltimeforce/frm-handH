<?php
$gallery = get_field('gallery_vehicle');

if ($gallery && is_array($gallery)):
    // Normalizar a un arreglo con url y alt
    $imgs = [];
    foreach ($gallery as $item) {
        $url = $alt = '';

        if (is_array($item)) {               // ACF como array
            $id  = $item['ID'] ?? 0;
            $url = $item['url'] ?? ($id ? wp_get_attachment_image_url($id, 'full') : '');
            $alt = $item['alt'] ?? ($id ? get_post_meta($id, '_wp_attachment_image_alt', true) : ($item['title'] ?? ''));
        } elseif (is_numeric($item)) {       // ACF como ID
            $id  = (int) $item;
            $url = wp_get_attachment_image_url($id, 'full');
            $alt = get_post_meta($id, '_wp_attachment_image_alt', true);
        } else {                              // ACF como URL
            $url = $item;
            $alt = '';
        }

        if ($url) $imgs[] = ['url' => $url, 'alt' => $alt];
    }
?>

    <div class="listing_fullview">
        <div class="listing_fullview-close">
            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60" fill="none">
                <rect width="60" height="60" rx="30" fill="#EEE9E2" fill-opacity="0.8" />
                <path d="M42 42L18 18L30 30L18 42L42 18" stroke="#8C6E47" stroke-width="2" />
            </svg>
        </div>
        <div id="openGridView" class="listing_fullview-button">
            <img src="<?php echo IMG; ?>/grid-icon.svg" alt="icon">
        </div>

        <div class="listing_fullview-content">
            <?php if (!empty($imgs)) : ?>
                <div class="listing_fullview-slide splide">
                    <div class="splide__track">
                        <ul class="splide__list">
                            <?php foreach ($imgs as $img): ?>
                                <li class="splide__slide listing_fullview-item">
                                    <img class="wh-100" src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr($img['alt'] ?: 'Vehicle Image'); ?>">
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php endif; ?>