<?php

$auctions_title   = get_field('auctions_title');
$auctions_content = get_field('auctions_content');
$auctions_link    = get_field('auctions_link');

$consign_content_1 = get_field('consign_content_1');
$how_to_get_title = get_field('how_to_get_title');

$lat = get_field('lat');
$lng = get_field('lng');


$venue_id = isset($args['venue_id']) ? (int) $args['venue_id'] : 0;
$auction_id = isset($args['auction_id']) ? (int) $args['auction_id'] : 0;

if (is_singular('auction') && $venue_id) {
    $auctions_title   = get_field('auctions_title', $venue_id);
    $auctions_content = get_field('auctions_content', $venue_id);
    $auctions_link    = get_field('auctions_link', $venue_id);

    $consign_content_1 = get_field('consign_content_1', $venue_id);
    $how_to_get_title = get_field('how_to_get_title', $venue_id);

    $lat = get_field('lat', $venue_id);
    $lng = get_field('lng', $venue_id);
}
?>

<?php if ($auctions_title || $auctions_content || $auctions_link): ?>
    <section class="single_venue_content">
        <div class="single_venue_content-container">
            <div class="single_venue_content-top">
                <?php if ($auctions_title): ?>
                    <div class="single_venue_content-title">
                        <h2><?php echo esc_html($auctions_title); ?></h2>
                    </div>
                <?php endif; ?>

                <div class="single_venue_content-content">
                    <?php if ($auctions_content): ?>
                        <div class="content">
                            <?php echo wp_kses_post($auctions_content); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($auctions_link): ?>
                        <div class="actions">
                            <a href="<?php echo esc_url($auctions_link['url']); ?>" alt="<?php echo esc_html($auctions_link['title']); ?>" class="permalink_border">
                                <?php echo esc_html($auctions_link['title']); ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
                                    <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
                                </svg>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- ------------------------------------------------------------------------------------------------------ -->
<!-- ------------------------------------------------------------------------------------------------------ -->
<!-- ------------------------------------------------------------------------------------------------------ -->

