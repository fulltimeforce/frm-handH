<?php
get_header();

get_banner('Homepage / classic auctions / News and Insights', '', 'News and Insights');
?>

<section class="blog_section pblock160">
    <div class="container">
        <div class="blog_section-filter">
            <div>
                <form method="GET" action="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>" style="display:flex; gap:1rem;">
                    <input 
                        type="search" 
                        id="blog-search"
                        name="s"
                        placeholder="Search for..."
                        value="<?php echo esc_attr($_GET['s'] ?? ''); ?>">

                    <select id="blog-category" class="blog_section-filter-select" name="category_name" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php
                        $post_ids = get_posts(array(
                            'post_type'      => 'post',
                            'posts_per_page' => -1,
                            'fields'         => 'ids',
                        ));

                        if ($post_ids) {
                            $categories = get_terms(array(
                                'taxonomy'   => 'category',
                                'hide_empty' => true,
                                'object_ids' => $post_ids,
                            ));

                            foreach ($categories as $cat) {
                                $selected = (isset($_GET['category_name']) && $_GET['category_name'] === $cat->slug) ? 'selected' : '';
                                echo '<option value="' . esc_attr($cat->slug) . '" ' . $selected . '>' . esc_html($cat->name) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </form>
            </div>
            <div>
                <p>
                    Showing 
                    <select id="blog-perpage" class="blog_section-filter-page" name="posts_per_page" onchange="this.form.submit()">
                        <option value="12" <?php echo (($_GET['posts_per_page'] ?? 12) == 12) ? 'selected' : ''; ?>>12</option>
                        <option value="24" <?php echo (($_GET['posts_per_page'] ?? 12) == 24) ? 'selected' : ''; ?>>24</option>
                        <option value="36" <?php echo (($_GET['posts_per_page'] ?? 12) == 36) ? 'selected' : ''; ?>>36</option>
                    </select> 
                    Per Page
                </p>
            </div>
        </div>

        <div class="blog_section-box">
            <div class="blog_section-grid">
                <?php
                $paged = max(1, get_query_var('paged'));
                $posts_per_page = isset($_GET['posts_per_page']) ? intval($_GET['posts_per_page']) : 12;

                $args = array(
                    'post_type'      => 'post',
                    'posts_per_page' => $posts_per_page,
                    'paged'          => $paged,
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                );

                if (!empty($_GET['s'])) $args['s'] = sanitize_text_field($_GET['s']);
                if (!empty($_GET['category_name'])) $args['category_name'] = sanitize_text_field($_GET['category_name']);

                $query = new WP_Query($args);

                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post(); 
                        $short_description = get_field('post_short_description');
                        $date       = get_the_date('d/m/Y');
                        $title      = get_the_title();
                        $permalink  = get_permalink();
                        $thumbnail  = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                ?>
                        <div class="blog_article">
                            <div class="blog_article-image">
                                <img src="<?php echo esc_url($thumbnail ?: get_template_directory_uri().'/images/default.jpg'); ?>" alt="<?php echo esc_attr($title); ?>">
                            </div>
                            <div class="blog_article-content">
                                <span class="p12"><?php echo esc_html($date); ?></span>
                                <h3><?php echo esc_html($title); ?></h3>
                                <?php if ($short_description): ?>
                                    <p class="p14"><?php echo esc_html($short_description); ?></p>
                                <?php endif; ?>
                                <a class="p12" href="<?php echo esc_url($permalink); ?>">Read More >></a>
                            </div>
                        </div>
                <?php
                    endwhile;
                else:
                    echo '<p class="p18">No posts found .</p>';
                endif;

                wp_reset_postdata();
                ?>
            </div>

            <div class="blog_section-pagination">
                <?php
                echo paginate_links(array(
                    'total'   => $query->max_num_pages,
                    'current' => $paged,
                    'prev_text' => __('<svg xmlns="http://www.w3.org/2000/svg" width="19" height="14" viewBox="0 0 19 14" fill="none">
  <path d="M19 7L1.00049 7M1.00049 7L7.00049 13M1.00049 7L7.0005 0.999999" stroke="#8C6E47"/>
</svg>'),
                    'next_text' => __('<svg xmlns="http://www.w3.org/2000/svg" width="19" height="14" viewBox="0 0 19 14" fill="none">
  <path d="M-7.15494e-08 7L17.9995 7M17.9995 7L11.9995 1M17.9995 7L11.9995 13" stroke="#8C6E47"/>
</svg>'),
                    'format'  => '?paged=%#%',
                    'add_args' => array(
                        's' => $_GET['s'] ?? '',
                        'category_name' => $_GET['category_name'] ?? '',
                        'posts_per_page' => $_GET['posts_per_page'] ?? 12
                    ),
                ));
                ?>
            </div>

        </div>
    </div>
</section>

<?php get_footer(); ?>
