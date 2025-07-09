<?php
/*
    Template name: home
*/

get_header();

?>

<main class="hero relative">
    <div class="video__bg">
        <video autoplay playsinline muted loop>
            <source src="<?php echo IMG; ?>/HHH.mp4">
        </video>
    </div>
    <div class="container">
        <div class="hero_content">
            <div class="breadlines">
                <p>Est. 1993</p>
            </div>
            <h1><span>Auctioneers of classic</span> motorcars & motorcycles</h1>
            <div class="hero_actions">
                <a href="">Register to Bid</a>
                <a href="">Upcoming Auctions</a>
            </div>
        </div>
    </div>
</main>

<section class="our_successes">
    <div class="container">
        <div class="our_successes-head">
            <div class="breadlines">
                <p>What Sets Us Apart</p>
            </div>
            <h2>Our Successes</h2>
            <p>H&H Classics has been selling classic & collector motorcars and motorcycles for longer than any other auction house in the UK and Europe. We sell with a passion and enthusiasm that sets us apart.</p>
        </div>
    </div>
    <div class="our_successes-body">
        <div class="w-100">
            <div class="splide" role="group" id="text1">
                <div class="splide__track">
                    <ul class="splide__list">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <li class="splide__slide">
                                <h3>2024 Motorcar Highlights</h3>
                            </li>
                            <li class="splide__slide">
                                <h3>•</h3>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </div>
            </div>
            <div class="splide" role="group" id="cars1">
                <div class="splide__track">
                    <ul class="splide__list">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <li class="splide__slide">
                                <div class="car_card">
                                    <div class="car_card-flex">
                                        <div class="car_card-image">
                                            <div class="car_card-thumb">
                                                <img src="<?php echo IMG; ?>/car.png">

                                                <div class="permalink">
                                                    <a href="#">View</a>
                                                </div>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="42" height="30" viewBox="0 0 42 30" fill="none">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M42.2512 0C24.5511 4.20263 9.49137 15.2238 0 30.1354H42.2512V0Z" fill="#D3C7B6" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="car_card-info">
                                            <div class="car_card-content">
                                                <p>19th Jun, 2024</p>
                                                <h3>1904 Bayard Type AC2K Twin-Cylinder 9/11hp Rear Entrance Tonneau</h3>
                                            </div>
                                            <div class="car_card-price">
                                                <h4>
                                                    <span>Sold for</span>
                                                    £128,250
                                                </h4>
                                                <p>(including buyers premium)</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="w-100">
            <div class="splide" role="group" id="text2">
                <div class="splide__track">
                    <ul class="splide__list">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <li class="splide__slide">
                                <h3>2024 Motorcar Highlights</h3>
                            </li>
                            <li class="splide__slide">
                                <h3>•</h3>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </div>
            </div>
            <div class="splide" role="group" id="cars2">
                <div class="splide__track">
                    <ul class="splide__list">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <li class="splide__slide">
                                <div class="car_card">
                                    <div class="car_card-flex">
                                        <div class="car_card-image">
                                            <div class="car_card-thumb">
                                                <img src="<?php echo IMG; ?>/car.png">

                                                <div class="permalink">
                                                    <a href="#">View</a>
                                                </div>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="42" height="30" viewBox="0 0 42 30" fill="none">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M42.2512 0C24.5511 4.20263 9.49137 15.2238 0 30.1354H42.2512V0Z" fill="#D3C7B6" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="car_card-info">
                                            <div class="car_card-content">
                                                <p>19th Jun, 2024</p>
                                                <h3>1904 Bayard Type AC2K Twin-Cylinder 9/11hp Rear Entrance Tonneau</h3>
                                            </div>
                                            <div class="car_card-price">
                                                <h4>
                                                    <span>Sold for</span>
                                                    £128,250
                                                </h4>
                                                <p>(including buyers premium)</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="discover">
    <div class="container">
        <div class="discover_head">
            <div class="breadlines">
                <p>Discover</p>
            </div>
            <h2>Vehicles For Sale</h2>
        </div>
        <div class="discover_body">
            <div class="vehicles_grid">
                <div class="vehicle_card">
                    <div class="vehicle_card-image">
                        <img src="<?php echo IMG; ?>/car.png">
                    </div>
                    <div class="vehicle_card-info">
                        <div class="vehicle_card-content">
                            <h3>1958 Mercedes Benz 190 SL</h3>
                        </div>
                        <div class="vehicle_card-price">
                            <h4>
                                <span>Estimated at</span>
                                £70,000 - £90,000
                            </h4>
                            <ul>
                                <li><b>Registration No:</b> 551 XWD</li>
                                <li><b>Chassis No:</b> A1210428501142</li>
                                <li><b>MOT:</b> July 2025</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php get_template_part('inc/section/cta.php'); ?>

<?php get_footer(); ?>