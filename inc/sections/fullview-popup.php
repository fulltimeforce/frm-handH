<?php if( have_rows('vehicle_image_gallery') ): ?>
    <div class="listing_fullview">
        <div class="listing_fullview-close">
            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60" fill="none">
                <rect width="60" height="60" rx="30" fill="#EEE9E2" fill-opacity="0.8"/>
                <path d="M42 42L18 18L30 30L18 42L42 18" stroke="#8C6E47" stroke-width="2"/>
            </svg>
        </div>
        <div id="openGridView" class="listing_fullview-button">
            <img src="<?php echo IMG; ?>/grid-icon.svg" alt="icon">
        </div>
        <div class="listing_fullview-content">
            <?php 
            $images = get_field('vehicle_image_gallery');
            if( $images ): ?>
                <div class="listing_fullview-slide splide">
                    <div class="splide__track">
                        <ul class="splide__list">
                            <?php foreach( $images as $row ): 
                                $image = $row['image'];
                                if( $image ): ?>
                                    <li class="splide__slide listing_fullview-item">
                                        <img class="wh-100" src="<?php echo esc_url($image); ?>" alt="Vehicle Image">
                                    </li>
                                <?php endif; 
                            endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>