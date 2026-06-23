<?php
$auct_title = get_field('about_auction_title');
$auct_text = get_field('about_auction_text');
$auct_btn1 = get_field('about_auction_btn1');
$auct_btn2 = get_field('about_auction_btn2');

$auct_btn1_text = get_field('about_auction_btn1_text');
$auct_btn2_text = get_field('about_auction_btn2_text');
?>

<section class="upcoming" id="upcoming-auctions">
    <?php get_template_part('inc/sections/upcoming'); ?>
    <div class="upcoming_container">
        <div class="upcoming_info">
            <?php if ($auct_title): ?>
                <div class="upcoming_info-title">
                    <h2><?php echo $auct_title; ?></h2>
                </div>
            <?php endif; ?>
            <div class="upcoming_info-content">
                <?php if ($auct_text): ?>
                    <div>
                        <?php echo $auct_text; ?>
                    </div>
                <?php endif; ?>
                <div class="upcoming_info-actions">
                    <?php if ($auct_btn1 && !empty($auct_btn1_text)): ?>
                        <a href="<?php echo esc_url($auct_btn1); ?>" class="permalink_border">
                            <?php echo $auct_btn1_text; ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                                <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                            </svg>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($auct_btn2 && !empty($auct_btn2_text)): ?>
                        <a href="<?php echo esc_url($auct_btn2); ?>" class="permalink_border">
                            <?php echo $auct_btn2_text; ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                                <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>