<?php
$heritage_title = get_field('heritage_title');
$heritage_htitle = get_field('heritage_high_title');
$heritage_text = get_field('heritage_text');
$heritage_btn1 = get_field('heritage_button1');
$heritage_btn2 = get_field('heritage_button2');
$button_top_text = get_field('button_top_text');
$button_bottom_text = get_field('button_bottom_text');
?>
<section class="heritage">
    <div class="heritage_container">
        <div class="tabs">
            <a href="#our-heritage" class="active">Our Heritage</a>
            <a href="#timelines">Timeline</a>
            <a href="#specialists">Our Specialists</a>
            <a href="#private-sales">Private Sales</a>
            <a href="#upcoming-auctions">Upcoming Auctions</a>
        </div>
        <div class="heritage_information" id="our-heritage">
            <div class="heritage_images">
                <img class="heritage_images-main" src="<?php echo IMG; ?>/about/4.png">
                <div class="heritage_images-slider">
                    <div id="heritage" class="splide">
                        <div class="splide__track">
                            <?php if (have_rows('heritage_images')): ?>
                                <ul class="splide__list">
                                    <?php while (have_rows('heritage_images')): the_row();
                                        $image = get_sub_field('heritage_image');
                                    ?>
                                        <?php if ($image): ?>
                                            <li class="splide__slide">
                                                <img src="<?php echo esc_url($image); ?>" alt="Heritage image">
                                            </li>
                                        <?php endif; ?>
                                    <?php endwhile; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="image_progress">
                        <div class="progress"></div>
                    </div>
                </div>
            </div>
            <div class="heritage_content">
                <div class="breadlines">
                    <p>Classic Vehicle Auctions</p>
                </div>
                <?php if ($heritage_title) : ?>
                    <h2><span><?php echo $heritage_htitle; ?></span><?php echo $heritage_title; ?></h2>
                <?php endif; ?>
                <?php if ($heritage_text) : ?>
                    <div class="content">
                        <?php echo $heritage_text; ?>
                    </div>
                <?php endif; ?>
                <div class="actions">
                    <?php if($heritage_btn1 && !empty($button_top_text)): ?>
                    <a href="<?php echo esc_url($heritage_btn1); ?>" class="permalink_border">
                        <?php echo $button_top_text; ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                            <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                        </svg>
                    </a>
                    <?php endif; ?>
                    
                    <?php if($heritage_btn2 && !empty($button_bottom_text)): ?>
                    <a href="<?php echo esc_url($heritage_btn2); ?>" class="permalink_border">
                        <?php echo $button_bottom_text; ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                            <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                        </svg>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>