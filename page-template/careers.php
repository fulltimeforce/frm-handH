<?php
/*
    Template name: careers
*/

get_header();

get_banner('Homepage / About / Careers', '', 'Careers');

$title = get_field('title_careers');
$description = get_field('description_careers');
$link = get_field('link_careers');

?>

<section class="faq in_opportunity_page">
    <div class="faq_container">
        <div class="faq_information mb_64">
            <div class="faq-title">
                <h2><?php echo $title; ?></h2>
            </div>
            <div class="faq-content">
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
    </div>
</section>

<?php if (have_rows('opportunities')): ?>
    <section class="opportunities">
        <div class="opportunities_container">
            <div class="opportunities_row">
                <div class="opportunities_information">
                    <div class="opportunities_title spacing">
                        <h2>Current Opportunities</h2>
                    </div>
                    <div class="opportunities_dropdown">
                        <ul id="my-accordion" class="accordionjs">
                            <?php while (have_rows('opportunities')): the_row(); ?>
                                <li>
                                    <div>
                                        <h3><?php echo get_sub_field('subtitle_oppo'); ?></h3>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                            <path d="M0 8.99943L18 8.99943M8.99969 0L8.99969 18" stroke="#8C6E47" stroke-width="2" />
                                        </svg>
                                    </div>
                                    <div class="description">
                                        <?php
                                        if (!empty(get_sub_field('content_oppo'))) {
                                            echo get_sub_field('content_oppo');
                                        }
                                        ?>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                </div>
                <div class="opportunities_images">
                    <div class="spacing"></div>
                    <div class="opportunities_images-collection">
                        <?php while (have_rows('opportunities')): the_row(); ?>
                            <img
                                src="<?php echo get_sub_field('image_oppo')['url'] ?>"
                                title="<?php echo get_sub_field('image_oppo')['title'] ?>"
                                alt="<?php echo get_sub_field('image_oppo')['alt'] ?>"
                                width="<?php echo get_sub_field('image_oppo')['width'] ?>"
                                height="<?php echo get_sub_field('image_oppo')['height'] ?>"
                                loading="lazy">
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php get_footer(); ?>

<script>
    $("#my-accordion").accordionjs({
        closeAble: true,
        closeOther: true,
        slideSpeed: 150,
        activeIndex: 100,
        openSection: function(section) {
            console.log(section)
        },
        beforeOpenSection: function(section) {
            console.log(section)
        },
    });
</script>