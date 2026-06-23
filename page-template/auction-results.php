<?php
/*
    Template name: auction-results
*/
get_header();

$banner_breadcrumb = get_field('ar_banner_breadcrumb') ?: 'Homepage / classic auctions / Auction Results';
$banner_title = get_field('ar_banner_title') ?: '';

get_banner(
    $banner_breadcrumb,
    get_the_post_thumbnail_url(get_the_ID(), 'full'),
    $banner_title
);
get_template_part('inc/sections/auction-results/result');
get_template_part('inc/sections/auction-results/vehicles');

get_footer(); ?>