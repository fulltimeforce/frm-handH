<section class="cta">
    <div class="cta_bg">
        <?php if (!empty(get_field('banner_venue-auction'))): ?>
            <img src="<?php echo get_field('banner_venue-auction'); ?>" alt="Banner">
        <?php else: ?>
            <img src="<?php echo IMG; ?>/single-venue-banner.webp" alt="Banner">
        <?php endif; ?>
    </div>
    <div class="container">
        <div class="cta_content">
            <h2>Trusted auctioneers of classic and collector motorcars and motorcycles since 1993</h2>
            <div class="cta_links">
                <a href="<?php echo esc_url(get_permalink(48)); ?>" alt="Contact Us Now">Contact Us Now</a>
                <a href="<?php echo esc_url(get_permalink(50)); ?>" alt="Upcoming Auctions">Upcoming Auctions</a>
            </div>
        </div>
    </div>
</section>