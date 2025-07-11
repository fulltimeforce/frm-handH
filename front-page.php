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

<section class="upcoming">
    <div class="container_side">
        <div class="upcoming_head">
            <div class="breadlines">
                <p>Explore</p>
            </div>
            <h2>Upcoming Auctions</h2>
        </div>
        <div class="upcoming_body">
            <div class="splide" role="group" id="upcoming">
                <div class="splide__arrows">
                    <button class="splide__arrow splide__arrow--prev">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="26" viewBox="0 0 50 26" fill="none">
                            <path d="M0 13H48M48 13L36 1M48 13L36 25" stroke="#8C6E47" stroke-width="2" />
                        </svg>
                    </button>
                    <button class="splide__arrow splide__arrow--next">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="26" viewBox="0 0 50 26" fill="none">
                            <path d="M0 13H48M48 13L36 1M48 13L36 25" stroke="#8C6E47" stroke-width="2" />
                        </svg>
                    </button>
                </div>
                <div class="splide__track">
                    <ul class="splide__list">
                        <?php for ($j = 0; $j < 5; $j++): ?>
                            <li class="splide__slide">
                                <div class="vehicle">
                                    <img src="<?php echo IMG; ?>/logo.png" class="vehicle-logo">
                                    <div class="vehicle_bg">
                                        <img src="<?php echo IMG; ?>/4.png">
                                    </div>
                                    <div class="w-100 vehicle_bottom">
                                        <div class="w-100 vehicle_content">
                                            <div class="vehicle-info">
                                                <h2>
                                                    <span>Classic Motorcars</span>
                                                    Pavilion Gardens
                                                </h2>
                                                <ul>
                                                    <li>Date: 12th Feb, 2025 - 9:00 am</li>
                                                    <li>Location: St John's Rd, Buxton SK17 6BE</li>
                                                </ul>
                                                <div class="flex">
                                                    <a href="#">View Now</a>
                                                    <a href="#">Venue Details</a>
                                                    <a href="#">Send me a reminder</a>
                                                </div>
                                                <div class="lots_live">
                                                    <span class="dot"></span>
                                                    <p>Lots Live (8)</p>
                                                </div>
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
    <div class="container">
        <div class="upcoming_foot">
            <div>
                <p>Bidding is available live at all our auction venues, online through our website, or by telephone and commission. Parking and entry into the auction is free for auction attendees with a catalogue.</p>
            </div>
            <a href="#" class="permalink" alt="View All Vehicles">
                View All Vehicles
                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                    <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                </svg>
            </a>
        </div>
        <a href="#" class="permalink_border">
            View All Auction Venues
            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
            </svg>
        </a>
    </div>
</section>

<section class="banner_car">
    <video autoplay loop muted>
        <source src="<?php echo IMG; ?>/banner-car.mp4">
    </video>
</section>

<section class="why_choose_us">
    <div class="container">
        <div class="why_choose_us-info">
            <div class="content">
                <div class="breadlines">
                    <p>Why Choose H&H Classics</p>
                </div>
                <h2>The very best chance of selling successfully</h2>
                <p>Over 30 years of record-breaking sales, expert valuations, and unmatched passion for classic and collector vehicles.</p>
            </div>
            <div class="image">
                <img src="<?php echo IMG; ?>/5.png">
            </div>
        </div>
        <div class="why_choose_us-stats">
            <div>
                <h3>$11M+</h3>
                <p>Classic Car Sold</p>
            </div>
            <div>
                <h3>83%</h3>
                <p>sell-through rate</p>
            </div>
            <div>
                <h3>70,000+</h3>
                <p>Global Clients</p>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="upcoming_foot">
            <div>
                <p>Founded by Simon Hope and Mark Hamilton in 1993 H&H Classics has been continuously trading longer than any other UK or European auction house. In fact, you could say that we’ve reached classic status ourselves.</p>
            </div>
            <a href="#" class="permalink" alt="View All Vehicles">
                Purchase A Catalogue
                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                    <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                </svg>
            </a>
        </div>
        <a href="#" class="permalink_border">
            Learn More About H&H Classics
            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
            </svg>
        </a>
    </div>
</section>

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
                <?php for ($i = 0; $i < 5; $i++): ?>
                    <div class="vehicle_card">
                        <div class="vehicle_card-image">
                            <div class="splide vehicle_card-thumbs" role="group">
                                <div class="splide__arrows">
                                    <button class="splide__arrow splide__arrow--prev">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="14" viewBox="0 0 13 14" fill="none">
                                            <path d="M0 7H12M12 7L6 1M12 7L6 13" stroke="black" />
                                        </svg>
                                    </button>
                                    <button class="splide__arrow splide__arrow--next">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="14" viewBox="0 0 13 14" fill="none">
                                            <path d="M0 7H12M12 7L6 1M12 7L6 13" stroke="black" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="splide__track">
                                    <ul class="splide__list">
                                        <li class="splide__slide">
                                            <img src="<?php echo IMG; ?>/car.png">
                                        </li>
                                        <li class="splide__slide">
                                            <img src="<?php echo IMG; ?>/car2.png">
                                        </li>
                                        <li class="splide__slide">
                                            <img src="<?php echo IMG; ?>/car3.png">
                                        </li>
                                    </ul>
                                </div>
                            </div>
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
                <?php endfor; ?>
            </div>
            <a href="#" class="permalink" alt="View All Vehicles">
                View All Vehicles
                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                    <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                </svg>
            </a>
        </div>
    </div>
