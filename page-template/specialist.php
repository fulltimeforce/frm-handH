<?php
/*
    Template name: specialists
*/

get_header();

$subtitle = get_field('specialist_subtitle');
$text = get_field('specialist_text');
$button = get_field('specialist_button');
?>

<?php get_template_part('inc/sections/breadcrumb'); ?>

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
                        <a class="primary_btn" href="<?php echo esc_url($button['url']); ?>" target="<?php echo esc_attr($button['target']); ?>">
                            <?php echo esc_html($button['title']); ?>
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
                                <div class="specialist_item">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <div class="specialist_image">
                                            <?php the_post_thumbnail('medium'); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <p><?php the_title(); ?></p>
                                        <span></span>
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