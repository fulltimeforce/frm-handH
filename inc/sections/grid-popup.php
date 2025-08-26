<?php if( have_rows('vehicle_image_gallery') ): ?>
    <div class="listing_grid">
        <div class="listing_grid-close">
            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60" fill="none">
                <rect width="60" height="60" rx="30" fill="#EEE9E2" fill-opacity="0.8"/>
                <path d="M42 42L18 18L30 30L18 42L42 18" stroke="#8C6E47" stroke-width="2"/>
            </svg>
        </div>
        <div class="listing_grid-content">
            <?php 
            $images = get_field('vehicle_image_gallery');
            $count  = count($images);
            if( $images ):
                foreach( $images as $row ):
                    $image = $row['image'];
                    if( $image ): ?>
                        <div class="listing_grid-item">
                            <img class="wh-100" src="<?php echo esc_url($image); ?>" alt="Vehicle Image">
                        </div>
                    <?php endif; 
                endforeach;
            endif;
            ?>
        </div>
    </div>
<?php endif; ?>