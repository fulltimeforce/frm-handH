<?php
/*
    Template name: refine-your-search
*/

get_header();

get_banner(
    'Homepage / Refine Your Search',
    get_the_post_thumbnail_url(get_the_ID(), 'full'),
    'Refine Your Search'
);

// ===== Opciones seguras =====
$defaults = [
    'min_year'  => 1920,
    'post_type' => 'vehicles',
];
$opt = $defaults;

// ===== Paginación y per page =====
$paged = max(1, get_query_var('paged') ? (int)get_query_var('paged') : (int)get_query_var('page'));
$ppp   = isset($_GET['posts_per_page']) ? max(1, (int)$_GET['posts_per_page']) : 6;

// ===== GET params =====
$q               = isset($_GET['search_vehicle'])     ? sanitize_text_field($_GET['search_vehicle'])     : '';
$vehicle_status  = isset($_GET['vehicle_status'])     ? sanitize_text_field($_GET['vehicle_status'])     : 'available';
$year_from_param = isset($_GET['year_from'])          ? sanitize_text_field($_GET['year_from'])          : '';
$year_to_param   = isset($_GET['year_to'])            ? sanitize_text_field($_GET['year_to'])            : '';
$brand_slug      = isset($_GET['vehicle_brand'])      ? sanitize_text_field($_GET['vehicle_brand'])      : '';
$cat_slug        = isset($_GET['vehicle_categories']) ? sanitize_text_field($_GET['vehicle_categories']) : '';
$lots_raw        = isset($_GET['lots'])               ? strtolower(sanitize_text_field($_GET['lots']))   : 'current';
$lots            = in_array($lots_raw, ['current', 'past'], true) ? $lots_raw : 'current';
$order_by        = isset($_GET['order_by']) ? sanitize_text_field($_GET['order_by']) : '';

// Campo meta de fecha/hora a comparar (ajusta si tu clave es otra)
$auction_date_meta = 'auction_date_latest';

// Hora “ahora” en el timezone de WP, al minuto (para coincidir con tu formato YYYY-mm-dd HH:ii)
$now_minute = date_i18n('Y-m-d H:i', current_time('timestamp'));

// ===== Meta query =====
$meta_query = ['relation' => 'AND'];

// Status (ACF: status)
if ($vehicle_status !== '') {
    $status_regex = '^[[:space:]]*' . preg_quote(strtolower($vehicle_status), '~') . '[[:space:]]*$';
    $meta_query[] = [
        'key'     => 'status',
        'value'   => $status_regex,
        'compare' => 'REGEXP',
    ];
}

// Filtro CURRENT/PAST por fecha del meta $auction_date_meta (comparación lexicográfica segura)
if ($lots === 'current') {
    $meta_query[] = [
        'key'     => $auction_date_meta,
        'value'   => $now_minute,
        'compare' => '>=',
        'type'    => 'CHAR',
    ];
} else { // past
    $meta_query[] = [
        'key'     => $auction_date_meta,
        'value'   => $now_minute,
        'compare' => '<',
        'type'    => 'CHAR',
    ];
}

// Rango por año sobre el mismo meta (coherencia)
$year_from = (ctype_digit($year_from_param) ? (int)$year_from_param : null);
$year_to   = (ctype_digit($year_to_param)   ? (int)$year_to_param   : null);

if ($year_from && $year_to) {
    if ($year_from > $year_to) {
        [$year_from, $year_to] = [$year_to, $year_from];
    }
    $start_dt = sprintf('%04d-01-01 00:00', $year_from);
    $end_dt   = sprintf('%04d-12-31 23:59', $year_to);
    $meta_query[] = [
        'key'     => $auction_date_meta,
        'value'   => [$start_dt, $end_dt],
        'compare' => 'BETWEEN',
        'type'    => 'CHAR',
    ];
} elseif ($year_from) {
    $start_dt = sprintf('%04d-01-01 00:00', $year_from);
    $meta_query[] = [
        'key'     => $auction_date_meta,
        'value'   => $start_dt,
        'compare' => '>=',
        'type'    => 'CHAR',
    ];
} elseif ($year_to) {
    $end_dt = sprintf('%04d-12-31 23:59', $year_to);
    $meta_query[] = [
        'key'     => $auction_date_meta,
        'value'   => $end_dt,
        'compare' => '<=',
        'type'    => 'CHAR',
    ];
}

// Solo con thumbnail
$meta_query[] = [
    'key'     => '_thumbnail_id',
    'compare' => 'EXISTS',
];

// ===== Tax query =====
$tax_query = [];
if ($brand_slug !== '') {
    $tax_query[] = [
        'taxonomy' => 'vehicle_brand',
        'field'    => 'slug',
        'terms'    => [$brand_slug],
    ];
}
if ($cat_slug !== '') {
    $tax_query[] = [
        'taxonomy' => 'vehicle_category',
        'field'    => 'slug',
        'terms'    => [$cat_slug],
    ];
}

