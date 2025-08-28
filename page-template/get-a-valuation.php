<?php
/*
    Template name: get-a-valuation
*/

get_header();

get_banner('Homepage / Classic Auctions / Get a Valuation', '', 'Get a Valuation');

?>

<section class="valuation_info">
    <div class="valuation_info-container">
        <div class="valuation_info-title">
            <h2>Sell my Classic Motorcar, Motorcycle or Vintage Scooter</h2>
        </div>
        <div class="valuation_info-row">
            <div class="valuation_info-col">
                <p>Choose the easiest way that works for you:</p>
                <h3>Contact our team</h3>
                <ol>
                    <li>Call our friendly team at <b>01925 210035</b></li>
                    <li>Email us at <a href="mailto:sales@handh.co.uk">sales@handh.co.uk</a>, or</li>
                    <li>Fill out the quick form below - we will get back to you promptly</li>
                </ol>
            </div>
            <div class="valuation_info-col">
                <p>For after-hours enquiries, reach out to:</p>
                <h3>James McWilliam</h3>
                <ul>
                    <li>Email: <a href="mailto:james.mcwilliam@handh.co.uk">james.mcwilliam@handh.co.uk</a></li>
                    <li>Tel: <b>+44 (0)7943 584767</b></li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="showcase">
    <div class="showcase-container">
        <div class="showcase-title">
            <h2>Showcase your Classic in the most prestigious venues and reach a global audience. Get a valuation for your classic car, motorcycle, or vintage scooter: it's free.</h2>
        </div>
        <div class="showcase-form w-100">
            <?php echo do_shortcode('[gravityform id="4" title="true" ajax="true"]'); ?>
        </div>
        <div class="showcase-connect">
            <p>Join H&H Classics on our social media channels today</p>
            <?php get_template_part('inc/sections/social-list-links'); ?>
        </div>
        <div class="showcase-dropdown">
            <ul id="my-accordion" class="accordionjs">
                <li>
                    <div>
                        <h3>Terms & Conditions</h3>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                            <path d="M0 8.99943L18 8.99943M8.99969 0L8.99969 18" stroke="#8C6E47" stroke-width="2" />
                        </svg>
                    </div>
                    <div class="description">
                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Quam, eveniet libero vel cumque vero labore minima rerum iste culpa necessitatibus modi aspernatur ducimus obcaecati nulla! Omnis sint et eos facere.</p>
                    </div>
                </li>
                <li>
                    <div>
                        <h3>Download Forms</h3>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                            <path d="M0 8.99943L18 8.99943M8.99969 0L8.99969 18" stroke="#8C6E47" stroke-width="2" />
                        </svg>
                    </div>
                    <div class="description">
                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Quam, eveniet libero vel cumque vero labore minima rerum iste culpa necessitatibus modi aspernatur ducimus obcaecati nulla! Omnis sint et eos facere.</p>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</section>

<?php get_template_part('inc/sections/cta'); ?>

<section class="upcoming pb160">
    <?php get_template_part('inc/sections/upcoming'); ?>
</section>

<?php get_footer(); ?>

<script>
    $("#my-accordion").accordionjs({
        closeAble: true,
        closeOther: true,
        slideSpeed: 150,
        activeIndex: 100,
        openSection: function(section) {
            let index = $(section).index();
            $(".opportunities").attr('data-state', index);
        }
    });
</script>