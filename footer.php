<footer class="footer">
    <div class="footer_container">
        <div class="footer_top">
            <a href="<?php echo esc_url(home_url('/')); ?>" title="<?php echo get_bloginfo('name'); ?>" class="footer_logo d-block w-100">
                <img src="<?php echo IMG; ?>/logo.svg" title="<?php echo get_bloginfo('name'); ?>" alt="<?php echo get_bloginfo('name'); ?>" class="w-100" loading="lazy">
            </a>
        </div>
        <div class="footer_grid">
            <div class="footer_col">
                <div class="footer_nav">
                    <p class="footer_nav-head">H&H Classics</p>
                    <div class="footer_nav-body">
                        <div>
                            <b>Address:</b>
                            <p>Sterling House Maple Court, Tankersley S75 3DP</p>
                        </div>
                        <div>
                            <b>Email:</b>
                            <a href="mailto:sales@handh.co.uk">sales@handh.co.uk</a>
                        </div>
                        <div>
                            <b>Phone:</b>
                            <a href="tel:+44 (0)1925 210035">+44 (0)1925 210035</a>
                        </div>
                    </div>
                </div>
                <div class="footer_east">
                    <b>H&H Classics is proud to support</b>
                    <img src="<?php echo IMG; ?>/logo-east.svg" class="w-100" alt="logo" title="East Cheshire Hospice">
                </div>
            </div>
            <div class="footer_col">
                <div class="footer_row">
                    <div class="footer_nav">
                        <p class="footer_nav-head">Auctions</p>
                        <?php
                        wp_nav_menu([
                            'theme_location' => 'footer-auctions-menu',
                            'container'      => false,
                            'depth'          => 1,
                            'walker'         => new Footer_Links_Walker(),
                            'items_wrap'     => '<div class="footer_nav-list">%3$s</div>',
                        ]);
                        ?>
                    </div>
                    <div class="footer_nav">
                        <p class="footer_nav-head">Private Sales</p>
                        <?php
                        wp_nav_menu([
                            'theme_location' => 'footer-private-sales-menu',
                            'container'      => false,
                            'depth'          => 1,
                            'walker'         => new Footer_Links_Walker(),
                            'items_wrap'     => '<div class="footer_nav-list">%3$s</div>',
                        ]);
                        ?>
                    </div>
                    <div class="footer_nav">
                        <p class="footer_nav-head">Account & Bidding</p>
                        <?php
                        wp_nav_menu([
                            'theme_location' => 'footer-account-bidding-menu',
                            'container'      => false,
                            'depth'          => 1,
                            'walker'         => new Footer_Links_Walker(),
                            'items_wrap'     => '<div class="footer_nav-list">%3$s</div>',
                        ]);
                        ?>
                    </div>
                </div>
                <div class="footer_bottom">
                    <div class="footer_suscription">
                        <p class="footer_nav-head">Get the H&H newsletter</p>
                        <p class="footer_nav-description">Be first to hear about our latest auction consignments of classic & collector motorcars and motorcycles</p>
                        <div class="w-100 footer_suscription-form">
                            <?php echo do_shortcode('[gravityform id="1" title="true" ajax="true"]'); ?>
                        </div>
                    </div>
                    <div class="footer_award">
                        <img src="<?php echo IMG; ?>/award.svg" class="w-100" alt="award" title="Quality Business Awards">
                        <?php get_template_part('inc/sections/social-list-links'); ?>
                        <?php
                        wp_nav_menu([
                            'theme_location' => 'footer-bold',
                            'container'      => false,
                            'items_wrap'     => '<div class="links">%3$s</div>', // envuelve todo
                            'walker'         => new Footer_Bold_Links_Walker(),
                        ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer_credits">
            <p>© <?php echo date('Y'); ?> H&H Classic Auctions Ltd. All Rights Reserved.</p>
            <div>
                <p>
                    <?php
                    wp_nav_menu([
                        'theme_location' => 'footer-terms',
                        'container'      => false,
                        'items_wrap'     => '%3$s', // 🔑 elimina el <ul>
                        'walker'         => new Footer_Terms_Walker(),
                    ]);
                    ?>
                </p>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>

</body>

</html>