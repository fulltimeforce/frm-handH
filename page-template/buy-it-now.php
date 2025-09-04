<?php
/*
    Template name: buy-it-now
*/

get_header();

get_banner(
    'Homepage / classic auctions / Auction Results',
    get_the_post_thumbnail_url(get_the_ID(), 'full'),
    'Auction Results'
);

/* paginación */
$paged = max(1, get_query_var('paged') ? (int) get_query_var('paged') : (int) get_query_var('page'));

/* per page desde el selector (fallback a 6) */
$ppp = isset($_GET['posts_per_page']) ? max(1, (int) $_GET['posts_per_page']) : 6;

/* GET params */
$q               = isset($_GET['search_vehicle']) ? sanitize_text_field($_GET['search_vehicle']) : '';
$vehicle_status  = isset($_GET['vehicle_status']) ? sanitize_text_field($_GET['vehicle_status']) : 'available';
$year_from_param = isset($_GET['year_from'])      ? sanitize_text_field($_GET['year_from'])      : '';
$year_to_param   = isset($_GET['year_to'])        ? sanitize_text_field($_GET['year_to'])        : '';
$brand_slug      = isset($_GET['vehicle_brand'])  ? sanitize_text_field($_GET['vehicle_brand'])  : '';

/* Meta query builder */
$meta_query = ['relation' => 'AND'];

/* Status (ACF: status) — exacto, ignorando mayúsculas y espacios */
if ($vehicle_status !== '') {
    $status_regex = '^[[:space:]]*' . preg_quote(strtolower($vehicle_status), '~') . '[[:space:]]*$';
    $meta_query[] = [
        'key'     => 'status',
        'value'   => $status_regex,
        'compare' => 'REGEXP',
    ];
}

/* Rango por año sobre ACF: auction_date_latest (YYYY-mm-dd HH:ii) */
$year_from = (ctype_digit($year_from_param) ? (int) $year_from_param : null);
$year_to   = (ctype_digit($year_to_param)   ? (int) $year_to_param   : null);

if ($year_from && $year_to) {
    if ($year_from > $year_to) {
        [$year_from, $year_to] = [$year_to, $year_from];
    }
    $start_dt = sprintf('%04d-01-01 00:00:00', $year_from);
    $end_dt   = sprintf('%04d-12-31 23:59:59', $year_to);
    $meta_query[] = [
        'key'     => 'auction_date_latest',
        'value'   => [$start_dt, $end_dt],
        'compare' => 'BETWEEN',
        'type'    => 'DATETIME',
    ];
} elseif ($year_from) {
    $start_dt = sprintf('%04d-01-01 00:00:00', $year_from);
    $meta_query[] = [
        'key'     => 'auction_date_latest',
        'value'   => $start_dt,
        'compare' => '>=',
        'type'    => 'DATETIME',
    ];
} elseif ($year_to) {
    $end_dt = sprintf('%04d-12-31 23:59:59', $year_to);
    $meta_query[] = [
        'key'     => 'auction_date_latest',
        'value'   => $end_dt,
        'compare' => '<=',
        'type'    => 'DATETIME',
    ];
}

/* Tax query (brands) */
$tax_query = [];
if ($brand_slug !== '') {
    $tax_query[] = [
        'taxonomy' => 'vehicle_brand',
        'field'    => 'slug',
        'terms'    => [$brand_slug],
    ];
}

/* Query */
$argsVehicle = [
    'post_type'      => 'vehicles',
    'posts_per_page' => $ppp,
    'paged'          => $paged,
    'meta_query'     => $meta_query,
    'meta_key'       => 'auction_date_latest',
    'orderby'        => 'meta_value',
    'order'          => 'DESC',
    'meta_type'      => 'DATETIME',
];

if ($q !== '') {
    $argsVehicle['s'] = $q; // busca en título/contenido
}

