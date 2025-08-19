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
                    <h2><?php echo wp_kses_post($auction_title); ?></h2>
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
                <h3>H&H Classic Auctions</h3>
            </div>
            <div>
                <div class="breadlines">
                    <p>Warrington 2024</p>
                </div>
                <h3>Voted best auction house</h3>
            </div>
        </div>
        <div class="celebrating-images">
            <div class="image">
                <a href="#" class="permalink">Learn More</a>
                <img src="<?php echo IMG; ?>/vector2.svg" class="image-vector">
                <img src="<?php echo IMG; ?>/1.jpg" class="image-thumb">
            </div>
            <div class="image">
                <a href="#" class="permalink">Learn More</a>
                <img src="<?php echo IMG; ?>/vector1.svg" class="image-vector">
                <img src="<?php echo IMG; ?>/2.png" class="image-thumb">
            </div>
        </div>
        <div class="celebrating-descriptions">
            <div>
                <p>H&H Classic Auctions are fabulous events where car and bike aficionados can browse and bid in relaxed environments alongside like-minded enthusiasts.</p>
            </div>
            <div>
                <p>H&H Classics - Classic Car & Motorbikes was awarded The Best Auction House in Warrington for 2024. An overall quality score exceeding 95% was achieved, making them the top ranked in Warrington.</p>
            </div>
        </div>
    </div>
</section>