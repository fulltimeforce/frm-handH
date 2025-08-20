<?php
/*
    Template name: contact
*/

get_header();

get_banner('Homepage / Contact', '', 'Contact H&H Classics');

$title = get_field('title_ci');
$description = get_field('description_ci');
$link1 = get_field('first_link_ci');
$link2 = get_field('second_link_ci');

?>

<section class="contact_information">
    <div class="contact_information-container">
        <h2><?php echo $title; ?></h2>
        <div class="content">
            <?php echo $description; ?>
        </div>
        <div class="actions">
            <?php if (!empty($link1)): ?>
                <a href="<?php echo $link1['url']; ?>" alt="<?php echo $link1['title']; ?>">
                    <?php echo $link1['title']; ?>
                </a>
            <?php endif; ?>

            <?php if (!empty($link2)): ?>
                <a href="<?php echo $link2['url']; ?>" alt="<?php echo $link2['title']; ?>">
                    <?php echo $link2['title']; ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="make_an_enquiry">
    <div class="make_an_enquiry-container">
        <div class="make_an_enquiry-head">
            <h2>Make an Enquiry Today</h2>
            <p>To enquire about a complimentary valuation for sale of your vehicle please call our office on 01925 210035, email sales@handh.co.uk or complete the form below. Our offices are in Cheshire and Surrey, however our field based specialists are positioned across the UK and Europe.</p>
        </div>
        <div class="make_an_enquiry-form">
            <?php echo do_shortcode('[gravityform id="2" title="true" ajax="true"]'); ?>
        </div>
        <div class="w-100">
            <h3>Join the success and consign your classic or performance motorcar, classic motorcycle or vintage scooter for sale with H&H Classics.</h3>
        </div>
    </div>
</section>

<?php get_template_part('inc/sections/request-register'); ?>

<section class="upcoming">
    <?php get_template_part('inc/sections/upcoming'); ?>
</section>

<?php if (have_rows('ubications')): ?>
    <section class="contact_banners">
        <div class="contact_banners-container">
            <?php while (have_rows('ubications')): the_row(); ?>
                <div class="headquarter">
                    <div class="headquarter-image">
                        <div>
                            <img
                                src="<?php echo get_sub_field('image_ubi')['url'] ?>"
                                title="<?php echo get_sub_field('image_ubi')['title'] ?>"
                                alt="<?php echo get_sub_field('image_ubi')['alt'] ?>"
                                width="<?php echo get_sub_field('image_ubi')['width'] ?>"
                                height="<?php echo get_sub_field('image_ubi')['height'] ?>"
                                loading="lazy">
                            <h3><?php echo get_sub_field('title_ubi'); ?></h3>
                        </div>
                    </div>
                    <div class="headquarter-content">
                        <div class="description">
                            <p><?php echo get_sub_field('description_ubi'); ?></p>
                        </div>
                        <div class="actions">
                            <a href="#">Visit Google Maps</a>
                        </div>
                        <div class="data">
                            <h4>Contact Us</h4>
                            <ul>
                                <li>Email: <a href="mailto:sales@handh.co.uk">sales@handh.co.uk</a></li>
                                <li>Tel: <b>+44 (0)1925 210035</b></li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>
<?php endif; ?>

<?php get_footer(); ?>