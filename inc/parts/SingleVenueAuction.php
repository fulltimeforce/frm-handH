<?php
$auctions_title   = get_field('auctions_title');
$auctions_content = get_field('auctions_content');
$auctions_link    = get_field('auctions_link');
?>

<?php if ($auctions_title || $auctions_content || $auctions_link): ?>
    <section class="single_venue_content">
        <div class="single_venue_content-container">
            <div class="single_venue_content-top">
                <?php if ($auctions_title): ?>
                    <div class="single_venue_content-title">
                        <h2><?php echo esc_html($auctions_title); ?></h2>
                    </div>
                <?php endif; ?>

                <div class="single_venue_content-content">
                    <?php if ($auctions_content): ?>
                        <div class="content">
                            <?php echo wp_kses_post($auctions_content); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($auctions_link): ?>
                        <div class="actions">
                            <a href="<?php echo esc_url($auctions_link['url']); ?>" alt="<?php echo esc_html($auctions_link['title']); ?>" class="permalink_border">
                                <?php echo esc_html($auctions_link['title']); ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                                    <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                                </svg>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php get_template_part('inc/sections/cta-single-venue_auction'); ?>

<section class="single_venue_info">
    <div class="single_venue_info-container">

        <?php
        $consign_content_1 = get_field('consign_content_1');
        if ($consign_content_1): ?>
            <div class="consign w-100">
                <h2>Consign with H&H</h2>
                <?php if ($consign_content_1): ?>
                    <div class="consign-content">
                        <?php echo wp_kses_post($consign_content_1); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>


        <?php
        $how_to_get_title = get_field('how_to_get_title');
        if ($how_to_get_title || have_rows('how_to_get_items')): ?>
            <div class="how_to_get w-100">
                <?php if ($how_to_get_title): ?>
                    <h2><?php echo esc_html($how_to_get_title); ?></h2>
                <?php endif; ?>

                <?php if (have_rows('how_to_get_items')): ?>
                    <div class="how_to_get-col">
                        <?php while (have_rows('how_to_get_items')): the_row();
                            $subtitle   = get_sub_field('item_subtitle');
                            $desc       = get_sub_field('item_description');
                        ?>
                            <div class="how_to_get-row">
                                <?php if ($subtitle): ?>
                                    <h3><?php echo esc_html($subtitle); ?></h3>
                                <?php endif; ?>

                                <?php if ($desc): ?>
                                    <div class="content">
                                        <p><?php echo wp_kses_post($desc); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="venue_map">
    <div class="venue_map-container">
        <div class="w-100 map_parent">
            <div id="map"></div>
        </div>
    </div>
</section>

<section class="upcoming pb160">
    <?php get_template_part('inc/sections/upcoming'); ?>
</section>