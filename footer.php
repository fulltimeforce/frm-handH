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
                    <img src="<?php echo IMG; ?>/logo-east.svg" class="w-100">
                </div>
            </div>
            <div class="footer_col">
                <div class="footer_row">
                    <div class="footer_nav">
                        <p class="footer_nav-head">Auctions</p>
                        <div class="footer_nav-list">
                            <a href="https://issuu.com/handhclassicsadmin/docs/h_h-2025-auction-calendar">Auction Calendar</a>
                            <a href="<?php echo esc_url(home_url('auction-results')) ?>">Auction Results</a>
                            <a href="<?php echo esc_url(home_url('get-a-valuation')) ?>">Enter Your Classic</a>
                            <a target="_blank" href="https://issuu.com/handhclassics/docs/handh-welcome-booklet-20231215?fr=sYjkxNTYzMTE1NDY" target="_blank">H&H Welcome Pack</a>
                        </div>
                    </div>
                    <div class="footer_nav">
                        <p class="footer_nav-head">Private Sales</p>
                        <div class="footer_nav-list">
                            <a href="<?php echo esc_url(home_url('vehicles-for-sale')) ?>">Vehicles For Sale</a>
                            <a href="<?php echo esc_url(home_url('vehicles-wanted')) ?>">Vehicles Wanted</a>
                            <a href="<?php echo esc_url(home_url('our-showroom')) ?>">Our Showroom</a>
                        </div>
                    </div>
                    <div class="footer_nav">
                        <p class="footer_nav-head">Account & Bidding</p>
                        <div class="footer_nav-list">
                            <?php if (!is_user_logged_in()) : ?>
                                <a href="<?php echo esc_url(home_url('my-account')); ?>">Register / Sign In</a>
                            <?php else: ?>
                                <a href="<?php echo esc_url(home_url('my-account/edit-account')); ?>">Register / Sign In</a>
                            <?php endif; ?>
                            <a href="#">Make a Payment</a>
                            <a target="_blank" href="https://handh.blob.core.windows.net/main/HandH-Telephone-Bidding-Form-20230714.pdf">Telephone Bid Form</a>
                        </div>
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
                        <img src="<?php echo IMG; ?>/award.svg" class="w-100">
                        <?php get_template_part('inc/sections/social-list-links'); ?>
                        <div class="links">
                            <a href="<?php echo esc_url(home_url('news-and-insights')); ?>">News</a>
                            <p>|</p>
                            <a href="<?php echo esc_url(home_url('about-us')); ?>">About</a>
                            <p>|</p>
                            <a href="<?php echo esc_url(home_url('frequently-asked-questions')); ?>">FAQs</a>
                            <p>|</p>
                            <a href="<?php echo esc_url(home_url('contact')); ?>">Contact</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer_credits">
            <p>© 2024 H&H Classic Auctions Ltd. All Rights Reserved.</p>
            <div>
                <p><a href="<?php echo esc_url(home_url('terms-conditions')); ?>">Terms and Conditions</a> | <a href="<?php echo esc_url(home_url('privacy-policy')); ?>">Privacy Policy</a> | <a href="<?php echo esc_url(home_url('cookies-policy')); ?>">Cookies Policy</a></p>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>

</body>

</html>