<?php if (is_singular('auction')): ?>

    <?php
    // ===== Opciones seguras =====
    $auction_number = trim((string) get_field('sale_number', $auction_id));

    $defaults = [
        'post_type' => 'vehicles',
    ];
    $opt = $defaults;

    // ===== Paginación y per page =====
    $paged = isset($_GET['vp']) ? max(1, (int) $_GET['vp']) : 1;
    $ppp   = isset($_GET['posts_per_page']) ? max(1, (int)$_GET['posts_per_page']) : 6;

    // ===== GET params (sin years ni lots) =====
    $q               = isset($_GET['search_vehicle'])     ? sanitize_text_field($_GET['search_vehicle'])     : '';
    $brand_slug      = isset($_GET['vehicle_brand'])      ? sanitize_text_field($_GET['vehicle_brand'])      : '';
    $cat_slug        = isset($_GET['vehicle_categories']) ? sanitize_text_field($_GET['vehicle_categories']) : '';
    $order_by        = isset($_GET['order_by']) ? sanitize_text_field($_GET['order_by']) : 'lot';

    // Meta keys
    $auction_date_meta = 'auction_date_latest';

    // ===== Meta query =====
    $meta_query = ['relation' => 'AND'];

    // SOLO vehículos de esta subasta (número exacto)
    if ($auction_number !== '') {
        $meta_query[] = [
            'key'     => 'auction_number_latest',
            'value'   => $auction_number,
            'compare' => '=',
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
    // Por defecto ordena por fecha de subasta (texto YYYY-mm-dd HH:ii) DESC
    $argsVehicle = [
        'post_type'      => $opt['post_type'],
        'posts_per_page' => $ppp,
        'paged'          => $paged,
        'meta_query'     => $meta_query,
        'meta_key'       => $auction_date_meta,
        'orderby'        => 'meta_value',
        'order'          => 'DESC',
        'meta_type'      => 'CHAR',
    ];

    if ($q !== '') {
        $argsVehicle['s'] = $q;
    }
    if (!empty($tax_query)) {
        $argsVehicle['tax_query'] = $tax_query;
    }

    // ===== Orden dinámico según order_by =====
    switch ($order_by) {
        case 'lot':
            // Solo con número de lote y orden numérico
            $meta_query[] = [
                'key'     => 'lot_number_latest',
                'compare' => 'EXISTS',
            ];
            $argsVehicle['meta_query'] = $meta_query;

            $argsVehicle['meta_key'] = 'lot_number_latest';
            $argsVehicle['orderby']  = 'meta_value_num';
            $argsVehicle['order']    = 'ASC'; // cambia a DESC si lo prefieres
            unset($argsVehicle['meta_type']);
            break;

        case 'low-to-high':
            // Precio estimado bajo (ASC) – numérico
            $meta_query[] = [
                'key'     => 'estimate_low',
                'compare' => 'EXISTS',
            ];
            $argsVehicle['meta_query'] = $meta_query;

            $argsVehicle['meta_key'] = 'estimate_low';
            $argsVehicle['orderby']  = 'meta_value_num';
            $argsVehicle['order']    = 'ASC';
            unset($argsVehicle['meta_type']);
            break;

        case 'high-to-low':
            // Precio estimado bajo (DESC) – numérico
            $meta_query[] = [
                'key'     => 'estimate_low',
                'compare' => 'EXISTS',
            ];
            $argsVehicle['meta_query'] = $meta_query;

            $argsVehicle['meta_key'] = 'estimate_low';
            $argsVehicle['orderby']  = 'meta_value_num';
            $argsVehicle['order']    = 'DESC';
            unset($argsVehicle['meta_type']);
            break;

        case 'oldest':
            // Fecha más antigua primero (string "Y-m-d H:i")
            $argsVehicle['meta_key']  = $auction_date_meta;
            $argsVehicle['orderby']   = 'meta_value';
            $argsVehicle['order']     = 'ASC';
            $argsVehicle['meta_type'] = 'CHAR';
            break;

        case 'newest':
            // Fecha más reciente primero
            $argsVehicle['meta_key']  = $auction_date_meta;
            $argsVehicle['orderby']   = 'meta_value';
            $argsVehicle['order']     = 'DESC';
            $argsVehicle['meta_type'] = 'CHAR';
            break;

        default:
            // Deja la orden base por fecha según CURRENT/PAST
            break;
    }

    $vehicles = new WP_Query($argsVehicle);

    ?>

    <style>
        .refine_vehicles[data-state="1"] .refine_vehicles-spacing {
            background: rgba(255, 255, 255, 0);
            padding: 0;
            border-color: rgba(255, 255, 255, 0);
        }
    </style>

    <section class="refine_vehicles" data-state="2">

        <section class="auction_vehicles" style="padding:0 !important">
            <div class="auction_vehicles-container">
                <form class="auction_result-filter filter_in_single_auction" method="get" action="" style="margin:0 !important">
                    <input type="hidden" name="order_by" value="<?php echo esc_attr($order_by); ?>">

                    <div class="auction_result-filter-search">
                        <input type="search" name="search_vehicle" placeholder="Search for..." value="<?php echo esc_attr($q); ?>">
                        <button type="submit">Go</button>
                    </div>

                    <?php if (NOT_APPEAR): ?>
                        <div class="auction_result-filter-select">
                            <select name="search_mode">
                                <option value=""><?php esc_html_e('Search all words any order'); ?></option>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="auction_result-filter-select">
                        <select name="order_by" onchange="this.form.submit()">
                            <option value=""><?php esc_html_e('Sort by'); ?></option>
                            <option value="lot" <?php selected($order_by, 'lot');          ?>><?php esc_html_e('Sort by lot number'); ?></option>
                            <option value="low-to-high" <?php selected($order_by, 'low-to-high');  ?>><?php esc_html_e('Estimate/Price - Low to High'); ?></option>
                            <option value="high-to-low" <?php selected($order_by, 'high-to-low');  ?>><?php esc_html_e('Estimate/Price - High to Low'); ?></option>
                            <option value="oldest" <?php selected($order_by, 'oldest');       ?>><?php esc_html_e('Date - Oldest first'); ?></option>
                            <option value="newest" <?php selected($order_by, 'newest');       ?>><?php esc_html_e('Date - Newest first'); ?></option>
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

                    <div></div>

                    <div class="auction_result-filter-page relative">
                        <p>
                            <?php esc_html_e('Showing'); ?>
                            <select id="blog-perpage" class="blog_section-filter-page" name="posts_per_page" onchange="this.form.submit()">
                                <option value="6" <?php selected((int)$ppp, 6);  ?>>6</option>
                                <option value="12" <?php selected((int)$ppp, 12); ?>>12</option>
                                <option value="24" <?php selected((int)$ppp, 24); ?>>24</option>
                            </select>
                            <?php esc_html_e('Per Page'); ?>
                        </p>

                        <div class="filter-view">
                            <button type="button" class="change_view" data-view="1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="15" viewBox="0 0 22 15" fill="none">
                                    <path d="M1 7.66667H1.01111M1 14.3333H1.01111M1 1H1.01111M6.55556 7.66667H21M6.55556 14.3333H21M6.55556 1H21" stroke="#8C6E47" stroke-width="1.11111" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </button>
                            <button type="button" class="change_view" data-view="2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none">
                                    <path d="M11 1V21M1 11H21M3.22222 1H18.7778C20.0051 1 21 1.99492 21 3.22222V18.7778C21 20.0051 20.0051 21 18.7778 21H3.22222C1.99492 21 1 20.0051 1 18.7778V3.22222C1 1.99492 1.99492 1 3.22222 1Z" stroke="#8C6E47" stroke-width="1.11111" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </section>

        <div class="refine_vehicles-container">
            <div class="refine_vehicles-module">
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
                        $total_pages = max(1, (int) $vehicles->max_num_pages);

                        $pagination = paginate_links([
                            'total'     => $total_pages,
                            'current'   => $paged,
                            'end_size'  => 1,
                            'mid_size'  => 2,
                            'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="19" height="14" viewBox="0 0 19 14" fill="none"><path d="M19 7L1.00049 7M1.00049 7L7.00049 13M1.00049 7L7.0005 0.999999" stroke="#8C6E47"/></svg>',
                            'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="19" height="14" viewBox="0 0 19 14" fill="none"><path d="M-7.15494e-08 7L17.9995 7M17.9995 7L11.9995 1M17.9995 7L11.9995 13" stroke="#8C6E47"/></svg>',
                            'base'      => esc_url_raw(add_query_arg('vp', '%#%')), // ?vp=2
                            'format'    => '',                                          // (con add_query_arg no hace falta)
                            'add_args'  => array_filter([
                                'search_vehicle'     => $q,
                                'vehicle_brand'      => $brand_slug,
                                'vehicle_categories' => $cat_slug,
                                'order_by'           => $_GET['order_by'] ?? '',
                                'posts_per_page'     => $ppp,
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

<?php endif; ?>

<!-- ------------------------------------------------------------------------------------------------------ -->
<!-- ------------------------------------------------------------------------------------------------------ -->
<!-- ------------------------------------------------------------------------------------------------------ -->

<?php get_template_part('inc/sections/cta-single-venue_auction'); ?>

<section class="single_venue_info">
    <div class="single_venue_info-container">

        <?php
        if ($consign_content_1): ?>
            <div class="consign w-100">
                <h2>Consign with H&H</h2>
                <div class="consign-content">
                    <?php echo wp_kses_post($consign_content_1); ?>
                </div>
            </div>
        <?php endif; ?>


        <?php
        // ...
        if ($how_to_get_title || ($venue_id ? have_rows('how_to_get_items', $venue_id) : have_rows('how_to_get_items'))): ?>
            <div class="how_to_get w-100">
                <?php if ($how_to_get_title): ?>
                    <h2><?php echo esc_html($how_to_get_title); ?></h2>
                <?php endif; ?>

                <?php if ($venue_id): ?>
                    <?php if (have_rows('how_to_get_items', $venue_id)): ?>
                        <div class="how_to_get-col">
                            <?php while (have_rows('how_to_get_items', $venue_id)): the_row();
                                $subtitle = get_sub_field('item_subtitle');
                                $desc     = get_sub_field('item_description'); ?>
                                <div class="how_to_get-row">
                                    <?php if ($subtitle): ?><h3><?php echo esc_html($subtitle); ?></h3><?php endif; ?>
                                    <?php if ($desc): ?><div class="content">
                                            <p><?php echo wp_kses_post($desc); ?></p>
                                        </div><?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if (have_rows('how_to_get_items')): ?>
                        <div class="how_to_get-col">
                            <?php while (have_rows('how_to_get_items')): the_row();
                                $subtitle = get_sub_field('item_subtitle');
                                $desc     = get_sub_field('item_description'); ?>
                                <div class="how_to_get-row">
                                    <?php if ($subtitle): ?><h3><?php echo esc_html($subtitle); ?></h3><?php endif; ?>
                                    <?php if ($desc): ?><div class="content">
                                            <p><?php echo wp_kses_post($desc); ?></p>
                                        </div><?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>


    </div>
</section>

<?php if (!empty($lat) && !empty($lng)): ?>
    <section class="venue_map">
        <div class="venue_map-container">
            <div class="w-100 map_parent">
                <div id="map"></div>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="upcoming pb160">
    <?php get_template_part('inc/sections/upcoming'); ?>
</section>