</section>

<section class="shadows">
    <div class="shadows_grid">
        <div class="col">
            <div class="col_head">
                <div class="col_title">
                    <h3>Request your free valuation</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                        <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="white" />
                    </svg>
                </div>
                <div class="col_description">
                    <p>Get a complimentary valuation to consign your Classic Motorcar, Motorbike or Scooter.</p>
                </div>
            </div>
            <a class="permalink" href="#">Get a <br>Valuation</a>
        </div>
        <div class="col">
            <div class="col_head">
                <div class="col_title">
                    <h3>Register to bid in our auctions</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                        <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="white" />
                    </svg>
                </div>
                <div class="col_description">
                    <p>Registering to bid is quick, easy & and safe using our secure online bidding platform.</p>
                </div>
            </div>
            <a class="permalink" href="#">Register <br>Now</a>
        </div>
        <div class="col">
            <div class="col_head">
                <div class="col_title">
                    <h3>Private sale and source service</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                        <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="white" />
                    </svg>
                </div>
                <div class="col_description">
                    <p>We look after the clients’ interests, despite our auctioning services - we also offer private sales.</p>
                </div>
            </div>
            <a class="permalink" href="#">Book an <br>Appointment</a>
        </div>
    </div>
</section>

<section class="clients">
    <div class="container_side">
        <div class="clients_head">
            <div class="breadlines">
                <p>Testimonials</p>
            </div>
            <h2>From Our Clients</h2>
        </div>
        <div class="clients_body">
            <div class="splide" role="group" id="clients">
                <div class="splide__arrows">
                    <button class="splide__arrow splide__arrow--prev">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="26" viewBox="0 0 50 26" fill="none">
                            <path d="M0 13H48M48 13L36 1M48 13L36 25" stroke="#8C6E47" stroke-width="2" />
                        </svg>
                    </button>
                    <button class="splide__arrow splide__arrow--next">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="26" viewBox="0 0 50 26" fill="none">
                            <path d="M0 13H48M48 13L36 1M48 13L36 25" stroke="#8C6E47" stroke-width="2" />
                        </svg>
                    </button>
                </div>
                <div class="splide__track">
                    <ul class="splide__list">
                        <?php for ($j = 0; $j < 5; $j++): ?>
                            <li class="splide__slide">
                                <div class="comment">
                                    <div class="comment_info">
                                        <div class="stars">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                                <path d="M8.76224 0.731762C8.83707 0.501435 9.16293 0.501435 9.23776 0.731763L11.189 6.73708C11.2225 6.84009 11.3185 6.90983 11.4268 6.90983H17.7411C17.9833 6.90983 18.084 7.21973 17.8881 7.36208L12.7797 11.0736C12.692 11.1372 12.6554 11.2501 12.6888 11.3531L14.6401 17.3584C14.7149 17.5887 14.4513 17.7803 14.2554 17.6379L9.14695 13.9264C9.05932 13.8628 8.94068 13.8628 8.85305 13.9264L3.74462 17.6379C3.54869 17.7803 3.28507 17.5887 3.35991 17.3584L5.31116 11.3531C5.34463 11.2501 5.30796 11.1372 5.22034 11.0736L0.11191 7.36208C-0.0840186 7.21973 0.0166752 6.90983 0.258856 6.90983H6.57322C6.68153 6.90983 6.77752 6.84009 6.81099 6.73708L8.76224 0.731762Z" fill="#8C6E47" />
                                            </svg>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                                <path d="M8.76224 0.731762C8.83707 0.501435 9.16293 0.501435 9.23776 0.731763L11.189 6.73708C11.2225 6.84009 11.3185 6.90983 11.4268 6.90983H17.7411C17.9833 6.90983 18.084 7.21973 17.8881 7.36208L12.7797 11.0736C12.692 11.1372 12.6554 11.2501 12.6888 11.3531L14.6401 17.3584C14.7149 17.5887 14.4513 17.7803 14.2554 17.6379L9.14695 13.9264C9.05932 13.8628 8.94068 13.8628 8.85305 13.9264L3.74462 17.6379C3.54869 17.7803 3.28507 17.5887 3.35991 17.3584L5.31116 11.3531C5.34463 11.2501 5.30796 11.1372 5.22034 11.0736L0.11191 7.36208C-0.0840186 7.21973 0.0166752 6.90983 0.258856 6.90983H6.57322C6.68153 6.90983 6.77752 6.84009 6.81099 6.73708L8.76224 0.731762Z" fill="#8C6E47" />
                                            </svg>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                                <path d="M8.76224 0.731762C8.83707 0.501435 9.16293 0.501435 9.23776 0.731763L11.189 6.73708C11.2225 6.84009 11.3185 6.90983 11.4268 6.90983H17.7411C17.9833 6.90983 18.084 7.21973 17.8881 7.36208L12.7797 11.0736C12.692 11.1372 12.6554 11.2501 12.6888 11.3531L14.6401 17.3584C14.7149 17.5887 14.4513 17.7803 14.2554 17.6379L9.14695 13.9264C9.05932 13.8628 8.94068 13.8628 8.85305 13.9264L3.74462 17.6379C3.54869 17.7803 3.28507 17.5887 3.35991 17.3584L5.31116 11.3531C5.34463 11.2501 5.30796 11.1372 5.22034 11.0736L0.11191 7.36208C-0.0840186 7.21973 0.0166752 6.90983 0.258856 6.90983H6.57322C6.68153 6.90983 6.77752 6.84009 6.81099 6.73708L8.76224 0.731762Z" fill="#8C6E47" />
                                            </svg>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                                <path d="M8.76224 0.731762C8.83707 0.501435 9.16293 0.501435 9.23776 0.731763L11.189 6.73708C11.2225 6.84009 11.3185 6.90983 11.4268 6.90983H17.7411C17.9833 6.90983 18.084 7.21973 17.8881 7.36208L12.7797 11.0736C12.692 11.1372 12.6554 11.2501 12.6888 11.3531L14.6401 17.3584C14.7149 17.5887 14.4513 17.7803 14.2554 17.6379L9.14695 13.9264C9.05932 13.8628 8.94068 13.8628 8.85305 13.9264L3.74462 17.6379C3.54869 17.7803 3.28507 17.5887 3.35991 17.3584L5.31116 11.3531C5.34463 11.2501 5.30796 11.1372 5.22034 11.0736L0.11191 7.36208C-0.0840186 7.21973 0.0166752 6.90983 0.258856 6.90983H6.57322C6.68153 6.90983 6.77752 6.84009 6.81099 6.73708L8.76224 0.731762Z" fill="#8C6E47" />
                                            </svg>
                                        </div>
                                        <div class="comment_title">
                                            <h3>Absolutely excellent from start to finish</h3>
                                        </div>
                                        <div class="comment_description">
                                            <p>“Absolutely excellent from start to finish. Lucas Gomershall guided us through the whole process. From the cars being collected to the actual auction, when the cars sold well, could not have gone more smoothly.”</p>
                                        </div>
                                    </div>
                                    <div class="comment_author">
                                        <div class="comment_photo">JL</div>
                                        <span>Andrew Cooper</span>
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

