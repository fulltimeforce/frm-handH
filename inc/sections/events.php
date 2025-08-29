<section class="events">
    <div class="container">
        <div class="featured_articles_head title_watermark">
            <div class="watermark"><p>Events & meets</p></div>
            <div class="breadlines">
                <p>Programme Lineup</p>
            </div>
            <h2>Events & Meets</h2>

            <?php if (have_rows('events_items')): ?>
                <div class="events_slide splide" role="group">
                    <div class="splide__arrows">
                        <button class="splide__arrow splide__arrow--prev">
                            <svg xmlns="http://www.w3.org/2000/svg" width="50" height="26" viewBox="0 0 50 26" fill="none">
                                <path d="M0 13H48M48 13L36 1M48 13L36 25" stroke="#8C6E47" stroke-width="2" />
                            </svg>
                        </button>
                        <button class="splide__arrow splide__arrow--next">
                            <svg xmlns="http://www.w3.org/2000/svg" width="50" height="26" viewBox="0 0 50 26" fill="none">
                                <path d="M0 13H48M48 13L36 1M48 13L36 25" stroke="#8C6E47" stroke-width="2" />
                            </svg>
                        </button>
                    </div>
                    <div class="splide__track">
                        <ul class="splide__list">
                            <?php while (have_rows('events_items')): the_row(); 
                                $bg       = get_sub_field('events_bg');
                                $subtitle = get_sub_field('events_subtitle');
                                $title    = get_sub_field('events_title');
                                $place    = get_sub_field('events_place');
                                $link     = get_sub_field('events_link');
                            ?>
                                <li class="splide__slide">
                                    <div class="events_slide-item" style="background-image:url('<?php echo esc_url($bg['url']); ?>')">
                                        <div class="events_slide-info">
                                            <?php if ($subtitle): ?>
                                                <p class="events_slide-subtitle"><?php echo esc_html($subtitle); ?></p>
                                            <?php endif; ?>

                                            <?php if ($title): ?>
                                                <p class="events_slide-title"><?php echo esc_html($title); ?></p>
                                            <?php endif; ?>

                                            <?php if ($place): ?>
                                                <p class="events_slide-subtitle"><?php echo esc_html($place); ?></p>
                                            <?php endif; ?>
                                            <div>
                                            <?php if ($link): ?>
                                                <a href="<?php echo esc_url($link['url']); ?>"><?php echo $link['title']; ?></a>
                                            <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</section>
