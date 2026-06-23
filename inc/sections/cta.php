<?php
$bg = get_field('background_image');
$title = get_field('title_cta');

$first_link = get_field('first_link');
$second_link = get_field('second_link');

$first_link_text  = !empty(get_field('first_button_text_d'))
    ? get_field('first_button_text_d')
    : 'Contact Us Now';

$second_link_text = !empty(get_field('second_button_text_d'))
    ? get_field('second_button_text_d')
    : 'Upcoming Auctions';

// slug de la página actual
$slug = get_post_field('post_name', get_post())
?>

<?php if (!empty($title)): ?>
    <section class="cta <?php echo esc_attr($slug); ?>">
        <?php if (!empty($bg)): ?>
            <div class="cta_bg">
                <img src="<?php echo $bg['url']; ?>" alt="<?php echo $bg['alt']; ?>">
            </div>
        <?php endif; ?>
        <div class="container">
            <div class="cta_content">
                <h2><?php echo $title; ?></h2>

                <div class="cta_links">
                    <?php if (!empty($first_link) && !empty($first_link_text)): ?>
                        <a href="<?php echo $first_link ?>" alt="<?php echo $first_link_text; ?>"><?php echo $first_link_text; ?></a>
                    <?php endif; ?>

                    <?php if (!empty($second_link) && !empty($second_link_text)): ?>
                        <a href="<?php echo $second_link ?>" alt="<?php echo $second_link_text; ?>"><?php echo $second_link_text; ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>