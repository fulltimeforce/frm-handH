<?php
/*
    Template name: buy-a-catalogue
*/

get_header();

get_banner('Homepage / Classic Auctions / Buy A Catalogue', get_the_post_thumbnail_url(get_the_ID(), 'full'), 'Buy A Catalogue');

?>

<section class="buy_catalogue pblock160">
    <div class="buy_catalogue-container">
        <div class="pre-order">
            <?php if (get_field('buycatalogue_title')): ?>
                <h2><?php echo get_field('buycatalogue_title'); ?></h2>
            <?php endif; ?>

            <?php if (get_field('buycatalogue_text')): ?>
                <div class="content">
                    <?php echo get_field('buycatalogue_text'); ?>
                </div>
            <?php endif; ?>

            <div class="buy_catalogue-product">
                <?php if (get_field('buycatalogue_image')): ?>
                    <img src="<?php echo esc_url(get_field('buycatalogue_image')['url']); ?>" class="thumb">
                <?php endif; ?>

                <?php if (get_field('buycatalogue_link')): ?>
                    <div class="actions">
                        <a href="<?php echo esc_url(get_field('buycatalogue_link')['url']); ?>" target="<?php echo get_field('buycatalogue_link')['target']; ?>">
                            <?php echo get_field('buycatalogue_link')['title']; ?>
                        </a>
                    </div>
                    <img src="<?php echo IMG; ?>/payments.png" class="payments">
                <?php endif; ?>
            </div>
        </div>
        <div class="line"></div>
        <?php if (get_field('buycatalogue_qr_code')): ?>
            <div class="qr">
                <h2>Or scan the QR code</h2>
                <img src="<?php echo esc_url(get_field('buycatalogue_qr_code')['url']); ?>">
                <?php echo get_field('buycatalogue_qr_code_text'); ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php get_template_part('inc/sections/cta'); ?>

<section class="upcoming pb160">
    <?php get_template_part('inc/sections/upcoming'); ?>
</section>

<?php get_footer(); ?>