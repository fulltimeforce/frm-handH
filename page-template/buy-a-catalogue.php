<?php
/*
    Template name: buy-a-catalogue
*/

get_header();

get_banner('Homepage / Classic Auctions / Buy A Catalogue', '', 'Buy A Catalogue');

?>

<section class="catalogue pblock160">
    <div class="container">
        <div class="pre-order">
            <h2>Pre-Order Your Catalogue Today for H&H Classics Auctions</h2>
            <div class="content">
                <p>All available lots are beautifully presented in luxury catalogues and available for auctions at the Imperial War Museum, Duxford, the Pavilion Gardens, Buxton and the National Motorcycle Museum, Solihull.</p>
                <p>Auction catalogues can be bought at the entrance both on viewing and auction days, however, if you would like to buy now and have it delivered to your door once printed, follow the link below, scan the QR code or contact us via telephone: 01925 210035 or email: info@HandH.co.uk. An annual subscription is available at a discounted rate of £150 plus £5 postage and ensure you never miss an H&H auction.</p>
                <p>Please note catalogues are sent out one week prior to sale, if you order your catalogue online within three days of the auction, you will need to print out your receipt and use it to collect your catalogue on the door.</p>
            </div>
        </div>
    </div>
</section>

<?php get_template_part('inc/sections/cta'); ?>

<section class="upcoming pb160">
    <?php get_template_part('inc/sections/upcoming'); ?>
</section>

<?php get_footer(); ?>