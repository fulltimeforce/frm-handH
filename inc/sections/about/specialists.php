<?php
$s_title = get_field('specialists_title');
$stext =  get_field('specialists_text');
$s_link = get_field('specialists_btn');
$s_text = get_field('specialists_btn_text');
?>

<section class="meet_our_specialist" id="specialists">
    <div class="meet_our_specialist-container">
        <div class="meet_our_specialist-head title_watermark">
            <div class="watermark">
                <p>Meet Our Specialists</p>
            </div>
            <div class="breadlines">
                <p>Classic Vehicle Experts</p>
            </div>
            <h2>Meet Our Specialists</h2>
        </div>
        <div class="meet_our_specialist-slider">
            <div class="splide" role="group" id="specialist">
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
                        <?php
                        $args = array(
                            'post_type'      => 'team',
                            'posts_per_page' => -1,
                            'orderby'        => 'menu_order',
                            'order'          => 'ASC'
                        );
                        $team_query = new WP_Query($args);

                        if ($team_query->have_posts()):
                            while ($team_query->have_posts()): $team_query->the_post();
                                $job_position = get_field('job_position');
                                $team_email   = get_field('team_email');
                                $team_phone   = get_field('team_phone');
                                $content      = get_the_content();
                                $image        = get_the_post_thumbnail_url(get_the_ID(), 'medium_large');
                        ?>
                                <li class="splide__slide">
                                    <div class="specialist_card">
                                        <div class="specialist_card-front">
                                            <div class="specialist_card-image">
                                                <?php if ($image): ?>
                                                    <img src="<?php echo esc_url($image); ?>" alt="<?php the_title_attribute(); ?>">
                                                <?php else: ?>
                                                    <img src="<?php echo IMG; ?>/member.png" alt="<?php the_title_attribute(); ?>">
                                                <?php endif; ?>
                                            </div>
                                            <div class="specialist_card-info specialist_card-toggle">
                                                <div>
                                                    <p><?php the_title(); ?></p>
                                                    <?php if ($job_position): ?>
                                                        <span><?php echo esc_html($job_position); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <button type="button" class="card_toggle">
                                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M0 8.99943L18 8.99943M8.99969 0L8.99969 18" stroke="#F5F2EE"
                                                            stroke-width="2" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="specialist_card-back">
                                            <div class="specialist_card-toggle">
                                                <div>
                                                    <p><?php the_title(); ?></p>
                                                    <?php if ($job_position): ?>
                                                        <span><?php echo esc_html($job_position); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <button type="button" class="card_toggle minus">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="2"
                                                        viewBox="0 0 18 2" fill="none">
                                                        <path d="M0 1L18 0.999999" stroke="#F5F2EE" stroke-width="2" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="specialist_card-content">
                                                <ul>
                                                    <?php if ($team_email): ?>
                                                        <li>Email: <a href="mailto:<?php echo antispambot($team_email); ?>"><?php echo antispambot($team_email); ?></a></li>
                                                    <?php endif; ?>
                                                    <?php if ($team_phone): ?>
                                                        <li>Tel: <a href="tel:<?php echo preg_replace('/\D+/', '', $team_phone); ?>"><?php echo esc_html($team_phone); ?></a></li>
                                                    <?php endif; ?>
                                                </ul>
                                                <?php if ($content): ?>
                                                    <p>
                                                        <?php
                                                        $clean = preg_replace('/<\/(h3|p)>/i', '<br><br>', $content);
                                                        $clean = strip_tags($clean, '<br>');
                                                        $clean = preg_replace('/(<br>\s*)+/', '<br><br>', $clean);
                                                        $pos = strpos($clean, '<br><br>', strpos($clean, '<br><br>') + 1);
                                                        if ($pos !== false && $pos <= 342) {
                                                            $excerpt = substr($clean, 0, $pos);
                                                        } else {
                                                            $excerpt = mb_substr($clean, 0, 342);
                                                            if (mb_strlen(strip_tags($clean)) > 342 && mb_substr(trim($excerpt), -1) !== '.') {
                                                                $excerpt .= '...';
                                                            }
                                                        }
                                                        echo $excerpt;
                                                        ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                            <a href="<?php the_permalink(); ?>">Read More</a>
                                        </div>
                                    </div>
                                </li>
                        <?php
                            endwhile;
                            wp_reset_postdata();
                        endif;
                        ?>
                    </ul>

                </div>
            </div>
        </div>
    </div>
    <div class="meet_our_specialist-foot">
        <?php if (!empty($s_title)): ?>
            <div class="meet_our_specialist-title">
                <h2><?php echo $s_title; ?></h2>
            </div>
        <?php endif; ?>
        <?php if (!empty($stext)): ?>
            <div class="meet_our_specialist-content">
                <?php echo $stext; ?>

                <?php if (!empty($s_link) && !empty($s_text)): ?>
                    <a href="<?php echo $s_link; ?>" class="permalink_border">
                        <?php echo $s_text; ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                            <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                        </svg>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>