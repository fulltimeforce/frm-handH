<?php
/*
    Template name: auction-results
*/

get_header();

get_banner('Homepage / classic auctions / Auction Results', get_the_post_thumbnail_url(get_the_ID(), 'full'), 'Auction Results');

$today = current_time('mysql');

/* paginación */
$paged = max(1, get_query_var('paged') ? (int) get_query_var('paged') : (int) get_query_var('page'));

/* per page desde el selector (fallback a 6) */
$ppp = isset($_GET['posts_per_page']) ? max(1, (int) $_GET['posts_per_page']) : 6;

$argsAuction = array(
    'post_type'      => 'auction',
    'posts_per_page' => $ppp,
    'paged'          => $paged,
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
    'meta_key'       => 'auction_date',
    'meta_type'      => 'DATETIME',
    'meta_query'     => array(
        array(
            'key'     => 'auction_date',
            'value'   => $today,
            'compare' => '<',
            'type'    => 'DATETIME'
        )
    )
);

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
                <select name="type">
                    <option value="allsaletypes">All Sale Types</option>
                    <option value="motorcars">Motor Cars</option>
                    <option value="motorcycles">Motorcycles</option>
                </select>
            </div>
            <div class="auction_result-filter-select">
                <select name="year">
                    <option value="">All Years</option>
                    <option value="2025">2025</option>
                    <option value="2024">2024</option>
                    <option value="2023">2023</option>
                    <option value="2022">2022</option>
                    <option value="2021">2021</option>
                    <option value="2020">2020</option>
                    <option value="2019">2019</option>
                    <option value="2018">2018</option>
                    <option value="2018">2018</option>
                </select>
            </div>
            <div class="auction_result-filter-page">
                <p>
                    Showing
                    <select id="blog-perpage" class="blog_section-filter-page" name="posts_per_page">
                        <option value="6" <?php selected($_GET['posts_per_page'] ?? '', 6); ?>>6</option>
                        <option value="12" <?php selected($_GET['posts_per_page'] ?? '', 12); ?>>12</option>
                        <option value="24" <?php selected($_GET['posts_per_page'] ?? '', 24); ?>>24</option>
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
            $pagination = paginate_links(array(
                'total'     => (int) $past_auctions->max_num_pages,
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
                echo '<div class="auction_result-pagination">' . $pagination . '</div>';
            }
            ?>
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