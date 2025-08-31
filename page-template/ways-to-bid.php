<?php
/*
    Template name: ways-to-bid
*/

get_header();

get_banner('Homepage / Classic Auctions / Ways to Bid', get_the_post_thumbnail_url(get_the_ID(), 'full'), 'Ways to Bid');

?>

<section class="insurance insurance_v2">
    <div class="insurance_container">
        <div class="insurance-title">
            <h2><?php the_field('ways_title'); ?></h2>
        </div>
        <div class="insurance-txt">
            <?php the_field('ways_text'); ?>
        </div>
    </div>
</section>

<section class="bid_online">
    <div class="bid_online-container">
        <div class="bid_online-row">
            <div class="bid_online-image">
                <?php if ($img = get_field('ways_image')): ?>
                    <img src="<?= esc_url($img['url']); ?>" alt="<?= esc_attr($img['alt']); ?>">
                <?php endif; ?>
            </div>
            <div class="bid_online-content">
               <div class="w-100">
                    <?php if ($title = get_field('ways_title_2')): ?>
                        <h3><?= esc_html($title); ?></h3>
                    <?php endif; ?>

                    <?php if ($text = get_field('ways_text_2')): ?>
                        <div class="content">
                            <?= $text; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (have_rows('ways_steps')): ?>
                        <ul>
                            <?php 
                            $i = 1; 
                            while (have_rows('ways_steps')): the_row(); 
                                $step_text = get_sub_field('ways_step_text');
                            ?>
                                <li>
                                    <h4>Step <?= $i; ?></h4>
                                    <p><?= esc_html($step_text); ?></p>
                                </li>
                            <?php 
                                $i++;
                            endwhile; 
                            ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="other_methods">
    <div class="other_methods-container">
        <div class="other_methods-image">
            <div>
                <?php if ($img = get_field('ways_method_image')): ?>
                    <img src="<?= esc_url($img['url']); ?>" alt="<?= esc_attr($img['alt']); ?>">
                <?php endif; ?>
                <h3>Other Methods to Bid</h3>
            </div>
        </div>

        <div class="other_methods-dropdown w-100">
            <?php if (have_rows('ways_method_items')): ?>
                <ul id="my-accordion" class="accordionjs">
                    <?php while (have_rows('ways_method_items')): the_row(); 
                        $item_title = get_sub_field('ways_method_item_title');
                        $item_text  = get_sub_field('ways_method_item_text');
                    ?>
                        <li>
                            <div>
                                <?php if ($item_title): ?>
                                    <h3><?= esc_html($item_title); ?></h3>
                                <?php endif; ?>

                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" 
                                    viewBox="0 0 18 18" fill="none">
                                    <path d="M0 8.99943L18 8.99943M8.99969 0L8.99969 18" 
                                        stroke="#8C6E47" stroke-width="2" />
                                </svg>
                            </div>
                            <div>
                                <div class="description">
                                    <?= $item_text; ?>
                                </div>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="once">
    <div class="once-container">
        <?php if ($title = get_field('ways_completed_title')): ?>
            <div class="once-title">
                <h2><?= esc_html($title); ?></h2>
            </div>
        <?php endif; ?>

        <?php if ($text = get_field('ways_completed_text')): ?>
            <div class="content">
                <?= $text; ?>
            </div>
        <?php endif; ?>

        <div class="actions">
            <?php if ($btn1 = get_field('ways_completed_button_1')): ?>
                <a href="<?= esc_url($btn1['url']); ?>" target="<?= esc_attr($btn1['target'] ?: '_self'); ?>">
                    <?= esc_html($btn1['title']); ?>
                </a>
            <?php endif; ?>

            <?php if ($btn2 = get_field('ways_completed_button_2')): ?>
                <a href="<?= esc_url($btn2['url']); ?>" target="<?= esc_attr($btn2['target'] ?: '_self'); ?>">
                    <?= esc_html($btn2['title']); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php get_template_part('inc/sections/cta'); ?>

<section class="upcoming pb160">
    <?php get_template_part('inc/sections/upcoming'); ?>
</section>

<?php get_footer(); ?>

<script>
    $("#my-accordion").accordionjs({
        closeAble: true,
        closeOther: true,
        slideSpeed: 150,
        activeIndex: 100,
    });
</script>