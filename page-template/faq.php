<?php
/*
    Template name: faq
*/

get_header();

get_banner('Homepage / About / FAQs', '', 'Frequently Asked Questions');

$title = get_field('title_faq');
$description = get_field('description_faq');
$link = get_field('link_faq');

?>

<section class="faq">
    <div class="faq_container">
        <div class="faq_information">
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

        <?php if (have_rows('faqs')): ?>
            <div class="faq_list">
                <ul id="my-accordion" class="accordionjs">
                    <?php
                    $count = 1; // Iniciar contador
                    while (have_rows('faqs')): the_row();
                    ?>
                        <li>
                            <div>
                                <h3>
                                    <?php echo str_pad($count, 2, '0', STR_PAD_LEFT) . '. ' . get_sub_field('question'); $count++; ?>
                                </h3>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                    <path d="M0 8.99943L18 8.99943M8.99969 0L8.99969 18" stroke="#8C6E47" stroke-width="2" />
                                </svg>
                            </div>
                            <div>
                                <div class="description">
                                    <?php
                                    if (!empty(get_sub_field('answer'))) {
                                        echo get_sub_field('answer');
                                    }
                                    ?>
                                    <!-- <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Nisi velit molestiae adipisci, nam facilis laudantium quam, id possimus sequi doloribus eos provident quod vitae temporibus rem similique. Obcaecati, blanditiis repudiandae.</p> -->
                                </div>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php get_template_part('inc/sections/cta'); ?>

<section class="upcoming">
    <?php get_template_part('inc/sections/upcoming'); ?>
</section>

<?php get_footer(); ?>

<script>
    $("#my-accordion").accordionjs({
        closeAble: true,
        closeOther: true,
        slideSpeed: 150,
        activeIndex: 100,
    });
</script>