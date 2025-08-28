<?php
/*
    Template name: auction-venues
*/

get_header();

get_banner('Homepage / Classic Auctions / Auction Venues', '', 'H&H Auction Venues 2025');

?>

<section class="big_video">
    <div class="big_video-container">
        <div class="video">
            <video poster="">
                <source src="<?php echo IMG; ?>/auction-venues.mp4">
            </video>
        </div>
        <h2>H&H Auction Venues 2025</h2>
    </div>
</section>

<section class="auction_content">
    <div class="auction_content-container">
        <div class="auction_content-grid">
            <div class="auction_content-title">
                <h2>H&H Classics' prestigious auction venues 2025</h2>
            </div>
            <div class="auction_content-content">
                <div class="content">
                    <p>Welcome to H&H Classics' prestigious auction venues, where automotive history meets exceptional collecting opportunities. Our carefully selected locations combine historical significance with modern auction facilities, providing the perfect backdrop for buying and selling classic vehicles.</p>
                    <p>Each venue has been chosen for its unique character and ability to showcase classic cars and motorcycles in their finest setting. From historic aviation centers to Victorian gardens, every location offers a distinctive atmosphere that enhances the auction experience.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="interactive_map" data-state="1">
    <div class="interactive_map-container">
        <div class="map w-100 relative">
            <?php get_template_part('inc/sections/map'); ?>
            <div class="map_information w-100">
                <div class="map_mini w-100 relative">
                    <?php get_template_part('inc/sections/pins'); ?>
                    <img src="<?php echo IMG; ?>/map/minimap.svg" class="w-100">
                </div>
                <div class="w-100 relative">
                    <div class="map_place">
                        <div class="map_place-image">
                            <img src="<?php echo IMG; ?>/map/1.png">
                        </div>
                        <div class="map_place-content">
                            <h3>Pavilion Gardens</h3>
                            <div class="content">
                                <p>Victorian-era gardens in historic spa town center hosts classic car auctions in elegant Octagon Hall and outdoor marquees, perfect for showcasing vehicles.</p>
                            </div>
                            <a href="#" alt="Venue Details">
                                Venue Details
                                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                                    <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div class="map_place">
                        <div class="map_place-image">
                            <img src="<?php echo IMG; ?>/map/2.png">
                        </div>
                        <div class="map_place-content">
                            <h3>National Motorcycle Museum</h3>
                            <div class="content">
                                <p>World's largest British motorcycle museum with 1,000+ restored bikes provides unique backdrop for classic car auctions, attracting 250,000 annual visitors.</p>
                            </div>
                            <a href="#" alt="Venue Details">
                                Venue Details
                                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                                    <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div class="map_place">
                        <div class="map_place-image">
                            <img src="<?php echo IMG; ?>/map/3.png">
                        </div>
                        <div class="map_place-content">
                            <h3>Imperial War Museum</h3>
                            <div class="content">
                                <p>Europe's largest aviation museum hosts classic car auctions beneath historic aircraft, featuring Spitfires and Concorde, with modern facilities and easy M11 motorway access.</p>
                            </div>
                            <a href="#" alt="Venue Details">
                                Venue Details
                                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                                    <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div class="map_place">
                        <div class="map_place-image">
                            <img src="<?php echo IMG; ?>/map/4.png">
                        </div>
                        <div class="map_place-content">
                            <h3>Kelham Hall</h3>
                            <div class="content">
                                <p>Historic Victorian mansion with elegant rooms and grounds hosting H&H classic car auctions, featuring period architecture and modern auction facilities.</p>
                            </div>
                            <a href="#" alt="Venue Details">
                                Venue Details
                                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                                    <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div class="map_place">
                        <div class="map_place-image">
                            <img src="<?php echo IMG; ?>/map/5.png">
                        </div>
                        <div class="map_place-content">
                            <h3>UTAC Millbrook Proving Ground</h3>
                            <div class="content">
                                <p>An advanced automotive research facility featuring sophisticated testing equipment, technical laboratories, and precision measurement systems for comprehensive vehicle evaluations.</p>
                            </div>
                            <a href="#" alt="Venue Details">
                                Venue Details
                                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                                    <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>

<script>
    let pins = document.querySelectorAll('.pin'),
        interactive_map = document.querySelector('.interactive_map');

    if(pins){
        Array.from(pins).forEach(pin=>{
            pin.addEventListener('click', (e)=>{
                e.preventDefault();
                let id = e.currentTarget.getAttribute('data-pin-id');
                interactive_map.setAttribute('data-state', id)
            })
        })
    }
</script>