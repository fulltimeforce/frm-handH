<?php
/*
    Template name: our-showroom
*/

get_header();

get_banner('Homepage / Private Sales / Our Showroom', '', 'Our Showroom');

$title = get_field('title_os');
$description = get_field('description_os');
$link = get_field('link_os');
$gallery = get_field('gallery');

?>

<section class="private_sales">
    <div class="private_sales-container">
        <div class="private_sales-top">
            <div class="private_sales-title">
                <h2><?php echo $title; ?></h2>
            </div>
            <div class="private_sales-content">
                <div class="content">
                    <?php echo $description; ?>
                </div>

                <?php if (!empty($link)): ?>
                    <div class="actions">
                        <a href="<?php echo $link['url']; ?>" alt="<?php echo $link['title']; ?>" class="permalink_border">
                            <?php echo $link['title']; ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                                <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                            </svg>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php if (!empty($gallery)): ?>
            <div class="private_sales-images">
                <div class="private_sales-slider">
                    <div class="splide" role="group" id="images">
                        <div class="splide__track">
                            <ul class="splide__list">
                                <?php foreach ($gallery as $image): ?>
                                    <li class="splide__slide">
                                        <img src="<?php echo $image['url']; ?>" alt="<?php echo $image['title']; ?>">
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="private_sales-bottom">
            <div class="private_sales-bottom-col">
                <p class="head"><b>H&H Classics | Private Sales Showroom</b></p>
                <p>La Source <br>Churt Road <br>Hindhead <br>Surrey. GU26 6NL</p>
            </div>
            <div class="private_sales-bottom-col">
                <div>
                    <p class="head"><b>Viewing is strictly by appointment only.</b></p>
                    <ul>
                        <li>Email: <a href="mailto:private.sales@handh.co.uk" class="email">private.sales@handh.co.uk</a></li>
                        <li>Tel: <a href="tel:+44 (0)1428 607899" class="tel"><b>+44 (0)1428 607899</b></a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="head">John Markey</h3>
                    <p>Tel: <a href="tel:+44 (0)7943 584767" class="tel"><b>+44 (0)7943 584767</b></a></p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>