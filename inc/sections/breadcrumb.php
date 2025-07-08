<?php
$bg_image = get_field('breadcrumb_image');
?>
<section class="page_breadcrumb" style="background-image: url('<?php echo esc_url($bg_image); ?>');">
    <div class="container">
        <div class="page_breadcrumb_list">
            <a>HOMEPAGE</a> /
            <a>ABOUT</a> /
            <a>MEET THE TEAM</a>
        </div>
        <h1 class="page_breadcrumb_title"><?php the_title(); ?></h1>
    </div>
</section>