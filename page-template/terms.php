<?php
/*
  Template name: terms
*/

get_header();

get_banner(
    'Homepage / ' . get_the_title(),
    get_the_post_thumbnail_url(get_the_ID(), 'full'),
    ''
);
?>

<section>
    <div class="container terms_content">
        <?php
        while (have_posts()) : the_post();
            the_content();
        endwhile;
        ?>
    </div>
</section>

<?php get_footer(); ?>