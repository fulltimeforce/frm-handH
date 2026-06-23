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

        <div class="listing_grid-scroll-hint scroll-downs" aria-hidden="true">
            <div class="mousey">
                <div class="scroller"></div>
            </div>
        </div>

        <div class="listing_grid-content">
            <?php
            $grid_items = [];

            foreach ($gallery as $item) {
                $url = $alt = '';

                if (is_array($item)) {
                    $id  = $item['ID'] ?? 0;
                    $url = $item['url'] ?? ($id ? wp_get_attachment_image_url($id, 'full') : '');
                    $alt = $item['alt'] ?? ($id ? get_post_meta($id, '_wp_attachment_image_alt', true) : ($item['title'] ?? ''));
                } elseif (is_numeric($item)) {
                    $id  = (int) $item;
                    $url = wp_get_attachment_image_url($id, 'full');
                    $alt = get_post_meta($id, '_wp_attachment_image_alt', true);
                } else {
                    $url = $item;
                    $alt = '';
                }

                if ($url) {
                    $grid_items[] = [
                        'url'            => $url,
                        'alt'            => $alt ?: 'Vehicle Image',
                        'fullview_index' => count($grid_items),
                        'is_placeholder' => false,
                    ];
                }
            }

            $placeholder = (defined('IMG') ? IMG : get_template_directory_uri() . '/images') . '/placeholder-vehicle.png';
            $min_items   = 9;

            if (count($grid_items) < $min_items) {
                $needed = $min_items - count($grid_items);
                for ($i = 0; $i < $needed; $i++) {
                    $grid_items[] = [
                        'url'            => $placeholder,
                        'alt'            => 'Further photographs coming soon',
                        'is_placeholder' => true,
                    ];
                }
            }

            foreach ($grid_items as $grid_item) : ?>
                <div class="listing_grid-item w-100"<?php if (empty($grid_item['is_placeholder'])): ?> data-fullview-index="<?php echo (int) $grid_item['fullview_index']; ?>" role="button" tabindex="0"<?php endif; ?>>
                    <img class="wh-100" src="<?php echo esc_url($grid_item['url']); ?>" alt="<?php echo esc_attr($grid_item['alt']); ?>">
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>