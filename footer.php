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
                            <a href="#">Auction Calendar</a>
                            <a href="#">Auction Results</a>
                            <a href="#">Enter Your Classic</a>
                            <a href="#">H&H Welcome Pack</a>
                        </div>
                    </div>
                    <div class="footer_nav">
                        <p class="footer_nav-head">Private Sales</p>
                        <div class="footer_nav-list">
                            <a href="#">Vehicles For Sale</a>
                            <a href="#">Vehicles Wanted</a>
                            <a href="#">Our Showroom</a>
                        </div>
                    </div>
                    <div class="footer_nav">
                        <p class="footer_nav-head">Account & Bidding</p>
                        <div class="footer_nav-list">
                            <a href="#">Register / Sign In</a>
                            <a href="#">Make a Payment</a>
                            <a href="#">Telephone Bid Form</a>
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
                            <a href="#">News</a>
                            <p>|</p>
                            <a href="#">About</a>
                            <p>|</p>
                            <a href="#">FAQs</a>
                            <p>|</p>
                            <a href="#">Contact</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer_credits">
            <p>© 2024 H&H Classic Auctions Ltd. All Rights Reserved.</p>
            <div>
                <p><a href="">Terms and Conditions</a> | <a href="">Privacy Policy</a> | <a href="">Cookies Policy</a></p>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>

</body>

</html>