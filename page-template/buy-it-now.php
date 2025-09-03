<?php
/*
    Template name: buy-it-now
*/

get_header();

get_banner('Homepage / classic auctions / Auction Results', get_the_post_thumbnail_url(get_the_ID(), 'full'), 'Auction Results');

/* paginación */
$paged = max(1, get_query_var('paged') ? (int) get_query_var('paged') : (int) get_query_var('page'));

/* per page desde el selector (fallback a 6) */
$ppp = isset($_GET['posts_per_page']) ? max(1, (int) $_GET['posts_per_page']) : 6;

$argsVehicle = array(
    'post_type'      => 'vehicles',
    'posts_per_page' => $ppp,
    'paged'          => $paged,
);

$vehicles = new WP_Query($argsVehicle);

?>

<section class="auction_result-tab">
    <div class="container">
        <div>
            <a href="<?php echo esc_url(home_url('auction-results')); ?>">PAST AUCTIONS</a>
            <a class="active">Unsold Vehicles</a>
        </div>
    </div>
</section>

<section class="auction_vehicles">
    <div class="auction_vehicles-container">
        <div class="auction_vehicles-head">
            <h2>Buy It Now - The vehicles listed below are available post auction for purchase.</h2>
            <div class="content">
                <p>Vehicles unsold in the most recent auction are promoted here for 14 days post auction, you may make an offer using the provided form below or contact by call us on 01925 210035 or send an email to <a href="mailto:sales@HandH.co.uk">sales@HandH.co.uk</a>.</p>
            </div>
        </div>
        <form class="auction_result-filter" method="get" action="">
            <div class="auction_result-filter-search">
                <input type="search" name="search_vehicle" placeholder="Search for..." value="<?php echo get_search_query(); ?>">
                <button type="submit">Go</button>
            </div>
            <div class="auction_result-filter-select">
                <select name="model">
                    <option value="">All Models</option>
                    <option value="Piccadilly Roadster" <?php selected($_GET['model'] ?? '', 'Piccadilly Roadster'); ?>>
                        Piccadilly Roadster
                    </option>
                </select>
            </div>
            <div class="auction_result-filter-select">
                <select name="orderby">
                    <option value="">Sort by</option>
                    <option value="lot" <?php selected($_GET['orderby'] ?? '', 'lot'); ?>>Sort by lot number</option>
                    <option value="estimate" <?php selected($_GET['orderby'] ?? '', 'estimate'); ?>>Sort by Estimate</option>
                </select>
            </div>
            <div class="auction_result-filter-select">
                <select name="status">
                    <option value="available" <?php selected($_GET['status'] ?? '', 'available'); ?>>Available for Sale</option>
                </select>
            </div>
            <div class="auction_result-filter-year">
                <select name="year_from">
                    <option value="">From</option>
                    <?php for ($y = 1920; $y <= date('Y'); $y++): ?>
                        <option value="<?php echo $y; ?>" <?php selected($_GET['year_from'] ?? '', $y); ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
                <p>To</p>
                <select name="year_to">
                    <option value="">To</option>
                    <?php for ($y = 1920; $y <= date('Y'); $y++): ?>
                        <option value="<?php echo $y; ?>" <?php selected($_GET['year_to'] ?? '', $y); ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="auction_result-filter-page">
                <p>
                    Showing
                    <select id="blog-perpage" class="blog_section-filter-page" name="posts_per_page" onchange="this.form.submit()">
                        <option value="6" <?php selected($_GET['posts_per_page'] ?? '', 6); ?>>6</option>
                        <option value="12" <?php selected($_GET['posts_per_page'] ?? '', 12); ?>>12</option>
                        <option value="24" <?php selected($_GET['posts_per_page'] ?? '', 24); ?>>24</option>
                    </select>
                    Per Page
                </p>
            </div>
        </form>
        <?php if ($vehicles->have_posts()): ?>
            <div class="w-100">
                <?php while ($vehicles->have_posts()) : $vehicles->the_post(); ?>
                    <?php
                    $id = get_the_ID();
                    $title      = get_the_title($id);
                    $permalink  = get_permalink($id);

                    $estimate_high = get_field('estimate_high', $id);
                    $estimate_low = get_field('estimate_low', $id);

                    $image      = get_the_post_thumbnail_url($id, 'large');
                    if (!$image) {
                        $image = IMG . '/car2.png';
                    }
                    ?>
                    <div class="auction_result-list-item">
                        <div class="auction_result-list-img">
                            <img class="w-100" src="<?php echo esc_url($image); ?>" alt="<?php echo $title; ?>">
                        </div>
                        <div class="auction_result-list-info">
                            <h3><?php echo $title; ?></h3>
                            <div class="auction_result-list-data">
                                <div>
                                    <?php if ($registration_no): ?>
                                        <p>Registration No: <span><?php echo esc_html($registration_no); ?></span></p>
                                    <?php endif; ?>
                                    <?php if ($chassis_no): ?>
                                        <p>Chassis No: <span><?php echo esc_html($chassis_no); ?></span></p>
                                    <?php endif; ?>
                                    <?php if ($vehicle_mot): ?>
                                        <p>MOT: <span><?php echo esc_html($vehicle_mot); ?></span></p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <?php if ($estimate_low && $estimate_high): ?>
                                        <p>Estimated at</p>
                                        <p class="gold-text">
                                            <?php echo '£' . $estimate_low . ' - £' . $estimate_high; ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($vehicle_short_description): ?>
                                <p class="auction_result-list-description">
                                    <?php echo esc_html($vehicle_short_description); ?>
                                </p>
                            <?php endif; ?>
                            <a alt="Enquire Now" href="<?php echo $permalink; ?>" class="permalink_border">
                                Enquire Now
                                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                                    <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                                </svg>
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            </div>

            <?php
            $pagination = paginate_links(array(
                'total'     => (int) $vehicles->max_num_pages,
                'current'   => $paged,
                'mid_size'  => 2,
                'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="19" height="14" viewBox="0 0 19 14" fill="none"><path d="M19 7L1.00049 7M1.00049 7L7.00049 13M1.00049 7L7.0005 0.999999" stroke="#8C6E47"/></svg>',
                'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="19" height="14" viewBox="0 0 19 14" fill="none"><path d="M-7.15494e-08 7L17.9995 7M17.9995 7L11.9995 1M17.9995 7L11.9995 13" stroke="#8C6E47"/></svg>',
                // preserva parámetros del filtro en la URL
                'add_args'  => array_filter(array(
                    'posts_per_page' => $ppp,
                )),
            ));

            if ($pagination) {
                echo '<div class="auction_result-pagination with_border">' . $pagination . '</div>';
            }
            ?>
        <?php endif; ?>
    </div>
    <div class="advertise_container auction_result-form">
        <h2>Contact Sales Department</h2>
        <div class="advertise_form">
            <?php echo do_shortcode('[gravityform id="2" title="true" ajax="true"]'); ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>