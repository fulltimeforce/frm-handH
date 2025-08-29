<section class="timeline_section">
    <div class="timeline_container">
        <h2>Our world-class auction venues include:</h2>
        <div class="timeline_list">
            <div class="timeline_list-start">
                <img src="<?php echo IMG; ?>/down-button.svg" alt="Start">
            </div>

            <?php if( have_rows('time_items') ): ?>
                <?php $i = 0; ?>
                <?php while( have_rows('time_items') ): the_row(); 
                    $title = get_sub_field('time_item_title');
                    $image = get_sub_field('time_item_image');
                    $link  = get_sub_field('time_item_link');
                    $side = ($i % 2 == 0) ? 'left' : 'right';
                ?>
                    <div class="timeline_item <?php echo $side; ?>">
                        <?php if( $title ): ?>
                            <h3><?php echo esc_html($title); ?></h3>
                        <?php endif; ?>

                        <?php if( $image ): ?>
                            <div class="timeline_image w-100">
                                <img class="w-100" 
                                    src="<?php echo esc_url($image['url']); ?>" 
                                    alt="<?php echo esc_attr($image['alt']); ?>">
                                <?php if( $link ): ?>
                                    <a href="<?php echo esc_url($link['url']); ?>" class="timeline_button">
                                        View Venue
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php $i++; ?>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</section>