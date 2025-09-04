<?php
/*
    Template name: auction-results
*/

get_header();

get_banner('Homepage / classic auctions / Auction Results', get_the_post_thumbnail_url(get_the_ID(), 'full'), 'Auction Results');

$today = current_time('mysql');

/* paginación */
$paged = max(1, get_query_var('paged') ? (int) get_query_var('paged') : (int) get_query_var('page'));

/* GET params */
$currentYear = (int) date('Y');
$ppp         = isset($_GET['posts_per_page']) ? max(1, (int) $_GET['posts_per_page']) : 6;

$sale_type   = isset($_GET['sale_type']) ? sanitize_text_field($_GET['sale_type']) : 'all';
$year_param   = isset($_GET['auction_year']) ? sanitize_text_field($_GET['auction_year']) : (string) $currentYear;

/* Opcional: lista blanca de sale_types tal como en ACF (value : label) */
$allowed_sale_types = ['all', 'motorcars', 'motorcycles', 'automobilia', 'bicycles', 'liveonline'];
if (!in_array($sale_type, $allowed_sale_types, true)) {
    $sale_type = 'all';
}

/* meta query (fechas pasadas + filtro por año + sale_type por ACF) */
$meta_query = [
    'relation' => 'AND',
    [
        'key'     => 'auction_date',
        'value'   => $today,
        'compare' => '<',
        'type'    => 'DATETIME',
    ],
];

if ($year_param && $year_param !== 'all' && ctype_digit($year_param)) {
    $y = (int) $year_param;
    $start_of_year = sprintf('%04d-01-01 00:00:00', $y);
    $end_of_year   = sprintf('%04d-12-31 23:59:59', $y);

    $meta_query[] = [
        'key'     => 'auction_date',
        'value'   => [$start_of_year, $end_of_year],
        'compare' => 'BETWEEN',
        'type'    => 'DATETIME',
    ];
}

if ($sale_type && $sale_type !== 'all') {
    $meta_query[] = [
        'key'     => 'sale_type',   // ACF field name
        'value'   => $sale_type,    // e.g., 'motorcars'
        'compare' => '=',
    ];
}

$argsAuction = [
    'post_type'      => 'auction',
    'posts_per_page' => $ppp,
    'paged'          => $paged,
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
    'meta_key'       => 'auction_date',
    'meta_type'      => 'DATETIME',
    'meta_query'     => $meta_query,
];

$past_auctions = new WP_Query($argsAuction);
?>

<section class="auction_result-tab">
    <div class="container" style="border: none;">
        <div>
            <a class="active">PAST AUCTIONS</a>
            <a href="<?php echo esc_url(home_url('buy-it-now')); ?>">Unsold Vehicles</a>
        </div>
    </div>
</section>

<section class="auction_vehicles">
    <div class="auction_vehicles-container">

        <form class="auction_result-filter" method="get" action="">
            <div class="auction_result-filter-select">
                <select name="sale_type" onchange="this.form.submit()">
                    <option value="all" <?php selected(($_GET['sale_type'] ?? 'all'), 'all'); ?>>All Sale Types</option>
                    <option value="motorcars" <?php selected(($_GET['sale_type'] ?? ''), 'motorcars'); ?>>Motor Cars</option>
                    <option value="motorcycles" <?php selected(($_GET['sale_type'] ?? ''), 'motorcycles'); ?>>Motorcycles</option>
                </select>
            </div>

            <div class="auction_result-filter-select">
                <select name="auction_year" onchange="this.form.submit()">
                    <?php
                    $selectedYear = $_GET['auction_year'] ?? (string) $currentYear;
                    for ($i = $currentYear; $i >= 2008; $i--): ?>
                        <option value="<?php echo $i; ?>" <?php selected($selectedYear, (string)$i); ?>>
                            <?php echo $i; ?>
                        </option>
                    <?php endfor; ?>
                    <option value="all" <?php selected($selectedYear, 'all'); ?>>All Years</option>
                </select>
            </div>

            <div class="auction_result-filter-page">
                <p>
                    Showing
                    <select id="blog-perpage" class="blog_section-filter-page" name="posts_per_page">
                        <option value="6" <?php selected((int)($_GET['posts_per_page'] ?? $ppp), 6); ?>>6</option>
                        <option value="12" <?php selected((int)($_GET['posts_per_page'] ?? $ppp), 12); ?>>12</option>
                        <option value="24" <?php selected((int)($_GET['posts_per_page'] ?? $ppp), 24); ?>>24</option>
                    </select>
                    Per Page
                </p>
            </div>
        </form>

        <?php if ($past_auctions->have_posts()): ?>
            <div class="auction_result-list past_auctions">

                <?php while ($past_auctions->have_posts()) : ?>
                    <?php
                    $past_auctions->the_post();
                    $auction_id = get_the_ID();
                    $venue_id   = (int) get_field('template_venue', $auction_id);

                    hnh_render_auction_card($auction_id, $venue_id);
                    ?>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>

            </div>

            <?php
            $pagination = paginate_links([
                'total'     => (int) $past_auctions->max_num_pages,
                'current'   => $paged,
                'mid_size'  => 2,
                'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="19" height="14" viewBox="0 0 19 14" fill="none"><path d="M19 7L1.00049 7M1.00049 7L7.00049 13M1.00049 7L7.0005 0.999999" stroke="#8C6E47"/></svg>',
                'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="19" height="14" viewBox="0 0 19 14" fill="none"><path d="M-7.15494e-08 7L17.9995 7M17.9995 7L11.9995 1M17.9995 7L11.9995 13" stroke="#8C6E47"/></svg>',
                'add_args'  => array_filter([
                    'sale_type'      => $sale_type,
                    'auction_year'   => $year_param,
                    'posts_per_page' => $ppp,
                ]),
            ]);

            if ($pagination) {
                echo '<div class="auction_result-pagination">' . $pagination . '</div>';
            }
            ?>
        <?php else: ?>
            <div class="no-one">
                <p>No results found</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>

<script>
    document.addEventListener('change', function(e) {
        if (e.target && e.target.id === 'blog-perpage') {
            e.target.form.submit();
        }
    });
</script>