<?php
$filters = hnh_get_auction_filters();
$past_auctions = hnh_get_past_auctions_query($filters);
// Extrayendo variables
$paged = $filters['paged'];
$posts_per_page = $filters['posts_per_page'];
$sale_type = $filters['sale_type'];
$year_param = $filters['auction_year'];
$currentYear = (int) date('Y');

// Filters
$sale_type_label = get_field('sale_type_label');
$motor_cars_label = get_field('motor_cars_label');
$motorcycles_label = get_field('motorcycles_label');
$year_filter_label = get_field('year_filter_label');
$min_auction_year = get_field('min_auction_year');
// Pagination
$showing_label = get_field('showing_label');
$per_page_label = get_field('per_page_label');
$per_page_options = get_field('per_page_options'); // Repeater
// Empty State
$no_results_message = get_field('no_results_message'); // textarea
?>
<section class="auction_vehicles">
  <div class="auction_vehicles-container">

    <form class="auction_result-filter" method="get" action="">
      <div class="auction_result-filter-select">
        <select name="sale_type" onchange="this.form.submit()">
          <option value="all" <?php selected(($_GET['sale_type'] ?? 'all'), 'all'); ?>>
            <?= esc_html($sale_type_label); ?>
          </option>
          <option value="motorcars" <?php selected(($_GET['sale_type'] ?? ''), 'motorcars'); ?>>
            <?= esc_html($motor_cars_label); ?>
          </option>
          <option value="motorcycles" <?php selected(($_GET['sale_type'] ?? ''), 'motorcycles'); ?>>
            <?= esc_html($motorcycles_label); ?>
          </option>
        </select>
      </div>

      <div class="auction_result-filter-select">
        <select name="auction_year" onchange="this.form.submit()">
          <?php
          $selectedYear = $_GET['auction_year'] ?? (string) $currentYear;
          for ($i = $currentYear; $i >= $min_auction_year; $i--): ?>
            <option value="<?php echo $i; ?>" <?php selected($selectedYear, (string) $i); ?>>
              <?php echo $i; ?>
            </option>
          <?php endfor; ?>
          <option value="all" <?php selected($selectedYear, 'all'); ?>>
            <?= esc_html($year_filter_label); ?>
          </option>
        </select>
      </div>

      <div class="auction_result-filter-page">
        <p>
          <?= esc_html($showing_label); ?>
          <select id="blog-perpage" class="blog_section-filter-page" name="posts_per_page">
            <?php foreach ($per_page_options as $option): ?>
              <option value="<?= esc_attr($option['value']); ?>" <?php selected((int) ($_GET['posts_per_page'] ?? $posts_per_page), (int) $option['value']); ?>>
                <?= esc_html($option['value']); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?= esc_html($per_page_label); ?>
        </p>
      </div>
    </form>

    <?php if ($past_auctions->have_posts()): ?>
      <div class="auction_result-list past_auctions">

        <?php while ($past_auctions->have_posts()): ?>
          <?php
          $past_auctions->the_post();
          $auction_id = get_the_ID();
          $venue_id = (int) get_field('template_venue', $auction_id);

          hnh_render_auction_card($auction_id, $venue_id);
          ?>
        <?php endwhile; ?>
        <?php wp_reset_postdata(); ?>

      </div>

      <?php
      $pagination = paginate_links([
        'total' => (int) $past_auctions->max_num_pages,
        'current' => $paged,
        'mid_size' => 2,
        'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="19" height="14" viewBox="0 0 19 14" fill="none"><path d="M19 7L1.00049 7M1.00049 7L7.00049 13M1.00049 7L7.0005 0.999999" stroke="#8C6E47"/></svg>',
        'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="19" height="14" viewBox="0 0 19 14" fill="none"><path d="M-7.15494e-08 7L17.9995 7M17.9995 7L11.9995 1M17.9995 7L11.9995 13" stroke="#8C6E47"/></svg>',
        'add_args' => array_filter([
          'sale_type' => $sale_type,
          'auction_year' => $year_param,
          'posts_per_page' => $posts_per_page,
        ]),
      ]);

      if ($pagination) {
        echo '<div class="auction_result-pagination">' . $pagination . '</div>';
      } ?>
    <?php else: ?>
      <div class="no-one">
        <p>
          <?= esc_html($no_results_message); ?>
        </p>
      </div>
    <?php endif; ?>
  </div>
</section>