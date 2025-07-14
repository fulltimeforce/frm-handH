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
                            <div class="vehicle <?php echo $j == 0 ? 'active' : ''; ?>">
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
                                <div class="vehicle_title">
                                    <span>Classic Motorcycles</span>
                                    <h3>National Motorcycle Museum</h3>
                                </div>
                            </div>
                        </li>
                    <?php endfor; ?>
                    <li class="splide__slide">
                        <div class="vehicle <?php echo $j == 0 ? 'active' : ''; ?>">
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
                            <div class="vehicle_title">
                                <span>Classic Motorcycles</span>
                                <h3>National Motorcycle Museum</h3>
                            </div>
                        </div>
                        <div class="vehicle_final">
                            <h3>Stay tuned for more classic auctions to come</h3>
                            <img src="<?php echo IMG; ?>/path_car.svg">
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>