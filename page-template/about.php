<?php
/*
    Template name: about
*/

get_header();

get_banner('Homepage / About / About H&H Classics', '', 'About H&H Classics');

?>

<section class="timeline">
    <div class="container">
        <div class="timeline_head">
            <h2>A timeline of our journey over the past three decades</h2>
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
                                        <p>First Auction at Pavilion Gardens, Buxton H&H is established by Simon Hope</p>
                                        <div class="timecard-time">
                                            <span>1993</span>
                                        </div>
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
                                        <p>1968 Lotus 49 F1 Single-seater (ex Rob Walker) Sold: £367,500</p>
                                        <div class="timecard-time">
                                            <span>1999</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="splide__slide">
                            <div class="timecard">
                                <div class="timecard-grid">
                                    <div>
                                        <img src="<?php echo IMG; ?>/face1.png">
                                        <p>Damian Jones joined H&H</p>
                                        <div class="timecard-time">
                                            <span>2005</span>
                                        </div>
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
                                        <img src="<?php echo IMG; ?>/face1.png">
                                        <p>Damian Jones joined H&H</p>
                                        <div class="timecard-time">
                                            <span>2007</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="splide__slide">
                            <div class="timecard">
                                <div class="timecard-grid">
                                    <div>
                                        <img src="<?php echo IMG; ?>/slide2.png">
                                        <p>1929 Bentley 41/2 Litre (ex Woolf Barnato) Sold: 537,600 (resold: £874,00 in 2018)</p>
                                        <div class="timecard-time">
                                            <span>2011</span>
                                        </div>
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
                                        <img src="<?php echo IMG; ?>/slide2.png">
                                        <p><span>Colette Mckay joined H&H</span> 1922 Brough Superior SS80 'Old Bill' Sold: £292,500</p>
                                        <div class="timecard-time">
                                            <span>2012</span>
                                        </div>
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

<?php get_footer(); ?>