<?php
/*
    Template name: specialists
*/

get_header();

$bg_image = get_field('breadcrumb_image');
$subtitle = get_field('specialist_subtitle');
$text = get_field('specialist_text');
$button = get_field('specialist_button');

get_banner('Homepage / About / Meet the team', esc_url($bg_image), 'Meet the team');
?>

<section class="specialist_page">
    <div class="container">
        <div class="specialist_content">
             <?php if ($subtitle): ?>
                <h2><?php echo esc_html($subtitle); ?></h2>
            <?php endif; ?>
            <?php if ($text): ?>
                <div class="specialist_text text">
                <?php echo $text; ?>
                    <?php if ($button): ?>
                        <a class="link_btn" href="<?php echo esc_url($button['url']); ?>" target="<?php echo esc_attr($button['target']); ?>">
                            <span><?php echo esc_html($button['title']); ?></span>
                            <img src="<?php echo IMG; ?>/arrow.svg">
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="specialist_list">
          <?php
            $categories = get_terms([
                'taxonomy' => 'category',
                'hide_empty' => true,
                'orderby' => 'id',
                'order' => 'ASC',
            ]);

            if (!empty($categories)) :
                $index = 0;
                foreach ($categories as $category) :
                    $team_query = new WP_Query([
                        'post_type' => 'team',
                        'posts_per_page' => -1,
                        'tax_query' => [[
                            'taxonomy' => 'category',
                            'field' => 'term_id',
                            'terms' => $category->term_id,
                        ]],
                        'orderby' => 'date',
                        'order' => 'ASC',
                    ]);

                    if ($team_query->have_posts()) :
                        if ($index !== 0) : ?>
                            <h3 class="specialist_position"><?php echo esc_html($category->name); ?></h3>
                        <?php endif; ?>
                        
                        <div class="specialist_row">
                            <?php while ($team_query->have_posts()) : $team_query->the_post(); ?>
                                <div class="specialist_item_wrapper">
                                    <div class="specialist_item">
                                        <div class="specialist_item_header">
                                            <?php if (has_post_thumbnail()) : ?>
                                                <a href="<?php the_permalink(); ?>" class="specialist_item_image">
                                                    <?php the_post_thumbnail('medium'); ?>
                                                </a>
                                            <?php endif; ?>
                                            <div class="specialist_item_container">
                                                <div class="specialist_item_info">
                                                    <p class="specialist_item_name"><?php the_title(); ?></p>
                                                    <?php if (get_field('job_position')) : ?>
                                                        <span class="specialist_item_job"><?php the_field('job_position'); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <button class="specialist_toggle open" aria-expanded="false" type="button">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M3 11.9994L21 11.9994M11.9997 3L11.9997 21" stroke="#F5F2EE" stroke-width="2"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="specialist_item_body">
                                            <div class="specialist_item_card_header">
                                                <div>
                                                    <p class="specialist_item_body_name"><?php the_title(); ?></p>
                                                    <?php if (get_field('job_position')) : ?>
                                                        <span class="specialist_item_body_job"><?php the_field('job_position'); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <button class="specialist_toggle close" aria-expanded="false" type="button">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M3 12L21 12" stroke="#F5F2EE" stroke-width="2"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div>
                                                <?php if (get_field('team_email')) : ?>
                                                    <p class="specialist_item_body_email">Email: <strong><?php the_field('team_email'); ?></strong></p>
                                                <?php endif; ?>
    
                                                <?php if (get_field('team_phone')) : ?>
                                                    <p class="specialist_item_body_tel">Tel: <strong><?php the_field('team_phone'); ?></strong></p>
                                                <?php endif; ?>
                                            </div>

                                            <div class="specialist_item_body_description">
                                                <?php the_excerpt(); ?>
                                            </div>

                                            <a href="<?php the_permalink(); ?>" class="specialist_readmore">Read More</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        <?php
                        wp_reset_postdata();
                        $index++;
                    endif;
                endforeach;
            endif;
            ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>