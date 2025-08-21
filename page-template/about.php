<?php
/*
    Template name: about
*/

get_header();

get_banner('Homepage / About / About H&H Classics', '', 'About H&H Classics');

?>

<section class="heritage">
    <div class="heritage_container">
        <div class="tabs">
            <button type="button" class="active">Our Heritage</button>
            <button type="button">Timeline</button>
            <button type="button">Our Specialists</button>
            <button type="button">Private Sales</button>
            <button type="button">Upcoming Auctions</button>
        </div>
        <div class="heritage_information">
            <div class="heritage_images">

            </div>
            <div class="heritage_content">
                <div class="breadlines">
                    <p>Classic Vehicle Auctions</p>
                </div>
                <h2><span>Our Heritage:</span> A Legacy of Successes</h2>
                <div class="content">
                    <p>
                        Founded in 1993 by Simon Hope and Mark Hamilton, H&H Classics has become synonymous with
                        expertise and success in the world of classic and collector vehicles. Over the past 30 years,
                        we’ve sold more than 15,000 motorcars and motorcycles to collectors across the globe, achieving
                        record-breaking sales and global recognition.
                        <br><br>
                        Our achievements include selling a car for over $11 million—a 1960 Ferrari 250 GT SWB—and nearly
                        £2 million worth of motorcycles in a single day. With over 75,000 loyal clients globally, H&H
                        Classics has cemented its reputation for delivering excellence.
                    </p>
                </div>
                <div class="actions">
                    <a href="#" class="permalink_border">
                        Explore Upcoming Actions
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                            <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                        </svg>
                    </a>
                    <a href="#" class="permalink_border">
                        View Our Services
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                            <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$title = get_field('title_timeline');
?>
<style>
.timeline .splide__list::before {
    width: calc(100% * 2.65);
}
</style>
<section class="timeline">
    <div class="container">
        <div class="timeline_head">
            <h2><?php echo $title; ?></h2>
        </div>
    </div>
    <div class="container_side">
        <div class="timeline_body">
            <div class="splide" role="group" id="timeline">
                <div class="splide__arrows">
                    <button class="splide__arrow splide__arrow--prev">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="26" viewBox="0 0 50 26" fill="none">
                            <path d="M0 13H48M48 13L36 1M48 13L36 25" stroke="#8C6E47" stroke-width="2" />
                        </svg>
                    </button>
                    <button class="splide__arrow splide__arrow--next">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="26" viewBox="0 0 50 26" fill="none">
                            <path d="M0 13H48M48 13L36 1M48 13L36 25" stroke="#8C6E47" stroke-width="2" />
                        </svg>
                    </button>
                </div>
                <div class="splide__track">
                    <ul class="splide__list">
                        <li class="splide__slide">
                            <div class="timecard">
                                <div class="timecard-grid">
                                    <div>
                                        <img src="<?php echo IMG; ?>/logo.svg">
                                        <div class="content">
                                            <p><span>First Auction at <br>Pavilion Gardens, Buxton <br>H&H is
                                                    established by <br>Simon Hope</span></p>
                                        </div>
                                        <div class="timecard-time">
                                            <span>1993</span>
                                        </div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="42"
                                            viewBox="0 0 30 42" fill="none">
                                            <path
                                                d="M16.4142 0.585785C15.6332 -0.195263 14.3668 -0.195263 13.5858 0.585785L0.857865 13.3137C0.0768159 14.0948 0.0768159 15.3611 0.857865 16.1421C1.63891 16.9232 2.90524 16.9232 3.68629 16.1421L15 4.82843L26.3137 16.1421C27.0948 16.9232 28.3611 16.9232 29.1421 16.1421C29.9232 15.3611 29.9232 14.0948 29.1421 13.3137L16.4142 0.585785ZM15 42L17 42L17 2L15 2L13 2L13 42L15 42Z"
                                                fill="#8C6E47" />
                                        </svg>
                                    </div>
                                    <div></div>
                                </div>
                            </div>
                        </li>
                        <li class="splide__slide">
                            <div class="timecard">
                                <div class="timecard-grid">
                                    <div></div>
                                    <div>
                                        <img src="<?php echo IMG; ?>/slide1.png">
                                        <div class="content">
                                            <p>1968 Lotus 49 F1 Single-seater <br>(ex Rob Walker) <br>Sold: £367,500</p>
                                        </div>
                                        <div class="timecard-time">
                                            <span>1999</span>
                                        </div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="43"
                                            viewBox="0 0 30 43" fill="none">
                                            <path
                                                d="M13.5858 42.3595C14.3668 43.1406 15.6332 43.1406 16.4142 42.3595L29.1421 29.6316C29.9232 28.8506 29.9232 27.5842 29.1421 26.8032C28.3611 26.0221 27.0948 26.0221 26.3137 26.8032L15 38.1169L3.68629 26.8032C2.90524 26.0221 1.63891 26.0221 0.857863 26.8032C0.0768144 27.5842 0.0768144 28.8506 0.857863 29.6316L13.5858 42.3595ZM15 0.945312L13 0.945312L13 40.9453L15 40.9453L17 40.9453L17 0.945313L15 0.945312Z"
                                                fill="#8C6E47" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="splide__slide">
                            <div class="timecard">
                                <div class="timecard-grid">
                                    <div>
                                        <img src="<?php echo IMG; ?>/face1.png" class="round">
                                        <div class="content">
                                            <p>Damian Jones joined H&H</p>
                                        </div>
                                        <div class="timecard-time">
                                            <span>2005</span>
                                        </div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="42"
                                            viewBox="0 0 30 42" fill="none">
                                            <path
                                                d="M16.4142 0.585785C15.6332 -0.195263 14.3668 -0.195263 13.5858 0.585785L0.857865 13.3137C0.0768159 14.0948 0.0768159 15.3611 0.857865 16.1421C1.63891 16.9232 2.90524 16.9232 3.68629 16.1421L15 4.82843L26.3137 16.1421C27.0948 16.9232 28.3611 16.9232 29.1421 16.1421C29.9232 15.3611 29.9232 14.0948 29.1421 13.3137L16.4142 0.585785ZM15 42L17 42L17 2L15 2L13 2L13 42L15 42Z"
                                                fill="#8C6E47" />
                                        </svg>
                                    </div>
                                    <div></div>
                                </div>
                            </div>
                        </li>
                        <li class="splide__slide">
                            <div class="timecard">
                                <div class="timecard-grid">
                                    <div></div>
                                    <div>
                                        <img src="<?php echo IMG; ?>/circle1.png" class="round">
                                        <div class="content">
                                            <p>First Auction at <br>Imperial War Museum, Duxford</p>
                                        </div>
                                        <div class="timecard-time">
                                            <span>2007</span>
                                        </div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="43"
                                            viewBox="0 0 30 43" fill="none">
                                            <path
                                                d="M13.5858 42.3595C14.3668 43.1406 15.6332 43.1406 16.4142 42.3595L29.1421 29.6316C29.9232 28.8506 29.9232 27.5842 29.1421 26.8032C28.3611 26.0221 27.0948 26.0221 26.3137 26.8032L15 38.1169L3.68629 26.8032C2.90524 26.0221 1.63891 26.0221 0.857863 26.8032C0.0768144 27.5842 0.0768144 28.8506 0.857863 29.6316L13.5858 42.3595ZM15 0.945312L13 0.945312L13 40.9453L15 40.9453L17 40.9453L17 0.945313L15 0.945312Z"
                                                fill="#8C6E47" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="splide__slide">
                            <div class="timecard">
                                <div class="timecard-grid">
                                    <div>
                                        <img src="<?php echo IMG; ?>/slide2.png">
                                        <div class="content">
                                            <p>1929 Bentley 41/2 Litre <br>(ex Woolf Barnato) <br>Sold: 537,600
                                                <br>(resold: £874,00 in 2018)</p>
                                        </div>
                                        <div class="timecard-time">
                                            <span>2011</span>
                                        </div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="42"
                                            viewBox="0 0 30 42" fill="none">
                                            <path
                                                d="M16.4142 0.585785C15.6332 -0.195263 14.3668 -0.195263 13.5858 0.585785L0.857865 13.3137C0.0768159 14.0948 0.0768159 15.3611 0.857865 16.1421C1.63891 16.9232 2.90524 16.9232 3.68629 16.1421L15 4.82843L26.3137 16.1421C27.0948 16.9232 28.3611 16.9232 29.1421 16.1421C29.9232 15.3611 29.9232 14.0948 29.1421 13.3137L16.4142 0.585785ZM15 42L17 42L17 2L15 2L13 2L13 42L15 42Z"
                                                fill="#8C6E47" />
                                        </svg>
                                    </div>
                                    <div></div>
                                </div>
                            </div>
                        </li>
                        <li class="splide__slide">
                            <div class="timecard">
                                <div class="timecard-grid">
                                    <div>
                                        <img src="<?php echo IMG; ?>/face2.png" class="round">
                                    </div>
                                    <div>
                                        <img src="<?php echo IMG; ?>/slide2.png">
                                        <div class="content">
                                            <p><span>Colette Mckay joined H&H</span> <br>1922 Brough Superior SS80 'Old
                                                Bill' <br>Sold: £292,500</p>
                                        </div>
                                        <div class="timecard-time">
                                            <span>2012</span>
                                        </div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="43"
                                            viewBox="0 0 30 43" fill="none">
                                            <path
                                                d="M13.5858 42.3595C14.3668 43.1406 15.6332 43.1406 16.4142 42.3595L29.1421 29.6316C29.9232 28.8506 29.9232 27.5842 29.1421 26.8032C28.3611 26.0221 27.0948 26.0221 26.3137 26.8032L15 38.1169L3.68629 26.8032C2.90524 26.0221 1.63891 26.0221 0.857863 26.8032C0.0768144 27.5842 0.0768144 28.8506 0.857863 29.6316L13.5858 42.3595ZM15 0.945312L13 0.945312L13 40.9453L15 40.9453L17 40.9453L17 0.945313L15 0.945312Z"
                                                fill="#8C6E47" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="splide__slide">
                            <div class="timecard">
                                <div class="timecard-grid">
                                    <div>
                                        <img src="<?php echo IMG; ?>/slide3.png">
                                        <div class="content">
                                            <p>1960 Ferrari 250 GT SWB <br>1967 Ferrari 275 GTB/4 <br>Sold combined:
                                                <br>£9,758,320</p>
                                        </div>
                                        <div class="timecard-time">
                                            <span>2015</span>
                                        </div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="42"
                                            viewBox="0 0 30 42" fill="none">
                                            <path
                                                d="M16.4142 0.585785C15.6332 -0.195263 14.3668 -0.195263 13.5858 0.585785L0.857865 13.3137C0.0768159 14.0948 0.0768159 15.3611 0.857865 16.1421C1.63891 16.9232 2.90524 16.9232 3.68629 16.1421L15 4.82843L26.3137 16.1421C27.0948 16.9232 28.3611 16.9232 29.1421 16.1421C29.9232 15.3611 29.9232 14.0948 29.1421 13.3137L16.4142 0.585785ZM15 42L17 42L17 2L15 2L13 2L13 42L15 42Z"
                                                fill="#8C6E47" />
                                        </svg>
                                    </div>
                                    <div></div>
                                </div>
                            </div>
                        </li>
                        <li class="splide__slide">
                            <div class="timecard">
                                <div class="timecard-grid">
                                    <div></div>
                                    <div>
                                        <img src="<?php echo IMG; ?>/slide4.png">
                                        <div class="content">
                                            <p><span>First Auction at National Motorcycle Museum</span> <br>1964 Ferrari
                                                330GT Nembo Spider <br>Sold: £609,500 <br>1996 Subaru Impreza WRC '97
                                                (ex Colin McRae) <br>Sold: £235,750</p>
                                        </div>
                                        <div class="timecard-time">
                                            <span>2017</span>
                                        </div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="43"
                                            viewBox="0 0 30 43" fill="none">
                                            <path
                                                d="M13.5858 42.3595C14.3668 43.1406 15.6332 43.1406 16.4142 42.3595L29.1421 29.6316C29.9232 28.8506 29.9232 27.5842 29.1421 26.8032C28.3611 26.0221 27.0948 26.0221 26.3137 26.8032L15 38.1169L3.68629 26.8032C2.90524 26.0221 1.63891 26.0221 0.857863 26.8032C0.0768144 27.5842 0.0768144 28.8506 0.857863 29.6316L13.5858 42.3595ZM15 0.945312L13 0.945312L13 40.9453L15 40.9453L17 40.9453L17 0.945313L15 0.945312Z"
                                                fill="#8C6E47" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="meet_our_specialist">
    <div class="meet_our_specialist-container">
        <div class="meet_our_specialist-head">
            <div class="breadlines">
                <p>Classic Vehicle Experts</p>
            </div>
            <h2>Meet Our Specialists</h2>
        </div>
        <div class="meet_our_specialist-slider">
            <div class="splide" role="group" id="specialist">
                <div class="splide__arrows">
                    <button class="splide__arrow splide__arrow--prev">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="26" viewBox="0 0 50 26" fill="none">
                            <path d="M0 13H48M48 13L36 1M48 13L36 25" stroke="#8C6E47" stroke-width="2" />
                        </svg>
                    </button>
                    <button class="splide__arrow splide__arrow--next">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="26" viewBox="0 0 50 26" fill="none">
                            <path d="M0 13H48M48 13L36 1M48 13L36 25" stroke="#8C6E47" stroke-width="2" />
                        </svg>
                    </button>
                </div>
                <div class="splide__track">
                    <ul class="splide__list">
                        <?php for ($i = 0; $i < 8; $i++): ?>
                        <li class="splide__slide">
                            <div class="specialist_card">
                                <div class="specialist_card-front">
                                    <div class="specialist_card-image">
                                        <img src="<?php echo IMG; ?>/member.png">
                                    </div>
                                    <div class="specialist_card-info specialist_card-toggle">
                                        <div>
                                            <p>Colette McKay</p>
                                            <span>Managing Director</span>
                                        </div>
                                        <button type="button" class="card_toggle">
                                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0 8.99943L18 8.99943M8.99969 0L8.99969 18" stroke="#F5F2EE"
                                                    stroke-width="2" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="specialist_card-back">
                                    <div class="specialist_card-toggle">
                                        <div>
                                            <p>Colette McKay</p>
                                            <span>Managing Director</span>
                                        </div>
                                        <button type="button" class="card_toggle minus">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="2"
                                                viewBox="0 0 18 2" fill="none">
                                                <path d="M0 1L18 0.999999" stroke="#F5F2EE" stroke-width="2" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="specialist_card-content">
                                        <ul>
                                            <li>Email: <a
                                                    href="mailto:colette.mckay@handh.co.uk">colette.mckay@handh.co.uk</a>
                                            </li>
                                            <li>Tel: <a href="tel:07527 606312">07527 606312</a></li>
                                        </ul>
                                        <p>
                                            As a qualified accounting technician, Colette joined H&H in 2012 and has
                                            been involved in all aspects of the business and industry.
                                            <br><br>
                                            From a young age, her father has been taking her to car meets, race meetings
                                            and shows. As an adrenaline junkie, she can still be found at the same shows
                                            and race meetings but now with her own sons.
                                        </p>
                                    </div>
                                    <a href="#">Read More</a>
                                </div>
                            </div>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="meet_our_specialist-foot">
        <div class="meet_our_specialist-title">
            <h2>Our specialists are not just professional auctioneers, but genuine enthusiasts</h2>
        </div>
        <div class="meet_our_specialist-content">
            <p>
                At H&H Classics, our specialists live and breathe classic and collector vehicles. Their passion isn’t
                just professional—it’s personal. They own, drive, and care for these vehicles with the same enthusiasm
                as our clients, which gives them unparalleled insight into the market.
                <br><br>
                With decades of hands-on experience, our team provides accurate valuations, transparent guidance, and
                exceptional customer care. Whether you’re selling a Ferrari, a Fiat, or an Austin, you can rely on their
                expertise to make the process seamless and enjoyable.
            </p>
            <a href="#" class="permalink_border">
                Meet The Team
                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                    <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                </svg>
            </a>
        </div>
    </div>
