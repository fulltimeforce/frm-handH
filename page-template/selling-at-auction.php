<?php
/*
    Template name: selling-at-auction
*/

get_header();

get_banner('Homepage / Classic Auctions / Selling at Auction', get_the_post_thumbnail_url(get_the_ID(), 'full'), 'Selling at Auction');

//set fields
$title = get_field('selling_title');
$text = get_field('selling_text');
$button1 = get_field('selling_button_1');
$button2 = get_field('selling_button_2');
$title2 = get_field('selling_auctions_high_title');
$title3 = get_field('selling_auctions_title');
$text2 = get_field('selling_auctions_text');
?>

<section class="insurance insurance_v1 insurance_share">
    <div class="insurance_container">
        <?php if ($title): ?>
        <div class="insurance-title">
            <h2><?php echo $title; ?></h2>
        </div>
        <?php endif; ?>
        <?php if ($text): ?>
        <div class="content">
            <?php echo $text; ?>
        </div>
        <?php endif; ?>
        <div class="actions">
            <?php if ($button1): ?>
            <a href="<?php echo esc_url($button1['url']); ?>"
                <?php echo $button1['target'] ? 'target="' . esc_attr($button1['target']) . '"' : ''; ?>>
                <?php echo esc_html($button1['title']); ?>
            </a>
            <?php endif; ?>
            <?php if ($button2): ?>
            <a href="<?php echo esc_url($button2['url']); ?>"
                <?php echo $button2['target'] ? 'target="' . esc_attr($button2['target']) . '"' : ''; ?>>
                <?php echo esc_html($button2['title']); ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="w-100">
    <div class="insurance_container">
        <div class="insurance_slider">
            <div class="splide splidev1" role="group" id="logos1">
                <div class="splide__track">
                    <ul class="splide__list">
                        <?php if( have_rows('selling_brands') ): ?>
                        <?php while( have_rows('selling_brands') ): the_row(); 
                                $brand_img = get_sub_field('selling_brand');
                            ?>
                        <li class="splide__slide">
                            <img src="<?php echo esc_url($brand_img); ?>" alt="Brand Logo">
                        </li>
                        <?php endwhile; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<?php get_template_part('inc/sections/cta'); ?>


<section class="heritage_container selling_heritage">
    <div class="heritage_information">
        <div class="heritage_images">
            <?php if( have_rows('selling_auctions') ): ?>
            <?php 
                the_row();
                $first_img = get_sub_field('selling_auction_image');
            ?>
            <img class="heritage_images-main" src="<?php echo esc_url($first_img); ?>" alt="Auction Image">
            <div class="heritage_images-slider">
                <div id="heritage" class="splide">
                    <div class="splide__track">
                        <ul class="splide__list">
                            <?php while( have_rows('selling_auctions') ): the_row(); 
                                    $auction_img = get_sub_field('selling_auction_image');
                                ?>
                            <li class="splide__slide">
                                <img src="<?php echo esc_url($auction_img); ?>" alt="Auction Image">
                            </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                </div>
                <div class="image_progress">
                    <div class="progress"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <div class="heritage_content">
            <div class="breadlines">
                <p>Classic Vehicle Auctions</p>
            </div>
            <h2>
                <?php if ($title2): ?>
                <span><?php echo $title2; ?></span>
                <?php endif; ?>
                <?php if ($title3): ?>
                <?php echo $title3; ?>
                <?php endif; ?>
            </h2>
            <?php if ($text2): ?>
            <div class="content">
                <?php echo $text2; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="selling_insurance">
    <div class="heritage_container">
        <div class="insurance_share selling_insurance-content">
            <div class="selling_insurance-banner">
                <?php if ($image_i = get_field('section_image')): ?>
                    <img class="w-100" src="<?php echo esc_url($image_i); ?>" alt="<?php echo esc_attr(get_field('section_title')); ?>">
                <?php endif; ?>

                <?php if ($title_i = get_field('section_title')): ?>
                    <h2><?php echo esc_html($title_i); ?></h2>
                <?php endif; ?>
            </div>

            <?php if ($text_i = get_field('section_text')): ?>
                <div class="selling_insurance-text"><?php echo $text_i; ?></div>
            <?php endif; ?>

            <div class="actions">
                <?php if ($btn1 = get_field('section_button1')): ?>
                    <a href="<?php echo esc_url($btn1['url']); ?>" target="<?php echo esc_attr($btn1['target'] ?: '_self'); ?>">
                        <?php echo esc_html($btn1['title']); ?>
                    </a>
                <?php endif; ?>

                <?php if ($btn2 = get_field('section_button2')): ?>
                    <a href="<?php echo esc_url($btn2['url']); ?>" target="<?php echo esc_attr($btn2['target'] ?: '_self'); ?>">
                        <?php echo esc_html($btn2['title']); ?>
                    </a>
                <?php endif; ?>

                <?php if ($btn3 = get_field('section_button3')): ?>
                    <a href="<?php echo esc_url($btn3['url']); ?>" target="<?php echo esc_attr($btn3['target'] ?: '_self'); ?>">
                        <?php echo esc_html($btn3['title']); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>


<?php get_template_part('inc/sections/timeline'); ?>

