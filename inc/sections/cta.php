<?php
$bg = get_field('background_image');
$title = get_field('title_cta');
$first_link = get_field('first_link');
$second_link = get_field('second_link');
?>

<section class="cta">
    <?php if (!empty($bg)): ?>
        <div class="cta_bg">
            <img src="<?php echo $bg['url']; ?>" alt="<?php echo $bg['alt']; ?>">
        </div>
    <?php endif; ?>
    <div class="container">
        <div class="cta_content">
            <?php if (!empty($title)): ?>
                <h2><?php echo $title; ?></h2>
            <?php endif; ?>

            <div class="cta_links">
                <?php if (!empty($first_link)): ?>
                    <a href="<?php echo $first_link['url'] ?>" alt="<?php echo $first_link['title'] ?>"><?php echo $first_link['title'] ?></a>
                <?php endif; ?>

                <?php if (!empty($second_link)): ?>
                    <a href="<?php echo $first_link['url'] ?>" alt="<?php echo $first_link['title'] ?>"><?php echo $first_link['title'] ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>