</section>

<?php get_template_part('inc/sections/cta'); ?>

<?php
$title = get_field('title_private_sales');
$description = get_field('description_private_sales');
$link = get_field('link_private_sales');
$images = get_field('images_pv');
?>
<section class="tailored">
    <div class="tailored_container">
        <div class="tailored-flex">
            <div class="tailored_info">
                <div class="tailored_info-box">
                    <div class="breadlines">
                        <p>Tailored for Every Client</p>
                    </div>
                    <?php if (empty($title)): ?>
                    <h2>BESPOKE PRIVATE SALES</h2>
                    <?php endif; ?>

                    <?php if (empty($description)): ?>
                    <div class="tailored_info-content">
                        <P>At H&H Classics, we recognise that auctions aren't always the preferred route for everyone.
                            That's why we've developed our bespoke Private Sales and Confidential Sales services,
                            catering to clients who desire a more discreet and tailored approach to buying or selling
                            classic vehicles.

                            Our Private Sales Office ensures a seamless, stress-free experience, handling everything
                            from sourcing rare vehicles to discreetly finding the right buyers for your classic car or
                            motorcycle. With a commitment to flexibility and professionalism, we cater to the unique
                            needs of each client.</P>
                    </div>
                    <?php endif; ?>


                    <a href="<?php echo $link['url']; ?>" class="permalink_border" alt="<?php echo $link['title']; ?>">
                        Learn More About Private Sales
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                            <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                        </svg>
                    </a>

                </div>
            </div>


            <div class="tailored_images">
                <!-- <div class="tailored_image">
                    <img src="https://images.classic.com/vehicles/6eaf5413545febe6cbe56037734f09c7acbf8e19?w=1200&h=676&fit=crop"
                        alt="<?php echo get_sub_field('image_pv')['title'] ?>">
                </div>
                <div class="tailored_image">
                    <img src="https://images.classic.com/vehicles/6eaf5413545febe6cbe56037734f09c7acbf8e19?w=1200&h=676&fit=crop"
                        alt="<?php echo get_sub_field('image_pv')['title'] ?>">
                </div>
                <div class="tailored_image">
                    <img src="https://images.classic.com/vehicles/6eaf5413545febe6cbe56037734f09c7acbf8e19?w=1200&h=676&fit=crop"
                        alt="<?php echo get_sub_field('image_pv')['title'] ?>">
                </div>
                <div class="tailored_image">
                    <img src="https://images.classic.com/vehicles/6eaf5413545febe6cbe56037734f09c7acbf8e19?w=1200&h=676&fit=crop"
                        alt="<?php echo get_sub_field('image_pv')['title'] ?>">
                </div> -->
                <div class="imagelider">
                        <div id="imagelider" class="splide">
                        <div class="splide__track">
                            <ul class="splide__list">
                            <li class="splide__slide">
                                <img src="https://images.classic.com/vehicles/6eaf5413545febe6cbe56037734f09c7acbf8e19?w=1200&h=676&fit=crop"
                                    alt="<?php echo get_sub_field('image_pv')['title'] ?>">
                            </li>
                            <li class="splide__slide">
                                <img src="https://imageio.forbes.com/specials-images/imageserve/5d35eacaf1176b0008974b54/0x0.jpg?format=jpg&crop=4560,2565,x790,y784,safe&height=900&width=1600&fit=bounds" alt="Otra imagen">
                            </li>
                            <li class="splide__slide">
                                <img src="https://images.classic.com/vehicles/6eaf5413545febe6cbe56037734f09c7acbf8e19?w=1200&h=676&fit=crop" alt="Otra imagen">
                            </li>
                            </ul>
                        </div>
                    </div>

                </div>
                
            </div>


        </div>
    </div>
</section>

<section class="upcoming">
    <?php get_template_part('inc/sections/upcoming'); ?>
    <div class="upcoming_container">
        <div class="upcoming_info">
            <div class="upcoming_info-title">
                <h2>With 83% sell-through rate across live and online sales</h2>
            </div>
            <div class="upcoming_info-content">
                <p>
                    H&H Classics connects collectors from around the world through live and online auctions. With
                    prestigious venues like the Imperial War Museum, Duxford, and the Pavilion Gardens, Buxton, and a
                    global network spanning over 20 countries, we ensure every lot receives maximum exposure.
                    <br><br>
                    Our auctions boast an impressive 83% sell-through rate, thanks to our beautifully curated luxury
                    catalogues and innovative online bidding platform. For every bidder in the room at our live events,
                    three are registered online, with successful sales often shipping thousands of miles to new owners.
                </p>
                <div class="upcoming_info-actions">
                    <a href="#" class="permalink_border">
                        Explore Upcoming Auctions
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                            <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                        </svg>
                    </a>
                    <a href="#" class="permalink_border">
                        View Our Services
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                            <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>