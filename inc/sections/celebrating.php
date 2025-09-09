<?php
//fields
$auction_subtitle = get_field('auction_subtitle');
$auction_title = get_field('auction_title');
$auction_text = get_field('auction_text');
$auction_button = get_field('auction_button');
?>
<section class="celebrating">
    <div class="celebrating_container">
        <div class="celebrating_info">
            <div class="content">
                <?php if ($auction_subtitle): ?>
                    <div class="breadlines">
                        <p><?php echo $auction_subtitle; ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($auction_title): ?>
                    <?php
                    // Separar en palabras
                    $words = explode(' ', $auction_title, 2);
                    $first_word = $words[0];
                    $rest = isset($words[1]) ? $words[1] : '';
                    ?>
                    <h2>
                        <span><?php echo esc_html($first_word); ?></span>
                        <?php echo esc_html($rest); ?>
                    </h2>
                <?php endif; ?>
                <?php if ($auction_text): ?>
                    <p><?php echo $auction_text; ?></p>
                <?php endif; ?>
                <?php if ($auction_button): ?>
                    <a href="<?php echo esc_url($auction_button['url']); ?>" class="permalink_border">
                        <?php echo ($auction_button['title']); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                            <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                        </svg>
                    </a>
                <?php endif; ?>
            </div>
            <div class="celebrating_info-thumb">
                <div class="image">
                    <?php if ($auction_button): ?>
                        <a href="<?php echo esc_url($auction_button['url']); ?>" class="permalink">Learn More</a>
                        <img src="<?php echo IMG; ?>/vector3.svg" class="image-vector">
                        <img src="<?php echo IMG; ?>/3.jpg" class="image-thumb">
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="celebrating-subtitles">
            <div>
                <div class="breadlines">
                    <p>Classic Auction Experience</p>
                </div>
                <h3><?php the_field('celebrating_section1'); ?></h3>
            </div>
            <div>
                <div class="breadlines">
                    <p>Warrington 2024</p>
                </div>
                <h3><?php the_field('celebrating_section2'); ?></h3>
            </div>
        </div>
        <div class="celebrating-images">
            <div class="image">
                <?php if ($link1 = get_field('section_link1')): ?>
                    <a href="<?php echo esc_url($link1['url']); ?>" class="permalink">Learn More</a>
                <?php endif; ?>
                <?php if ($icon1 = get_field('section_icon1')): ?>
                    <img src="<?php echo $icon1; ?>" alt="icon" class="image-vector">
                <?php endif; ?>
                <?php if ($image1 = get_field('section_image1')): ?>
                    <img src="<?php echo $image1; ?>" alt="image" class="image-thumb">
                <?php endif; ?>
            </div>
            <div class="image">
                <?php if ($link2 = get_field('section_link2')): ?>
                    <a href="<?php echo esc_url($link2['url']); ?>" class="permalink">Learn More</a>
                <?php endif; ?>
                <?php if ($icon2 = get_field('section_icon2')): ?>
                    <img src="<?php echo $icon2; ?>" alt="icon" class="image-vector">
                <?php endif; ?>
                <?php if ($image2 = get_field('section_image2')): ?>
                    <img src="<?php echo $image2; ?>" alt="image" class="image-thumb">
                <?php endif; ?>
            </div>
        </div>
        <div class="celebrating-descriptions">
            <div>
                <p><?php the_field('section_text1'); ?></p>
            </div>
            <div>
                <p><?php the_field('section_text2'); ?></p>
            </div>
        </div>
    </div>
</section>