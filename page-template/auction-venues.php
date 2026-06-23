<?php
/*
    Template name: auction-venues
*/

get_header();

$banner_breadcrumb = get_field('av_banner_breadcrumb') ?: 'Homepage / Classic Auctions / Auction Venues';
$banner_title_field = get_field('av_banner_title');
$banner_title = $banner_title_field ? $banner_title_field . ' ' . date('Y') : '';
get_banner(
    $banner_breadcrumb,
    get_the_post_thumbnail_url(get_the_ID(), 'full'),
    $banner_title
);

// Big Video Section
get_template_part('inc/sections/auction-venues/big-video');

// Auction Content Section
get_template_part('inc/sections/auction-venues/auction-content');

// Interactive Map Section
get_template_part('inc/sections/auction-venues/interactive-map');

get_footer(); ?>