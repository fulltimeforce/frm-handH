<?php
/*
    Template name: our-services
*/

get_header();

get_banner('Homepage / About / Our Services', '', 'Our Services');

$title = get_field('title_careers');
$description = get_field('description_careers');
$link = get_field('link_careers');

?>

<section class="faq in_services_page">
    <div class="faq_container">
        <div class="faq_information mb_0">
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

<?php if (have_rows('our_services')): ?>
    <div class="opportunities-buttons">
        <?php while (have_rows('our_services')): the_row(); ?>
            <button class="<?php echo get_row_index() == 1 ? 'active' : ''; ?>">
                <?php echo get_sub_field('button_in_top_service') ?>
            </button>
        <?php endwhile; ?>
    </div>
    <div class="w-100 opportunities-column">
        <?php while (have_rows('our_services')): the_row(); ?>
            <section class="opportunities" data-state="0">
                <div class="opportunities_container">
                    <div class="opportunities_row">
                        <div class="opportunities_information">
                            <div class="opportunities_title spacing">
                                <h2><?php echo get_sub_field('title_service'); ?></h2>
                            </div>
                            <div class="opportunities_dropdown">
                                <ul class="accordionjs my-accordion">
                                    <?php while (have_rows('dropdown_service')): the_row(); ?>
                                        <li>
                                            <div>
                                                <h3><?php echo get_sub_field('subtitle_serv'); ?></h3>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                                    <path d="M0 8.99943L18 8.99943M8.99969 0L8.99969 18" stroke="#8C6E47" stroke-width="2" />
                                                </svg>
                                            </div>
                                            <div class="description">
                                                <?php
                                                if (!empty(get_sub_field('content_serv'))) {
                                                    echo get_sub_field('content_serv');
                                                }
                                                ?>
                                            </div>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            </div>
                        </div>
                        <div class="opportunities_images">
                            <!-- <div class="spacing"></div> -->
                            <div class="opportunities_images-collection">
                                <?php while (have_rows('dropdown_service')): the_row(); ?>
                                    <img
                                        src="<?php echo get_sub_field('image_serv')['url'] ?>"
                                        title="<?php echo get_sub_field('image_serv')['title'] ?>"
                                        alt="<?php echo get_sub_field('image_serv')['alt'] ?>"
                                        width="<?php echo get_sub_field('image_serv')['width'] ?>"
                                        height="<?php echo get_sub_field('image_serv')['height'] ?>"
                                        loading="lazy">
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<section class="request_quote">
    <div class="request_quote-container">
        <h3>Request a transport quote</h3>
        <div class="request_quote-box">
            <?php echo do_shortcode('[gravityform id="5" title="true" ajax="true"]'); ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>

<script>
    $(".my-accordion").accordionjs({
        closeAble: true,
        closeOther: true,
        slideSpeed: 150,
        activeIndex: 100,
        openSection: function(section) {
            let index = $(section).index();
            $(section).closest(".opportunities").attr('data-state', index);
        }
    });
</script>