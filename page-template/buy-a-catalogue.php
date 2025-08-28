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
            <h2>Pre-Order Your Catalogue Today for <br>H&H Classics Auctions</h2>
            <div class="content">
                <p>All available lots are beautifully presented in luxury catalogues and available for auctions at the Imperial War Museum, Duxford, the Pavilion Gardens, Buxton and the National Motorcycle Museum, Solihull.</p>
                <p>Auction catalogues can be bought at the entrance both on viewing and auction days, however, if you would like to buy now and have it delivered to your door once printed, follow the link below, scan the QR code or contact us via telephone: <b>01925 210035</b> or email: <a href="mailto:info@HandH.co.uk">info@HandH.co.uk</a>. An annual subscription is available at a discounted rate of £150 plus £5 postage and ensure you never miss an H&H auction.</p>
                <p>Please note catalogues are sent out one week prior to sale, if you order your catalogue online within three days of the auction, you will need to print out your receipt and use it to collect your catalogue on the door.</p>
            </div>
            <div class="buy_catalogue-product">
                <img src="<?php echo IMG; ?>/placeholder2.png" class="thumb">
                <div class="actions">
                    <a href="#">Purchase a Catalogue</a>
                </div>
                <img src="<?php echo IMG; ?>/payments.png" class="payments">
            </div>
        </div>
        <div class="line"></div>
        <div class="qr">
            <h2>Or scan the QR code</h2>
            <img src="<?php echo IMG; ?>/qr.png">
            <p>*Please make sure you keep your address details up to date to ensure timely delivery of auction catalogues. If your address details change at any time, please send your new details to us at <a href="mailto:info@HandH.co.uk">info@HandH.co.uk</a></p>
        </div>
    </div>
</section>

<?php get_template_part('inc/sections/cta'); ?>

<section class="upcoming pb160">
    <?php get_template_part('inc/sections/upcoming'); ?>
</section>

<?php get_footer(); ?>