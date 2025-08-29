<?php
/*
    Template name: buy-a-catalogue
*/

get_header();

get_banner('Homepage / Classic Auctions / Buy A Catalogue', '', 'Buy A Catalogue');

?>

<section class="buy_catalogue pblock160">
    <div class="buy_catalogue-container">
        <div class="pre-order">
            <?php if (get_field('buycatalogue_title')): ?>
                <h2><?php echo get_field('buycatalogue_title'); ?></h2>
            <?php endif; ?>
            <div class="content">
                <?php if (get_field('buycatalogue_text')): ?>
                    <?php echo get_field('buycatalogue_text'); ?>
                <?php endif; ?>
            </div>
            <?php if (get_field('buycatalogue_image')): ?>
            <div class="buy_catalogue-product">
                <img src="<?php echo esc_url(get_field('buycatalogue_image')['url']); ?>" class="thumb">
                <div class="actions">
                    <a href="<?php echo esc_url(get_field('buycatalogue_link')['url']); ?>">Purchase a Catalogue</a>
                </div>
                <img src="<?php echo IMG; ?>/payments.png" class="payments">
            </div>
            <?php endif; ?>
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