// ===== Query =====
// Orden lógico: actuales ASC (la más próxima primero), pasados DESC (lo más reciente primero)
$order_dir = ($lots === 'current') ? 'ASC' : 'DESC';

$argsVehicle = [
    'post_type'      => $opt['post_type'],
    'posts_per_page' => $ppp,
    'paged'          => $paged,
    'meta_query'     => $meta_query,
    'meta_key'       => $auction_date_meta,
    'orderby'        => 'meta_value',
    'order'          => $order_dir,
    'meta_type'      => 'CHAR', // formateo YYYY-mm-dd HH:ii
];

if ($q !== '') {
    $argsVehicle['s'] = $q;
}
if (!empty($tax_query)) {
    $argsVehicle['tax_query'] = $tax_query;
}

if ($order_by === 'lot') {
    // Asegura que solo entren posts con número de lote
    $meta_query[] = [
        'key'     => 'lot_number_latest',
        'compare' => 'EXISTS',
    ];
    $argsVehicle['meta_query'] = $meta_query;

    // Orden numérico por el meta del lote
    $argsVehicle['meta_key']  = 'lot_number_latest';
    $argsVehicle['orderby']   = 'meta_value_num';
    $argsVehicle['order']     = 'ASC'; // o 'DESC' si prefieres
    unset($argsVehicle['meta_type']);   // no hace falta cuando usamos meta_value_num
}

$vehicles = new WP_Query($argsVehicle);

// Years (para selects)
$yf_sel  = $year_from_param;
$yt_sel  = $year_to_param;
$minYear = (int)$opt['min_year'];
$maxYear = (int)date('Y');

?>

<section class="auction_vehicles without_spacing">
    <div class="auction_vehicles-container">
        <form class="auction_result-filter" method="get" action="">
            <input type="hidden" name="lots" value="<?php echo esc_attr($lots); ?>">
            <input type="hidden" name="order_by" value="<?php echo esc_attr($order_by); ?>">

            <div class="auction_result-filter-search">
                <input type="search" name="search_vehicle" placeholder="Search for..." value="<?php echo esc_attr($q); ?>">
                <button type="submit">Go</button>
            </div>

            <div class="auction_result-filter-select">
                <select name="search_mode">
                    <option value=""><?php esc_html_e('Search all words any order'); ?></option>
                </select>
            </div>

            <div class="auction_result-filter-select">
                <select name="order_by">
                    <option value=""><?php esc_html_e('Sort by'); ?></option>
                    <option value="lot" <?php selected($_GET['order_by'] ?? '', 'lot'); ?>><?php esc_html_e('Sort by lot number'); ?></option>
                </select>
            </div>

            <div class="auction_result-filter-select">
                <select name="vehicle_categories" onchange="this.form.submit()">
                    <option value=""><?php esc_html_e('Main Categories'); ?></option>
                    <?php
                    $cats = get_terms([
                        'taxonomy'   => 'vehicle_category',
                        'hide_empty' => true,
                        'parent'     => 0,
                        'orderby'    => 'name',
                        'order'      => 'ASC',
                    ]);
                    if (!is_wp_error($cats) && $cats):
                        foreach ($cats as $t): ?>
                            <option value="<?php echo esc_attr($t->slug); ?>" <?php selected($cat_slug, $t->slug); ?>>
                                <?php echo esc_html($t->name); ?>
                            </option>
                    <?php endforeach;
                    endif; ?>
                </select>
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
                    <option value=""><?php esc_html_e('Artist/Maker/Brand'); ?></option>
                    <?php if (!is_wp_error($brands) && $brands): ?>
                        <?php foreach ($brands as $term): ?>
                            <option value="<?php echo esc_attr($term->slug); ?>" <?php selected($brand_slug, $term->slug); ?>>
                                <?php echo esc_html($term->name); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="auction_result-filter-year">
                <select name="year_from" onchange="this.form.submit()">
                    <option value=""><?php esc_html_e('From'); ?></option>
                    <?php for ($y = $minYear; $y <= $maxYear; $y++): ?>
                        <option value="<?php echo $y; ?>" <?php selected($yf_sel, (string)$y); ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
                <p><?php esc_html_e('To'); ?></p>
                <select name="year_to" onchange="this.form.submit()">
                    <option value=""><?php esc_html_e('To'); ?></option>
                    <?php for ($y = $minYear; $y <= $maxYear; $y++): ?>
                        <option value="<?php echo $y; ?>" <?php selected($yt_sel, (string)$y); ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
            </div>

        </form>
    </div>
</section>

