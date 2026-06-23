<?php
// SVG arrow component
$arrow_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none" aria-hidden="true">
    <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
</svg>';

$venues = get_field('venues'); ?>

<section class="interactive_map" data-state="1" aria-labelledby="map-heading">
    <div class="interactive_map-container">
        <div class="map w-100 relative">
            <?php get_template_part('inc/sections/map'); ?>
            <div class="map_information w-100">
                <div class="map_mini w-100 relative">
                    <?php get_template_part('inc/sections/pins'); ?>
                    <img src="<?= esc_url(IMG . '/map/minimap.svg'); ?>" alt="UK map showing venue locations"
                        class="w-100">
                </div>
                <div class="w-100 relative">
                    <?php foreach ($venues as $index => $venue):
                        $image_url = $venue['image'];
                        $button = $venue['button']; ?>
                        <div class="map_place" data-venue-id="<?= esc_attr($index + 1); ?>">
                            <div class="map_place-image">
                                <?php if (!empty($image_url) && !empty($image_url['url'])): ?>
                                    <img src="<?= esc_url($image_url['url']); ?>" alt="<?= esc_attr($venue['name']); ?>"
                                        loading="lazy">
                                <?php endif; ?>
                            </div>
                            <div class="map_place-content">
                                <h3><?= esc_html($venue['name']); ?></h3>
                                <div class="content">
                                    <p><?= esc_html($venue['description']); ?></p>
                                </div>
                                <?php if (!empty($button) && !empty($button["link"]) && !empty($button["title"])): ?>
                                    <a href="<?= esc_url($button["link"]) ?>"
                                        aria-label="View details for <?= esc_attr($button['title']); ?>">
                                        <?= esc_html($button["title"]) ?>
                                        <?= $arrow_svg; ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>