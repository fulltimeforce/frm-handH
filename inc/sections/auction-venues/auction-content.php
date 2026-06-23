<?php
$current_year = date('Y');
$av_content_title = get_field('av_content_title') ?: "H&H Classics' prestigious auction venues";
$av_content_description = get_field('av_content_description'); ?>

<section class="auction_content" aria-labelledby="auction-content-heading">
    <div class="auction_content-container">
        <div class="auction_content-grid">
            <div class="auction_content-title">
                <h2 id="auction-content-heading">
                    <?= esc_html($av_content_title . ' ' . $current_year); ?>
                </h2>
            </div>
            <?php if (!empty($av_content_description)): ?>
                <div class="auction_content-content">
                    <div class="content">
                        <?= wp_kses_post($av_content_description); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>