<section class="refine_vehicles">
    <div class="refine_vehicles-container">

        <div class="refine_vehicles-module" data-state="2">
            <div class="refine_modules">
                <div class="refine_buttons">
                    <button type="button" class="<?php echo $lots === 'current' ? 'active' : ''; ?>" data-lots="current">Current lots</button>
                    <button type="button" class="<?php echo $lots === 'past'    ? 'active' : ''; ?>" data-lots="past">Past lots</button>
                </div>
                <div class="refine_views">
                    <form method="get" action="">
                        <input type="hidden" name="lots" value="<?php echo esc_attr($lots); ?>">
                        <div class="auction_result-filter-page">
                            <p>
                                <?php esc_html_e('Showing'); ?>
                                <select id="blog-perpage" class="blog_section-filter-page" name="posts_per_page" onchange="this.form.submit()">
                                    <option value="6" <?php selected((int)$ppp, 6);  ?>>6</option>
                                    <option value="12" <?php selected((int)$ppp, 12); ?>>12</option>
                                    <option value="24" <?php selected((int)$ppp, 24); ?>>24</option>
                                </select>
                                <?php esc_html_e('Per Page'); ?>
                            </p>
                        </div>
                        <div class="filter-view">
                            <button type="button" class="change_view" data-view="1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="15" viewBox="0 0 22 15" fill="none">
                                    <path d="M1 7.66667H1.01111M1 14.3333H1.01111M1 1H1.01111M6.55556 7.66667H21M6.55556 14.3333H21M6.55556 1H21" stroke="#8C6E47" stroke-width="1.11111" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                            <button type="button" class="change_view" data-view="2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none">
                                    <path d="M11 1V21M1 11H21M3.22222 1H18.7778C20.0051 1 21 1.99492 21 3.22222V18.7778C21 20.0051 20.0051 21 18.7778 21H3.22222C1.99492 21 1 20.0051 1 18.7778V3.22222C1 1.99492 1.99492 1 3.22222 1Z" stroke="#8C6E47" stroke-width="1.11111" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="refine_vehicles-spacing">
                <?php if ($vehicles->have_posts()): ?>
                    <!-- GRID -->
                    <div class="refine_cards refine_grid">
                        <?php while ($vehicles->have_posts()) : $vehicles->the_post(); ?>
                            <?php hnh_render_vehicle_card(get_the_ID()); ?>
                        <?php endwhile; ?>
                    </div>

                    <?php $vehicles->rewind_posts(); ?>

                    <!-- LIST -->
                    <div class="refine_cards refine_list">
                        <?php while ($vehicles->have_posts()) : $vehicles->the_post(); ?>
                            <?php hnh_render_vehicle_item(get_the_ID()); ?>
                        <?php endwhile; ?>
                    </div>

                    <?php
                    // === Paginación ===
                    $pagination = paginate_links([
                        'total'     => (int) $vehicles->max_num_pages,
                        'current'   => $paged,
                        'mid_size'  => 2,
                        'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="19" height="14" viewBox="0 0 19 14" fill="none"><path d="M19 7L1.00049 7M1.00049 7L7.00049 13M1.00049 7L7.0005 0.999999" stroke="#8C6E47"/></svg>',
                        'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="19" height="14" viewBox="0 0 19 14" fill="none"><path d="M-7.15494e-08 7L17.9995 7M17.9995 7L11.9995 1M17.9995 7L11.9995 13" stroke="#8C6E47"/></svg>',
                        'add_args'  => array_filter([
                            'lots'              => $lots,
                            'search_vehicle'    => $q,
                            'vehicle_brand'     => $brand_slug,
                            'vehicle_categories' => $cat_slug,
                            'order_by'          => $_GET['order_by'] ?? '',
                            'vehicle_status'    => $vehicle_status,
                            'year_from'         => $year_from_param,
                            'year_to'           => $year_to_param,
                            'posts_per_page'    => $ppp,
                        ], static fn($v) => $v !== '' && $v !== null),
                    ]);

                    if ($pagination) {
                        echo '<div class="auction_result-pagination">' . $pagination . '</div>';
                    }

                    wp_reset_postdata();
                    ?>

                <?php else: ?>
                    <div class="no-one">
                        <p><?php esc_html_e('No results found'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<?php get_footer(); ?>

<script>
    // Toggle grid/list (ya lo tenías)
    let change_view = document.querySelectorAll('.change_view'),
        vehicles_module = document.querySelector('.refine_vehicles-module');

    if (change_view && vehicles_module) {
        Array.from(change_view).forEach(view => {
            view.addEventListener('click', (e) => {
                e.preventDefault();
                let id = e.currentTarget.getAttribute('data-view');
                vehicles_module.setAttribute('data-state', id);
            });
        });
    }

    // Toggle Current/Past lots: setea hidden input 'lots' y envía el formulario principal
    const lotsButtons = document.querySelectorAll('.refine_buttons button[data-lots]');
    const filterForm = document.querySelector('form.auction_result-filter');
    if (lotsButtons && filterForm) {
        lotsButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const val = btn.getAttribute('data-lots') || 'current';
                // actualiza hidden en el form
                let hidden = filterForm.querySelector('input[name="lots"]');
                if (!hidden) {
                    hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'lots';
                    filterForm.appendChild(hidden);
                }
                hidden.value = val;

                // estado visual
                lotsButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                filterForm.submit();
            });
        });
    }
</script>