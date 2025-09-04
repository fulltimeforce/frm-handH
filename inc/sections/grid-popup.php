<?php
$gallery = get_field('gallery_vehicle');

if ($gallery && is_array($gallery)): ?>
    <div class="listing_grid">
        <div class="listing_grid-close">
            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60" fill="none">
                <rect width="60" height="60" rx="30" fill="#EEE9E2" fill-opacity="0.8" />
                <path d="M42 42L18 18L30 30L18 42L42 18" stroke="#8C6E47" stroke-width="2" />
            </svg>
        </div>

        <div class="listing_grid-content">
            <?php
            foreach ($gallery as $item) {
                $url = $alt = '';

                if (is_array($item)) {                 // ACF devuelve array
                    $id  = $item['ID'] ?? 0;
                    $url = $item['url'] ?? ($id ? wp_get_attachment_image_url($id, 'full') : '');
                    $alt = $item['alt'] ?? ($id ? get_post_meta($id, '_wp_attachment_image_alt', true) : ($item['title'] ?? ''));
                } elseif (is_numeric($item)) {         // IDs
                    $id  = (int) $item;
                    $url = wp_get_attachment_image_url($id, 'full');
                    $alt = get_post_meta($id, '_wp_attachment_image_alt', true);
                } else {                                // URLs
                    $url = $item;
                    $alt = '';
                }

                if ($url): ?>
                    <div class="listing_grid-item">
                        <img class="wh-100" src="<?php echo esc_url($url); ?>" alt="<?php echo esc_attr($alt ?: 'Vehicle Image'); ?>">
                    </div>
            <?php endif;
            }
            ?>
        </div>
    </div>
<?php endif; ?>