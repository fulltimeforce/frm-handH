<?php
/*
    Template name: about
*/
get_header();

$banner_breadcrumb = get_field('banner_breadcrumb') ?: 'Homepage / About / About H&H Classics';
$banner_title = get_field('banner_title') ?: '';

get_banner(
    $banner_breadcrumb,
    get_the_post_thumbnail_url(get_the_ID(), 'full'),
    $banner_title
);
get_template_part('inc/sections/about/heritage');
get_template_part('inc/sections/about/timeline-section');
get_template_part('inc/sections/about/specialists');
get_template_part('inc/sections/cta');
get_template_part('inc/sections/about/private-sales');
get_template_part('inc/sections/about/upcoming-auctions');

get_footer();