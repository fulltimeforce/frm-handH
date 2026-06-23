<?php
$titleb = get_field('title_private_sales');
$descriptionb = get_field('description_private_sales');
$linkb = get_field('link_private_sales');
$textb = get_field('text_private_sales');
$imagesb = get_field('images_pv');
?>

<section class="tailored" id="private-sales">
    <div class="tailored_container">
        <div class="tailored-flex">
            <div class="tailored_info">
                <div class="tailored_info-box">
                    <div class="tailored_info-box-ss">
                        <div class="breadlines">
                            <p>Tailored for Every Client</p>
                        </div>
                        <?php if (!empty($titleb)): ?>
                            <h2><?php echo $titleb; ?></h2>
                        <?php endif; ?>

                        <?php if (!empty($descriptionb)): ?>
                            <div class="tailored_info-content">
                                <?php echo $descriptionb; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($linkb) && !empty($textb)): ?>
                            <a href="<?php echo $linkb; ?>" class="permalink_border">
                                <?php echo $textb; ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                                    <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>

                </div>
            </div>


            <div class="tailored_images">
                <div class="imagelider-wrapper">
                    <div class="imagelider">
                        <?php if (have_rows('images_pv')): ?>
                            <?php while (have_rows('images_pv')): the_row(); ?>
                                <?php
                                $imagepv = get_sub_field('image_pv');
                                if ($imagepv):
                                ?>
                                    <div class="slide">
                                        <img src="<?php echo esc_url($imagepv['url']); ?>" alt="<?php echo esc_attr($imagepv['alt']); ?>">
                                    </div>
                                <?php endif; ?>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="imagelider-slide">
                    <div class="splide" id="mobile-slide">
                        <div class="splide__track">
                            <ul class="splide__list">
                                <?php if (have_rows('images_pv')): ?>
                                    <?php while (have_rows('images_pv')): the_row(); ?>
                                        <?php
                                        $imagepv = get_sub_field('image_pv');
                                        if ($imagepv):
                                        ?>
                                            <li class="splide__slide">
                                                <img src="<?php echo esc_url($imagepv['url']); ?>" alt="<?php echo esc_attr($imagepv['alt']); ?>">
                                            </li>
                                        <?php endif; ?>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>