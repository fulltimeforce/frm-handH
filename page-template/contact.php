<?php
/*
    Template name: contact
*/

get_header();

get_banner();

?>

<section class="upcoming">
    <?php get_template_part('inc/sections/upcoming'); ?>
</section>

<?php get_template_part('inc/sections/request-register'); ?>

<?php get_footer(); ?>