<section class="auction-tabs selling_tabs">
    <div class="container">
        <ul class="auction-tabs-nav">
            <?php if (get_field('before_auction_title')): ?>
            <li class="active" data-tab="before">Before the Auction</li>
            <?php endif; ?>
            <?php if (get_field('bidding_auction_title')): ?>
            <li data-tab="bidding">Bidding at Auction</li>
            <?php endif; ?>
            <?php if (get_field('after_auction_title')): ?>
            <li data-tab="after">After the Auction</li>
            <?php endif; ?>
            <?php if (get_field('fees_auction_title')): ?>
            <li data-tab="fees">Fees</li>
            <?php endif; ?>
        </ul>

        <div class="auction-tabs-content">
            <div class="tab-panel active" id="before">
                <div class="auction-grid">
                    <?php if (get_field('before_auction_image')): ?>
                    <div class="auction-image">
                        <img src="<?php echo get_field('before_auction_image'); ?>" alt="Auction">
                    </div>
                    <?php endif; ?>
                    <div class="auction-info">
                        <?php if (get_field('before_auction_title')): ?>
                        <h2><?php echo get_field('before_auction_title'); ?></h2>
                        <?php endif; ?>
                        <div>
                            <?php if (get_field('before_auction_text')): ?>
                            <?php echo get_field('before_auction_text'); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-panel" id="bidding">
                <div class="auction-grid">
                    <?php if (get_field('bidding_auction_image')): ?>
                    <div class="auction-image">
                        <img src="<?php echo get_field('bidding_auction_image'); ?>" alt="Auction">
                    </div>
                    <?php endif; ?>
                    <div class="auction-info">
                        <?php if (get_field('bidding_auction_title')): ?>
                        <h2><?php echo get_field('bidding_auction_title'); ?></h2>
                        <?php endif; ?>
                        <div>
                            <?php if (get_field('bidding_auction_text')): ?>
                            <?php echo get_field('bidding_auction_text'); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-panel" id="after">
                <div class="auction-grid">
                    <?php if (get_field('after_auction_image')): ?>
                    <div class="auction-image">
                        <img src="<?php echo get_field('after_auction_image'); ?>" alt="Auction">
                    </div>
                    <?php endif; ?>
                    <div class="auction-info">
                        <?php if (get_field('after_auction_title')): ?>
                        <h2><?php echo get_field('after_auction_title'); ?></h2>
                        <?php endif; ?>
                        <div>
                            <?php if (get_field('after_auction_text')): ?>
                            <?php echo get_field('after_auction_text'); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-panel" id="fees">
                <div class="auction-grid">
                    <?php if (get_field('fees_auction_image')): ?>
                    <div class="auction-image">
                        <img src="<?php echo get_field('fees_auction_image'); ?>" alt="Auction">
                    </div>
                    <?php endif; ?>
                    <div class="auction-info">
                        <?php if (get_field('fees_auction_title')): ?>
                        <h2><?php echo get_field('fees_auction_title'); ?></h2>
                        <?php endif; ?>
                        <div>
                            <?php if (get_field('fees_auction_text')): ?>
                            <?php echo get_field('fees_auction_text'); ?>
                            <?php endif; ?>
                        </div>
                        <?php if (get_field('fees_auction_card_text')): ?>
                        <div class="auction-cards">
                            <div class="auction-card">
                                <?php echo get_field('fees_auction_card_text'); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div>
                            <?php if (get_field('fees_auction_text_2')): ?>
                            <?php echo get_field('fees_auction_text_2'); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if (get_field('selling_terms')): ?>
    <div class="faq_list">
        <ul id="my-accordion" class="accordionjs">
            <li>
                <div>
                    <h3>Terms & Conditions</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path d="M0 8.99943L18 8.99943M8.99969 0L8.99969 18" stroke="#8C6E47" stroke-width="2" />
                    </svg>
                </div>
                <div>
                    <div class="description">
                        <?php echo get_field('selling_terms'); ?>
                    </div>
                </div>
            </li>
            <?php if (get_field('selling_download')): ?>
            <li>
                <div>
                    <h3>Download Forms</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path d="M0 8.99943L18 8.99943M8.99969 0L8.99969 18" stroke="#8C6E47" stroke-width="2" />
                    </svg>
                </div>
                <div>
                    <div class="description">
                        <?php echo get_field('selling_download'); ?>
                    </div>
                </div>
            </li>
            <?php endif; ?>
        </ul>
    </div>
    <?php endif; ?>
</section>



<?php
$bg2 = get_field('cta2_background_image');
$title2 = get_field('cta2_title');
$first_link2 = get_field('cta2_first_link');
$second_link2 = get_field('cta2_second_link');
?>

<?php if (!empty($title2)): ?>
<section class="cta">
    <?php if (!empty($bg2)): ?>
    <div class="cta_bg">
        <img src="<?php echo $bg2['url']; ?>" alt="<?php echo $bg2['alt']; ?>">
    </div>
    <?php endif; ?>
    <div class="container">
        <div class="cta_content">
            <h2><?php echo $title2; ?></h2>

            <div class="cta_links">
                <?php if (!empty($first_link2)): ?>
                <a href="<?php echo $first_link2['url'] ?>"
                    alt="<?php echo $first_link2['title'] ?>"><?php echo $first_link2['title'] ?></a>
                <?php endif; ?>

                <?php if (!empty($second_link2)): ?>
                <a href="<?php echo $second_link2['url'] ?>"
                    alt="<?php echo $second_link2['title'] ?>"><?php echo $second_link2['title'] ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="upcoming pb160">
    <?php get_template_part('inc/sections/upcoming'); ?>
</section>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const tabs = document.querySelectorAll(".auction-tabs-nav li");
    const panels = document.querySelectorAll(".tab-panel");

    tabs.forEach(tab => {
        tab.addEventListener("click", () => {
            const target = tab.getAttribute("data-tab");

            // remove active
            tabs.forEach(t => t.classList.remove("active"));
            panels.forEach(p => p.classList.remove("active"));

            // add active
            tab.classList.add("active");
            document.getElementById(target).classList.add("active");
        });
    });
});
</script>

<?php get_footer(); ?>

<script>
$("#my-accordion").accordionjs({
    closeAble: true,
    closeOther: true,
    slideSpeed: 150,
    activeIndex: 100,
});
</script>