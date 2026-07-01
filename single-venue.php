<?php

get_header();

$bg_image = get_field('banner_venue-auction');
$subtitle = get_field('pavilion_hero_subtitle');
$text = get_field('pavilion_hero_text');

$title = get_field('title_banner_venue-auction');
if (empty($title)) {
    $title = get_the_title();
}

get_banner('Homepage / Classic Auctions / ' . $title, esc_url($bg_image), $title);

?>

<div class="pavilion_page">
    <div class="container">
        <section id="pavilionGardens" class="custom-carousel">
            <div id="pavilionSlider" class="splide w-100">
                <div class="splide__track">
                    <ul class="splide__list">
                        <?php if (have_rows('pavilion_slider')): ?>
                            <?php while (have_rows('pavilion_slider')): the_row();
                                $image = get_sub_field('pavilion_slider_image');
                                if ($image): ?>
                                    <li class="splide__slide">
                                        <div class="slide-wrapper">
                                            <div class="slide-image-container">
                                                <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>">
                                            </div>
                                            <!-- Progress bar -->
                                            <div class="my-progress">
                                                <div class="my-progress-bar"></div>
                                            </div>
                                        </div>
                                    </li>
                                <?php endif; ?>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="splide__arrows custom-arrows">
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
            </div>

            <?php
            $slider_subtitle = get_field('slider_subtitle');
            $slider_event_date = get_field('slider_event_date');
            $slider_description = get_field('slider_description');
            if ($slider_subtitle): ?>
                <h2><?= esc_html($slider_subtitle); ?></h2>
            <?php endif; ?>
            <?php
            if ($slider_event_date): ?>
                <p class="slider_event_date"><?= esc_html($slider_event_date); ?></p>
            <?php endif; ?>
            <?php
            if ($slider_description): ?>
                <div class="slider_event_date slider_event_date--description"><?= wp_kses_post($slider_description); ?></div>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php get_template_part('inc/parts/SingleVenueAuction'); ?>

<?php get_footer(); ?>

<?php
$lat = get_field('lat');
$lng = get_field('lng');
?>

<?php if (!empty($lat) && !empty($lng)): ?>
    <script src="<?php echo URL ?>/map.js"></script>
    <script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>
    <script>
        var locations = [{
            lat: <?php echo $lat; ?>,
            lng: <?php echo $lng; ?>
        }];

        async function initMap() {

            const {
                Map,
                InfoWindow
            } = await google.maps.importLibrary("maps");
            const {
                AdvancedMarkerElement
            } = await google.maps.importLibrary("marker");

            const position = {
                lat: <?php echo $lat; ?>,
                lng: <?php echo $lng; ?>
            };

            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 16,
                center: position,
                mapId: "efa54eadc0c22b2f",
                mapTypeControl: false,
                zoomControl: false,
                streetViewControl: false,
                fullscreenControl: false,
                rotateControl: false,
                scaleControl: false,
                panControl: false,
                clickableIcons: false,
                disableDefaultUI: true
            });

            const updateMarkerSize = () => {
                const isLargeScreen = window.matchMedia("(min-width: 1200px)").matches;
                return {
                    width: isLargeScreen ? "7.118vw" : "82.5px",
                    height: isLargeScreen ? "8.333vw" : "100px",
                };
            };

            const markers = locations.map((position, i) => {
                const markerElement = document.createElement("div");
                const markerSize = updateMarkerSize();
                markerElement.style.width = '48px';
                markerElement.style.height = '70px';
                markerElement.classList.add('markerElement');

                markerElement.innerHTML = '<img src="<?php echo IMG; ?>/Location-Pin.svg">';

                const marker = new google.maps.marker.AdvancedMarkerElement({
                    position,
                    content: markerElement,
                });

                return marker;
            });

            const markerCluster = new markerClusterer.MarkerClusterer({
                markers,
                map
            });

            window.addEventListener("resize", () => {
                markers.forEach((marker) => {
                    const markerSize = updateMarkerSize();
                    marker.content.style.width = markerSize.width;
                    marker.content.style.height = markerSize.height;
                });
            });
        }
        window.onload = initMap;
    </script>
<?php endif; ?>
