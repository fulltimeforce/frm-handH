<?php
/*
    Template name: search-make-and-model
*/
get_header();
$banner_title = get_field('smm_banner_title');
get_centered_banner('', $banner_title);
get_template_part('inc/sections/search-make-and-model/searched');
// get_template_part('inc/sections/search-make-and-model/header');
get_template_part('inc/sections/search-make-and-model/cta');
?>
<section class="upcoming pb160">
    <?php get_template_part('inc/sections/upcoming'); ?>
</section>
<?php get_footer(); ?>