<?php
/*
    Template name: auction-venues
*/

get_header();

get_banner('Homepage / Classic Auctions / Auction Venues', '', 'H&H Auction Venues 2025');

?>

<section class="big_video">
    <div class="big_video-container">
        <div class="video" data-state="1">
            <div class="video_poster">
                <button type="button" class="play_icon">
                    <svg width="120" height="120" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g id="playicon" opacity="0.95">
                            <rect id="triangle" width="120" height="120" rx="60" fill="white" />
                            <path id="circle" d="M59.6768 0.505859C64.719 0.459165 67.7263 0.692656 71.0059 1.38574L71.0186 1.38867C73.524 1.85282 77.4804 2.92419 79.749 3.75781L79.7578 3.76074C82.0291 4.54901 85.6117 6.08452 87.6582 7.10742H87.6592C89.6947 8.17182 93.0486 10.3147 95.0967 11.8047V11.8037C96.8886 13.1486 99.3249 15.1733 100.935 16.5938L101.57 17.1631C103.046 18.5964 105.736 21.7486 107.6 24.1719V24.1729C109.416 26.5956 111.791 30.2771 112.854 32.3105C113.918 34.3475 115.548 38.2145 116.431 40.9102C117.316 43.6125 118.388 47.7158 118.805 49.9873C119.174 52.2548 119.5 56.7677 119.5 59.9795C119.5 63.1909 119.174 67.703 118.805 69.9707C118.388 72.2421 117.316 76.3461 116.431 79.0488C115.548 81.7445 113.918 85.6115 112.854 87.6484C111.791 89.6818 109.416 93.3624 107.6 95.7852C105.732 98.2135 103.035 101.374 101.561 102.804C100.022 104.202 97.1354 106.624 95.0908 108.158C93.0427 109.648 89.6939 111.786 87.6602 112.85C85.614 113.873 82.03 115.41 79.7578 116.198L79.749 116.201C77.7638 116.931 74.4863 117.843 72.0195 118.37L71.0088 118.572C67.8647 119.22 64.6191 119.5 60 119.5C55.3814 119.5 52.1353 119.22 48.9434 118.572L48.9365 118.571L47.9502 118.37C45.514 117.843 42.2362 116.931 40.251 116.201L40.2393 116.197L39.3184 115.869C37.0623 115.034 34.1307 113.746 32.3389 112.851C30.305 111.787 26.9571 109.648 24.9092 108.158C22.8643 106.624 19.9771 104.202 18.4385 102.804C16.964 101.374 14.2669 98.2133 12.3994 95.7852H12.4004C10.584 93.3624 8.20909 89.6818 7.14648 87.6484C6.0821 85.6115 4.45209 81.7445 3.56934 79.0488C2.68427 76.3461 1.61138 72.242 1.19434 69.9707C0.825404 67.7029 0.500004 63.1908 0.5 59.9795C0.5 56.7678 0.825346 52.2548 1.19434 49.9873L1.19531 49.9883C1.61235 47.717 2.68428 43.6129 3.56934 40.9102C4.45209 38.2145 6.0821 34.3475 7.14648 32.3105C8.20908 30.2771 10.5839 26.5956 12.4004 24.1729L12.3994 24.1719C14.2633 21.7485 16.9536 18.5964 18.4297 17.1631C19.9647 15.7671 22.8541 13.3409 24.9023 11.8037L24.9033 11.8047C26.6955 10.5008 29.4879 8.69726 31.5215 7.55371L32.3408 7.10742C34.3891 6.08351 37.9245 4.54899 40.2393 3.76172L40.251 3.75781C42.5162 2.92541 46.3291 1.89978 48.6162 1.38574L48.6172 1.38672C51.5247 0.786582 54.8157 0.505859 59.625 0.505859H59.6768ZM47.2969 34.627C46.9357 34.627 46.5522 34.8115 46.2461 35.0156C45.9216 35.232 45.5972 35.5282 45.3496 35.8584C45.1037 36.1864 44.9486 36.6072 44.8359 37.3232C44.7239 38.0359 44.6466 39.0947 44.5938 40.7383C44.4879 44.0313 44.4766 49.7621 44.5 59.9805C44.5234 69.7073 44.5583 75.4363 44.6641 78.8447C44.7169 80.5467 44.7883 81.6866 44.8867 82.4619C44.9809 83.2037 45.1075 83.6858 45.3242 84.0146V84.0156C45.5519 84.3855 45.8718 84.7034 46.1953 84.9307C46.5024 85.1463 46.8869 85.332 47.25 85.332C47.35 85.332 47.4371 85.3037 47.4727 85.292C47.5227 85.2755 47.5753 85.2539 47.627 85.2314C47.731 85.1862 47.8606 85.1232 48.0098 85.0459C48.31 84.8904 48.7183 84.6639 49.2178 84.377C50.2181 83.8022 51.6052 82.9728 53.2764 81.9521C56.6199 79.9101 61.1113 77.0951 65.9414 73.999L65.9434 73.998C70.7721 70.8794 75.2614 67.9596 78.6025 65.7666C80.273 64.6702 81.6577 63.755 82.6543 63.0869C83.1523 62.7531 83.555 62.4798 83.8486 62.2764C84.1266 62.0838 84.3405 61.931 84.4287 61.8516C84.7114 61.597 84.9548 61.2689 85.1289 60.9541C85.2939 60.6558 85.4375 60.2954 85.4375 59.9795C85.4375 59.6635 85.2939 59.3032 85.1289 59.0049C84.9548 58.6901 84.7114 58.362 84.4287 58.1074C84.3405 58.028 84.1266 57.8752 83.8486 57.6826C83.555 57.4792 83.1523 57.2059 82.6543 56.8721C81.6577 56.204 80.273 55.2888 78.6025 54.1924C75.2614 51.9994 70.7721 49.0796 65.9434 45.9609L65.9414 45.96C61.1111 42.8638 56.6197 40.0488 53.2822 38.0068C51.6139 36.9861 50.2315 36.1565 49.2383 35.582C48.7424 35.2952 48.3388 35.0692 48.0439 34.9141C47.8976 34.837 47.7713 34.7744 47.6709 34.7295C47.6212 34.7073 47.5705 34.6855 47.5225 34.6689C47.4997 34.6611 47.4429 34.6412 47.3721 34.6318L47.2969 34.627Z" fill="#8C6E47" stroke="#8C6E47" />
                        </g>
                    </svg>
                </button>
                <img src="<?php echo IMG; ?>/auction-venues-poster.png" class="d-block w-100">
            </div>
            <div class="video_src">
                <video controls playsinline poster="<?php echo IMG; ?>/auction-venues-poster.png">
                    <source src="<?php echo IMG; ?>/auction-venues.mp4">
                </video>
            </div>
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
                            <a href="<?php echo esc_url(home_url('pavilion-gardens')); ?>" alt="Venue Details">
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
                            <a href="<?php echo esc_url(home_url('national-motorcycle-museum')); ?>" alt="Venue Details">
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
                            <a href="<?php echo esc_url(home_url('the-imperial-war-museum')); ?>" alt="Venue Details">
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
                            <a href="<?php echo esc_url(home_url('kelham-hall')); ?>" alt="Venue Details">
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
                            <a href="<?php echo esc_url(home_url('utac-millbrook-proving-ground')); ?>" alt="Venue Details">
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
    document.addEventListener("DOMContentLoaded", () => {
        const videoDiv = document.querySelector(".video");

        if (videoDiv) {
            videoDiv.addEventListener("click", () => {
                const currentState = videoDiv.getAttribute("data-state");
                videoDiv.setAttribute("data-state", "2");

                /*if (currentState === "1") {
                    videoDiv.setAttribute("data-state", "2");
                } else {
                    videoDiv.setAttribute("data-state", "1");

                    let video = videoDiv.querySelector('video')
                    video.pause()
                    video.muted = false;
                }*/
            });
        }
    });
</script>

<script>
    let pins = document.querySelectorAll('.pin'),
        interactive_map = document.querySelector('.interactive_map');

    if (pins) {
        Array.from(pins).forEach(pin => {
            pin.addEventListener('click', (e) => {
                e.preventDefault();
                let id = e.currentTarget.getAttribute('data-pin-id');
                interactive_map.setAttribute('data-state', id)
            })
        })
    }
</script>