<section class="celebrating">
    <div class="celebrating_container">
        <div class="celebrating_info">
            <div class="content">
                <div class="breadlines">
                    <p>Classic Auction Experience</p>
                </div>
                <h2><span>Celebrating</span> Over 30 Years</h2>
                <p>H&H are trusted by Classic Car and Motorcycle enthusiasts for over 30 years. H&H are Europe's largest established expert auction house of our kind.</p>
                <a href="#" class="permalink_border">
                    Learn More About The Team
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                        <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                    </svg>
                </a>
            </div>
            <div class="celebrating_info-thumb">
                <div class="image">
                    <a href="#" class="permalink">Learn More</a>
                    <img src="<?php echo IMG; ?>/vector3.svg" class="image-vector">
                    <img src="<?php echo IMG; ?>/3.jpg" class="image-thumb">
                </div>
            </div>
        </div>
        <div class="celebrating-subtitles">
            <div>
                <div class="breadlines">
                    <p>Classic Auction Experience</p>
                </div>
                <h3>H&H Classic Auctions</h3>
            </div>
            <div>
                <div class="breadlines">
                    <p>Warrington 2024</p>
                </div>
                <h3>Voted best auction house</h3>
            </div>
        </div>
        <div class="celebrating-images">
            <div class="image">
                <a href="#" class="permalink">Learn More</a>
                <img src="<?php echo IMG; ?>/vector2.svg" class="image-vector">
                <img src="<?php echo IMG; ?>/1.jpg" class="image-thumb">
            </div>
            <div class="image">
                <a href="#" class="permalink">Learn More</a>
                <img src="<?php echo IMG; ?>/vector1.svg" class="image-vector">
                <img src="<?php echo IMG; ?>/2.png" class="image-thumb">
            </div>
        </div>
        <div class="celebrating-descriptions">
            <div>
                <p>H&H Classic Auctions are fabulous events where car and bike aficionados can browse and bid in relaxed environments alongside like-minded enthusiasts.</p>
            </div>
            <div>
                <p>H&H Classics - Classic Car & Motorbikes was awarded The Best Auction House in Warrington for 2024. An overall quality score exceeding 95% was achieved, making them the top ranked in Warrington.</p>
            </div>
        </div>
    </div>
</section>

<?php get_template_part('inc/section/cta.php'); ?>

<?php get_footer(); ?>