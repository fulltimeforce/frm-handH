<?php
/*
    Template name: auction results
*/

get_header();

get_banner('Homepage / classic auctions / Auction Results', get_the_post_thumbnail_url(get_the_ID(), 'full'), 'Auction Results');
//get fields
$title = get_field('auction_result_title');
$description = get_field('auction_result_description');
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
        <h2><?php echo $title; ?></h2>
        <div class="auction_result-text p18"><?php echo $description; ?></div>
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
                    <?php for ($y=1920; $y<=date('Y'); $y++): ?>
                        <option value="<?php echo $y; ?>" <?php selected($_GET['year_from'] ?? '', $y); ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
                <p>To</p>
                <select name="year_to">
                    <option value="">To</option>
                    <?php for ($y=1920; $y<=date('Y'); $y++): ?>
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
        <div class="auction_result-list">
            <?php
            $args = array(
                'post_type'      => 'vehicles',
                'posts_per_page' => !empty($_GET['posts_per_page']) ? intval($_GET['posts_per_page']) : 6,
                'post_status'    => 'publish',
                's'              => isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '',
                'meta_query'     => array(),
            );

            if (!empty($_GET['search_vehicle'])) {
                $args['s'] = sanitize_text_field($_GET['search_vehicle']);
            }

            if (!empty($_GET['model'])) {
                $args['s'] = sanitize_text_field($_GET['model']);
            }

            // if (!empty($_GET['status']) && $_GET['status'] === 'available') {
            //     $args['meta_query'][] = array(
            //         'key'   => 'vehicle_current_lot',
            //         'value' => 'available',
            //     );
            // }

            if (!empty($_GET['year_from']) || !empty($_GET['year_to'])) {
                $year_from = !empty($_GET['year_from']) ? intval($_GET['year_from']) : 0;
                $year_to   = !empty($_GET['year_to']) ? intval($_GET['year_to']) : 9999;

                $args['meta_query'][] = array(
                    'key'     => 'vehicle_year',
                    'value'   => array($year_from, $year_to),
                    'type'    => 'NUMERIC',
                    'compare' => 'BETWEEN'
                );
            }

            if (!empty($_GET['orderby'])) {
                if ($_GET['orderby'] === 'lot') {
                    $args['orderby']  = 'meta_value_num';
                    $args['meta_key'] = 'vehicle_current_lot';
                } elseif ($_GET['orderby'] === 'estimate') {
                    $args['orderby']  = 'meta_value_num';
                    $args['meta_key'] = 'vehicle_estimate';
                }
            }


            $query = new WP_Query($args);

            if ($query->have_posts()) :
                while ($query->have_posts()) : $query->the_post();

                    $vehicle_place             = get_field('vehicle_place');
                    $vehicle_current_lot       = get_field('vehicle_current_lot');
                    $registration_no           = get_field('registration_no');
                    $chassis_no                = get_field('chassis_no');
                    $vehicle_mot               = get_field('vehicle_mot');
                    $vehicle_estimate          = get_field('vehicle_estimate');
                    $vehicle_short_description = get_field('vehicle_short_description');

                    $image = get_the_post_thumbnail_url(get_the_ID(), 'full');
                    if (!$image) {
                        $image = IMG . '/car.png';
                    }
                    ?>
                    
                    <div class="auction_result-list-item">
                        <div class="auction_result-list-img">
                            <img class="w-100" src="<?php echo esc_url($image); ?>" alt="<?php the_title(); ?>">
                        </div>
                        <div class="auction_result-list-info">
                            <h3><?php the_title(); ?></h3>
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
                                    <?php if ($vehicle_estimate): ?>
                                        <p>Estimated at</p>
                                        <p class="gold-text"><?php echo esc_html($vehicle_estimate); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($vehicle_short_description): ?>
                                <p class="auction_result-list-description">
                                    <?php echo esc_html($vehicle_short_description); ?>
                                </p>
                            <?php endif; ?>
                            <a href="<?php the_permalink(); ?>" class="permalink_border">
                                Enquire Now
                                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                                    <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    <?php
                endwhile;
                wp_reset_postdata();
            else :
                echo '<p class="auction_result-message">No results found.</p>';
            endif;

            // Pagination
            $big = 999;
            echo '<div class="auction_result-pagination">';
            echo paginate_links( array(
                'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                'format'    => '?paged=%#%',
                'current'   => max( 1, get_query_var('paged') ),
                'total'     => $query->max_num_pages,
                'prev_text' => __('<svg xmlns="http://www.w3.org/2000/svg" width="19" height="14" viewBox="0 0 19 14" fill="none">
  <path d="M19 7L1.00049 7M1.00049 7L7.00049 13M1.00049 7L7.0005 0.999999" stroke="#8C6E47"/>
</svg>'),
                'next_text' => __('<svg xmlns="http://www.w3.org/2000/svg" width="19" height="14" viewBox="0 0 19 14" fill="none">
  <path d="M-7.15494e-08 7L17.9995 7M17.9995 7L11.9995 1M17.9995 7L11.9995 13" stroke="#8C6E47"/>
</svg>'),
            ) );
            echo '</div>';
            ?>
        </div>
    </div>
    <div class="advertise_container auction_result-form">
        <h2>Contact Sales Department</h2>
        <div class="advertise_form">
            <?php echo do_shortcode('[gravityform id="2" title="true" ajax="true"]'); ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>