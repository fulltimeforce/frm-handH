<?php
/*
    Template name: home
*/
get_header();

//set fields
$why_video = get_field('whychoose_video');
?>


<?php get_template_part('inc/sections/front-page/hero'); ?>

<section class="upcoming" id="upcoming-auctions">
    <?php get_template_part('inc/sections/upcoming'); ?>
</section>

<?php get_template_part('inc/sections/front-page/banner-car'); ?>

<?php get_template_part("inc/sections/front-page/why-choose-us") ?>

<?php get_template_part('inc/sections/front-page/animated-text'); ?>

<?php get_template_part('inc/sections/front-page/our-successes'); ?>

<?php get_template_part("inc/sections/front-page/discover"); ?>

<?php get_template_part('inc/sections/request-register'); ?>

<?php get_template_part('inc/sections/front-page/clients'); ?>

<?php get_template_part('inc/sections/celebrating'); ?>

<?php get_template_part('inc/sections/events'); ?>

<?php get_template_part("inc/sections/front-page/featured-articles"); ?>

<?php get_footer(); ?>

<?php if ($why_video && !empty(get_field('whychoose_poster_video'))): ?>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            setTimeout(function () {
                const banner = document.querySelector(".banner_car");
                if (banner) {
                    banner.innerHTML = `
                <video autoplay muted loop playsinline>
                    <source src="<?php echo $why_video; ?>" type="video/mp4">
                    Your browser does not support the video.
                </video>
            `;
                }
            }, 1000); // 1 segundo
        });
    </script>
<?php endif; ?>