<?php
/*
    Template name: upcoming-actions
*/

get_header();

get_banner('Homepage / classic auctions / Upcoming Auctions');

?>

<section class="auction_list">
    <div class="container-pages">
        <?php for ($j = 0; $j < 5; $j++): ?>
            <div class="auction">
                <div class="auction_thumb">
                    <div class="thumb">
                        <img src="<?php echo IMG; ?>/auction1.png">
                    </div>
                </div>
                <div class="auction_info">
                    <h2>Pavilion Gardens</h2>
                    <div class="content">
                        <ul>
                            <li>Date: <b>Wednesday, February 12th, 2025</b></li>
                            <li>Location: <b>St John's Rd, Buxton SK17 6BE</b></li>
                            <li>View Lots: <b>49</b></li>
                        </ul>
                    </div>
                    <div class="content">
                        <p><b>Classic Motorcars:</b> An auction of classic, collector and performance motorcars to be held in the beautiful surrounds of the Pavilion Gardens, Buxton, Derbyshire.</p>
                    </div>
                    <a href="#">
                        Venue Details
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="12" viewBox="0 0 22 12" fill="none">
                            <path d="M0.25 6H20.25M20.25 6L15.25 1M20.25 6L15.25 11" stroke="#8C6E47" stroke-width="1.20833" />
                        </svg>
                    </a>
                </div>
                <div class="auction_actions">
                    <ul>
                        <li><a href="">View Upcoming Lots</a></li>
                        <li><a href="">Consign Your Classic</a></li>
                        <li><a href="">Watch Live</a></li>
                        <li><a href="">Learn how to bid</a></li>
                        <li><a href="">View E-Catalogue</a></li>
                    </ul>
                </div>
                <div class="auction_keytimes">

                </div>
            </div>
        <?php endfor; ?>
    </div>
</section>

<?php get_footer(); ?>