<?php
/*
    Template name: blog
*/

get_header();

get_banner('Homepage / classic auctions / News and Insights', '', 'News and Insights');

?>

<section class="blog_section pblock160">
    <div class="container">
        <div class="blog_section-filter">
            <div>
                <input 
                    type="search" 
                    id="blog-search"
                    name="s"
                    placeholder="Search for..."
                    value="<?php echo get_search_query(); ?>">

                <select id="blog-category" class="blog_section-filter-select" name="category_name">
                    <option value="">All Categories</option>
                    <?php
                    $categories = get_categories(array(
                        'taxonomy'   => 'category',
                        'hide_empty' => true,
                    ));

                    foreach ($categories as $cat) {
                        $selected = (get_query_var('category_name') === $cat->slug) ? 'selected' : '';
                        echo '<option value="' . esc_attr($cat->slug) . '" ' . $selected . '>' . esc_html($cat->name) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div>
                <p>
                    Showing 
                    <select id="blog-perpage" class="blog_section-filter-page" name="posts_per_page">
                        <option value="12" <?php selected(get_query_var('posts_per_page'), 12); ?>>12</option>
                        <option value="24" <?php selected(get_query_var('posts_per_page'), 24); ?>>24</option>
                        <option value="36" <?php selected(get_query_var('posts_per_page'), 36); ?>>36</option>
                    </select> 
                    Per Page
                </p>
            </div>
        </div>
        <div class="blog_section-box">
            <div class="blog_section-grid">
                <?php
                $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                $posts_per_page = (get_query_var('posts_per_page')) ? get_query_var('posts_per_page') : 12;

                $args = array(
                    'post_type'      => 'post',
                    'posts_per_page' => $posts_per_page,
                    'paged'          => $paged,
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                );

                if (!empty(get_query_var('s'))) {
                    $args['s'] = get_query_var('s');
                }

                if (!empty(get_query_var('category_name'))) {
                    $args['category_name'] = get_query_var('category_name');
                }

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
                                <?php if ($thumbnail): ?>
                                    <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($title); ?>">
                                <?php else: ?>
                                    <img src="<?php echo get_template_directory_uri(); ?>/images/default.jpg" alt="Placeholder">
                                <?php endif; ?>
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
                    <?php endwhile; ?>
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
                ));
                ?>
            </div>

            <?php wp_reset_postdata(); ?>
            <?php endif; ?>

        </div>
    </div>
</section>


<?php get_footer(); ?>