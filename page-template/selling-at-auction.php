<?php
/*
    Template name: selling-at-auction
*/

get_header();

get_banner('Homepage / Classic Auctions / Selling at Auction', '', 'Selling at Auction');

?>

<section class="insurance insurance_v1 insurance_share">
    <div class="insurance_container">
        <div class="insurance-title">
            <h2>Selling your classic motorcar or motorcycle at auction with H&H Classics</h2>
        </div>
        <div class="content">
            <p>Selling your classic motorcar or motorcycle with H&H Classics is easy. Our specialists will guide you through the whole process and you can choose exactly how involved in the detail you want to be.</p>
            <p>Whether you’re consigning to one of our prestigious in-person Classic Auctions or to one of our Online Auctions and whether you’re selling a stunning Ferrari or a practical classic we deliver the highest levels of service, professionalism and transparency. Our passionate and knowledgeable specialists will provide you with an honest and accurate valuation born out of almost three decades of experience in the classic and collector auction market and will support you throughout the marketing and sales journey. </p>
            <p>All consignments with H&H Classics whether consigned into one of our prestigious Classic Auctions or our Online Auctions will be showcased on our website and social media channels, in our regular e-newsletters and on the following specialist portals.</p>
        </div>
        <div class="actions">
            <a href="#">Get a complimentary Valuation</a>
            <a href="#">View Our Upcoming Auctions</a>
        </div>
    </div>
</section>

<section class="w-100">
    <div class="insurance_container">
        <div class="insurance_slider">
            <div class="splide splidev1" role="group" id="logos1">
                <div class="splide__track">
                    <ul class="splide__list">
                        <li class="splide__slide">
                            <img src="<?php echo IMG; ?>/logos/2/logo1.png" class="logo1">
                        </li>
                        <li class="splide__slide">
                            <img src="<?php echo IMG; ?>/logos/2/logo2.png" class="logo2">
                        </li>
                        <li class="splide__slide">
                            <img src="<?php echo IMG; ?>/logos/2/logo3.png" class="logo3">
                        </li>
                        <li class="splide__slide">
                            <img src="<?php echo IMG; ?>/logos/2/logo4.png" class="logo4">
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<?php get_template_part('inc/sections/cta'); ?>

<section class="upcoming pb160">
    <?php get_template_part('inc/sections/upcoming'); ?>
</section>

<?php get_footer(); ?>