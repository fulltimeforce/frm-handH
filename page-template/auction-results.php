<?php
/*
    Template name: auction results
*/

get_header();

get_banner('Homepage / classic auctions / Auction Results', '', 'Auction Results');

?>

<section class="auction_result-tab">
    <div class="container">
        <div>
            <a>PAST AUCTIONS</a>
            <a class="active">Unsold Vehicles</a>
        </div>
    </div>
</section>
<section class="auction_result-content">
    <div class="container">
        <h2>Buy It Now - The vehicles listed below are available post auction for purchase.</h2>
        <p class="auction_result-text p18">Vehicles unsold in the most recent auction are promoted here for 14 days post auction, you may make an offer using the provided form below or contact by call us on 01925210035 or send an email to <a>sales@HandH.co.uk.</a></p>
        <form class="auction_result-filter">
            <div class="auction_result-filter-search">
                <input type="search" placeholder="Search for...">
                <button>Go</button>
            </div>
            <div class="auction_result-filter-select">
                <select>
                    <option>All Models</option>
                </select>
            </div>
            <div class="auction_result-filter-select">
                <select>
                    <option>Sort by lot number</option>
                </select>
            </div>
            <div class="auction_result-filter-select">
                <select>
                    <option>Available for Sale</option>
                </select>
            </div>
            <div class="auction_result-filter-year">
                <select>
                    <option>1920</option>
                </select>
                <p>To</p>
                <select>
                    <option>2025</option>
                </select>
            </div>
            <div class="auction_result-filter-page">
                <p>
                    Showing 
                    <select id="blog-perpage" class="blog_section-filter-page" name="posts_per_page" onchange="this.form.submit()">
                        <option value="12" >12</option>
                        <option value="24" >24</option>
                        <option value="36" >36</option>
                    </select> 
                    Per Page
                </p>
            </div>
        </form>
        <div class="auction_result-list">
            <?php for ($i = 1; $i <= 6; $i++): ?>
                <div class="auction_result-list-item">
                    <div class="auction_result-list-img">
                        <img class="w-100" src="<?php echo IMG; ?>/car.png" alt="Car <?php echo $i; ?>">
                    </div>
                    <div class="auction_result-list-info">
                        <h3>Vehicle Name <?php echo $i; ?><br>(can Span across two lines)</h3>
                        <div class="auction_result-list-data">
                            <div>
                                <p>Registration No: <span>XXXXX</span></p>
                                <p>Chassis No: <span>XXXXX</span></p>
                                <p>MOT: <span>Status(eg. Exempt)</span></p>
                            </div>
                            <div>
                                <p>Estimated at</p>
                                <p class="gold-text">£XX0,000 - £XX0,000</p>
                            </div>
                        </div>
                        <p class="auction_result-list-description">Lorem ipsum dolor sit amet consectetur. Non adipiscing neque lobortis blandit. Non parturient lacinia pretium facilisi ut vitae quam pellentesque. Magna quam laoreet varius eleifend natoque ipsum at iaculis...</p>
                        <a href="#" class="permalink_border">
                            Enquire Now
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                                <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                            </svg>
                        </a>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</section>
<?php get_footer(); ?>