if (!empty($tax_query)) {
    $argsVehicle['tax_query'] = $tax_query;
}

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
                <input type="search" name="search_vehicle" placeholder="Search for..." value="<?php echo esc_attr($q); ?>">
                <button type="submit">Go</button>
            </div>

            <div class="auction_result-filter-select">
                <?php
                $brands = get_terms([
                    'taxonomy'   => 'vehicle_brand',
                    'hide_empty' => true,
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                ]);
                ?>
                <select name="vehicle_brand" onchange="this.form.submit()">
                    <option value=""><?php esc_html_e('All Brands'); ?></option>
                    <?php if (!is_wp_error($brands) && $brands): ?>
                        <?php foreach ($brands as $term): ?>
                            <option value="<?php echo esc_attr($term->slug); ?>" <?php selected($brand_slug, $term->slug); ?>>
                                <?php echo esc_html($term->name); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="auction_result-filter-select">
                <select name="order_by">
                    <option value="">Sort by</option>
                    <option value="lot" <?php selected($_GET['order_by'] ?? '', 'lot'); ?>>Sort by lot number</option>
                    <option value="estimate" <?php selected($_GET['order_by'] ?? '', 'estimate'); ?>>Sort by Estimate</option>
                </select>
            </div>

            <div class="auction_result-filter-select">
                <select name="vehicle_status" onchange="this.form.submit()">
                    <?php
                    $status_selected = $vehicle_status ?: 'available';
                    $status_opts = ['available' => 'Available for Sale', 'appraisal' => 'Appraisal', 'allocated' => 'Allocated', 'sold' => 'Sold'];
                    foreach ($status_opts as $val => $label): ?>
                        <option value="<?php echo esc_attr($val); ?>" <?php selected($status_selected, $val); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="auction_result-filter-year">
                <?php
                $yf_sel = $year_from_param;
                $yt_sel = $year_to_param;
                $minYear = 1920;
                $maxYear = (int) date('Y');
                ?>
                <select name="year_from" onchange="this.form.submit()">
                    <option value="">From</option>
                    <?php for ($y = $minYear; $y <= $maxYear; $y++): ?>
                        <option value="<?php echo $y; ?>" <?php selected($yf_sel, (string)$y); ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
                <p>To</p>
                <select name="year_to" onchange="this.form.submit()">
                    <option value="">To</option>
                    <?php for ($y = $minYear; $y <= $maxYear; $y++): ?>
                        <option value="<?php echo $y; ?>" <?php selected($yt_sel, (string)$y); ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="auction_result-filter-page">
                <p>
                    Showing
                    <select id="blog-perpage" class="blog_section-filter-page" name="posts_per_page" onchange="this.form.submit()">
                        <option value="6" <?php selected((int)$ppp, 6);  ?>>6</option>
                        <option value="12" <?php selected((int)$ppp, 12); ?>>12</option>
                        <option value="24" <?php selected((int)$ppp, 24); ?>>24</option>
                    </select>
                    Per Page
                </p>
            </div>
        </form>

        <?php if ($vehicles->have_posts()): ?>
            <div class="w-100">
                <?php while ($vehicles->have_posts()) : $vehicles->the_post(); ?>
                    <?php
                    $id        = get_the_ID();
                    hnh_render_vehicle_card($id);
                    ?>
                <?php endwhile;
                wp_reset_postdata(); ?>
            </div>

            <?php
            $pagination = paginate_links([
                'total'     => (int) $vehicles->max_num_pages,
                'current'   => $paged,
                'mid_size'  => 2,
                'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="19" height="14" viewBox="0 0 19 14" fill="none"><path d="M19 7L1.00049 7M1.00049 7L7.00049 13M1.00049 7L7.0005 0.999999" stroke="#8C6E47"/></svg>',
                'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="19" height="14" viewBox="0 0 19 14" fill="none"><path d="M-7.15494e-08 7L17.9995 7M17.9995 7L11.9995 1M17.9995 7L11.9995 13" stroke="#8C6E47"/></svg>',
                'add_args'  => array_filter([
                    'search_vehicle' => $q,
                    'vehicle_brand'  => $brand_slug,          // ← PRESERVAR BRAND
                    'vehicle_model'  => $_GET['vehicle_model']  ?? '',
                    'order_by'       => $_GET['order_by']       ?? '',
                    'vehicle_status' => $vehicle_status,
                    'year_from'      => $year_from_param,
                    'year_to'        => $year_to_param,
                    'posts_per_page' => $ppp,
                ], static fn($v) => $v !== '' && $v !== null),
            ]);

            if ($pagination) {
                echo '<div class="auction_result-pagination with_border">' . $pagination . '</div>';
            }
            ?>
        <?php else: ?>
            <div class="no-one">
                <p>No results found</p>
            </div>
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