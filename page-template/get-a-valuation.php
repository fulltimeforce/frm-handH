<?php
/*
    Template name: get-a-valuation
*/

get_header();

get_banner('Homepage / Classic Auctions / Get a Valuation', '', 'Get a Valuation');

?>

<section class="valuation_info">
    <div class="valuation_info-container">
        <div class="valuation_info-title">
            <?php if (get_field('getvaluation_title')): ?>
                <h2><?php echo get_field('getvaluation_title'); ?></h2>
            <?php endif; ?>
        </div>
        <div class="valuation_info-row">
            <?php if (get_field('getvaluation_column_1')): ?>
            <div class="valuation_info-col">
                <p><?php echo get_field('getvaluation_column_1'); ?></p>
                <h3><?php echo get_field('getvaluation_subtitle_1'); ?></h3>
                <?php if (get_field('getvaluation_text_1')): ?>
                <?php echo get_field('getvaluation_text_1'); ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php if (get_field('getvaluation_column_2')): ?>
            <div class="valuation_info-col">
                <p><?php echo get_field('getvaluation_column_2'); ?></p>
                <h3><?php echo get_field('getvaluation_subtitle_2'); ?></h3>
                <?php if (get_field('getvaluation_text_2')): ?>
                <?php echo get_field('getvaluation_text_2'); ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="showcase">
    <div class="showcase-container">
         <?php if (get_field('getvaluation_form_title')): ?>
        <div class="showcase-title">
            <h2><?php echo get_field('getvaluation_form_title'); ?></h2>
        </div>
         <?php endif; ?>
        <div class="showcase-form w-100">
            <?php echo do_shortcode('[gravityform id="4" title="true" ajax="true"]'); ?>
        </div>
        <div class="showcase-connect">
            <p>Join H&H Classics on our social media channels today</p>
            <?php get_template_part('inc/sections/social-list-links'); ?>
        </div>
        <div class="showcase-dropdown">
            <ul id="my-accordion" class="accordionjs">
                <?php if (get_field('getvaluation_terms_text')): ?>
                <li>
                    <div>
                        <h3>Terms & Conditions</h3>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                            <path d="M0 8.99943L18 8.99943M8.99969 0L8.99969 18" stroke="#8C6E47" stroke-width="2" />
                        </svg>
                    </div>
                    <div class="description">
                        <?php echo get_field('getvaluation_terms_text'); ?>
                    </div>
                </li>
                <?php endif; ?>
                <?php if (get_field('getvaluation_form_text')): ?>
                <li>
                    <div>
                        <h3>Download Forms</h3>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                            <path d="M0 8.99943L18 8.99943M8.99969 0L8.99969 18" stroke="#8C6E47" stroke-width="2" />
                        </svg>
                    </div>
                    <div class="description">
                        <?php echo get_field('getvaluation_form_text'); ?>
                    </div>
                </li>
                <?php endif; ?>
            </ul>
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
        openSection: function(section) {
            let index = $(section).index();
            $(".opportunities").attr('data-state', index);
        }
    });
</script>