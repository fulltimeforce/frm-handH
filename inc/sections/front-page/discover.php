<?php
// Query 1: vehicle_category = 27
$args_1 = [
  'post_type' => 'vehicles',
  'posts_per_page' => 6,
  'post_status' => 'publish',
  'meta_query' => [
    [
      'key' => 'type_of_vehicle',
      'value' => 'private-sale',
      'compare' => '=',
    ]
  ],
//  'orderby' => 'meta_value',
//  'order' => 'DESC',
  'meta_type' => 'DATETIME',
];
$q1 = new WP_Query($args_1);
?>
<?php if ($q1->have_posts()): ?>
  <section class="discover" data-state="1">
    <div class="container">
      <div class="discover_head title_watermark">
        <div class="watermark">
          <p>Private Vehicles For Sale</p>
        </div>
        <div class="breadlines">
          <p>Refine your Search</p>
        </div>
        <h2>Private Vehicles For Sale</h2>
      </div>

      <?php if (NOT_APPEAR): ?>
        <div class="opportunities-buttons w-100" style="max-width: 100%;padding: 0;">
          <button class="scroll_opportunity active" data-id="1" title="Classic Motorcars" alt="Classic Motorcars">Classic Motorcars</button>
          <button class="scroll_opportunity" data-id="2" title="Classic Motorcycles" alt="Classic Motorcycles">Classic Motorcycles</button>
          <button class="scroll_opportunity" data-id="3" title="Vintage Scooters" alt="Vintage Scooters">Vintage Scooters</button>
        </div>
      <?php endif; ?>

      <div class="discover_body">

        <div class="vehicles_grid" id="vehicle_type_1">
          <?php while ($q1->have_posts()):
            $q1->the_post(); ?>
            <?php hnh_render_vehicle_card(get_the_ID()); ?>
          <?php endwhile;
          wp_reset_postdata(); ?>
        </div>

        <?php if (NOT_APPEAR): ?>
          <?php
          // Query 2: vehicle_category = 22
          $args_2 = [
            'post_type' => 'vehicles',
            'posts_per_page' => 6,
            'post_status' => 'publish',
            'no_found_rows' => true,
            'ignore_sticky_posts' => true,
            'tax_query' => [
              [
                'taxonomy' => 'vehicle_category',
                'field' => 'term_id',
                'terms' => [22],
              ],
            ],
            'meta_query' => [
              [
                'key' => '_thumbnail_id',
                'compare' => 'EXISTS',
              ],
            ],
            'orderby' => 'date',
            'order' => 'DESC',
          ];
          $q2 = new WP_Query($args_2);
          ?>
          <div class="vehicles_grid" id="vehicle_type_2">
            <?php if ($q2->have_posts()): ?>
              <?php while ($q2->have_posts()):
                $q2->the_post(); ?>
                <?php hnh_render_vehicle_card(get_the_ID()); ?>
              <?php endwhile;
              wp_reset_postdata(); ?>
            <?php endif; ?>
          </div>

          <?php
          // Query 3: vehicle_category = 36
          $args_3 = [
            'post_type' => 'vehicles',
            'posts_per_page' => 6,
            'post_status' => 'publish',
            'no_found_rows' => true,
            'ignore_sticky_posts' => true,
            'tax_query' => [
              [
                'taxonomy' => 'vehicle_category',
                'field' => 'term_id',
                'terms' => [36],
              ],
            ],
            'meta_query' => [
              [
                'key' => '_thumbnail_id',
                'compare' => 'EXISTS',
              ],
            ],
            'orderby' => 'date',
            'order' => 'DESC',
          ];
          $q3 = new WP_Query($args_3);
          ?>
          <div class="vehicles_grid" id="vehicle_type_3">
            <?php if ($q3->have_posts()): ?>
              <?php while ($q3->have_posts()):
                $q3->the_post(); ?>
                <?php hnh_render_vehicle_card(get_the_ID()); ?>
              <?php endwhile;
              wp_reset_postdata(); ?>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <a href="<?php echo esc_url(get_permalink(803)); ?>" class="permalink" alt="View All Vehicles" title="View All Vehicles">
          View All Vehicles
          <svg xmlns="http://www.w3.org/2000/svg" width="25" height="14" viewBox="0 0 25 14" fill="none">
            <path d="M0 7H24M24 7L18 1M24 7L18 13" stroke="#8C6E47" />
          </svg>
        </a>
      </div>
    </div>
  </section>
<?php endif; ?>