<?php
$bg = get_field('background_image');
$title = get_field('title_cta');
$first_link = get_field('first_link');
$second_link = get_field('second_link');

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
                    <?php if (!empty($first_link)): ?>
                        <a href="<?php echo $first_link['url'] ?>" alt="<?php echo $first_link['title'] ?>"><?php echo $first_link['title'] ?></a>
                    <?php endif; ?>

                    <?php if (!empty($second_link)): ?>
                        <a href="<?php echo $second_link['url'] ?>" alt="<?php echo $second_link['title'] ?>"><?php echo $second_link['title'] ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>