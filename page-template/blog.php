<?php
/*
    Template name: blog
*/

get_header();

get_banner(
    'Homepage / classic auctions / News and Insights',
    get_the_post_thumbnail_url(get_the_ID(), 'full'),
    'News and Insights'
);

// ====== Captura y saneo de filtros (GET) ======
$s           = isset($_GET['post_search']) ? sanitize_text_field(wp_unslash($_GET['post_search'])) : '';
$cat_slug    = isset($_GET['category_name']) ? sanitize_text_field(wp_unslash($_GET['category_name'])) : '';
$allowed_ppp = [12, 24, 36];
$ppp_param   = isset($_GET['posts_per_page']) ? (int) $_GET['posts_per_page'] : 12;
$posts_per_page = in_array($ppp_param, $allowed_ppp, true) ? $ppp_param : 12;

// Paginación
$paged = max(1, (int) (get_query_var('paged') ?: get_query_var('page') ?: 1));

// ====== Query de posts ======
$args = [
    'post_type'      => 'post',
    'posts_per_page' => $posts_per_page,
    'paged'          => $paged,
    'orderby'        => 'date',
    'order'          => 'DESC',
];

// Mapear tu input "post_search" al parámetro 's' que sí entiende WP_Query
if ($s !== '') {
    $args['s'] = $s;
}
if ($cat_slug !== '') {
    $args['category_name'] = $cat_slug;
}

$query = new WP_Query($args);
?>

<section class="blog_section pblock160">
    <div class="container">

        <!-- ====== Filtros ====== -->
        <form class="blog_section-filter w-100" id="blog-filters" method="get" action="<?php echo esc_url(get_permalink()); ?>">
            <div>
                <input
                    type="search"
                    id="blog-search"
                    name="post_search"
                    placeholder="Search for..."
                    value="<?php echo esc_attr($s); ?>"
                    aria-label="Search posts" />

                <select id="blog-category" class="blog_section-filter-select" name="category_name" aria-label="Filter by category">
                    <option value="">All Categories</option>
                    <?php
                    $categories = get_categories([
                        'taxonomy'   => 'category',
                        'hide_empty' => true,
                        'exclude'    => [1],
                    ]);

                    foreach ($categories as $cat) {
                        printf(
                            '<option value="%s" %s>%s</option>',
                            esc_attr($cat->slug),
                            selected($cat_slug, $cat->slug, false),
                            esc_html($cat->name)
                        );
                    }
                    ?>
                </select>
            </div>

            <div>
                <p>
                    Showing
                    <select id="blog-perpage" class="blog_section-filter-page" name="posts_per_page" aria-label="Posts per page">
                        <?php foreach ($allowed_ppp as $opt): ?>
                            <option value="<?php echo (int) $opt; ?>" <?php selected($posts_per_page, $opt); ?>>
                                <?php echo (int) $opt; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    Per Page
                </p>
            </div>

            <!-- Botón accesible para no-JS (puedes ocultarlo por CSS si usas auto-submit) -->
            <button type="submit" class="screen-reader-text">Apply filters</button>
        </form>

        <div class="blog_section-box">
            <div class="blog_section-grid">
                <?php if ($query->have_posts()) : ?>
                    <?php while ($query->have_posts()) : $query->the_post();
                        $short_description = get_field('post_short_description');
                        $date       = get_the_date('d/m/Y');
                        $title      = get_the_title();
                        $permalink  = get_permalink();
                        $thumbnail  = get_the_post_thumbnail_url(get_the_ID(), 'medium_large');
                    ?>
                        <article class="blog_article">
                            <div class="blog_article-image">
                                <?php if ($thumbnail): ?>
                                    <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($title); ?>">
                                <?php else: ?>
                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/images/default.jpg'); ?>" alt="Placeholder">
                                <?php endif; ?>
                            </div>
                            <div class="blog_article-content">
                                <span class="p12"><?php echo esc_html($date); ?></span>
                                <h3><?php echo esc_html($title); ?></h3>

                                <?php if (!empty($short_description)) : ?>
                                    <p class="p14"><?php echo esc_html($short_description); ?></p>
                                <?php else: ?>
                                    <p class="p14">
                                        <?php echo esc_html(wp_trim_words(wp_strip_all_tags(get_the_excerpt() ?: get_the_content()), 24)); ?>
                                    </p>
                                <?php endif; ?>

                                <a class="p12" href="<?php echo esc_url($permalink); ?>">Read More >></a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No posts found.</p>
                <?php endif; ?>
            </div>

            <!-- ====== Paginación (preserva filtros) ====== -->
            <div class="blog_section-pagination">
                <?php
                if ($query->max_num_pages > 1) {
                    $add_args = array_filter([
                        's'               => $s !== '' ? $s : null,
                        'category_name'   => $cat_slug !== '' ? $cat_slug : null,
                        'posts_per_page'  => $posts_per_page,
                    ]);

                    echo paginate_links([
                        'total'     => (int) $query->max_num_pages,
                        'current'   => (int) $paged,
                        'mid_size'  => 2,
                        'end_size'  => 1,
                        'add_args'  => $add_args,
                        'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="19" height="14" viewBox="0 0 19 14" fill="none"><path d="M19 7L1.00049 7M1.00049 7L7.00049 13M1.00049 7L7.0005 0.999999" stroke="#8C6E47"/></svg>',
                        'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="19" height="14" viewBox="0 0 19 14" fill="none"><path d="M0 7L17.9995 7M17.9995 7L11.9995 1M17.9995 7L11.9995 13" stroke="#8C6E47"/></svg>',
                    ]);
                }
                ?>
            </div>

            <?php wp_reset_postdata(); ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>

<!-- ====== Auto-submit JS para filtros ====== -->
<script>
    (function() {
        const form = document.getElementById('blog-filters');
        const $search = document.getElementById('blog-search');
        const $cat = document.getElementById('blog-category');
        const $ppp = document.getElementById('blog-perpage');

        if (!form) return;

        // Cambios en selects -> submit
        [$cat, $ppp].forEach(function(el) {
            if (!el) return;
            el.addEventListener('change', function() {
                // Al aplicar filtros, volvemos a la primera página (no incluimos "paged")
                form.submit();
            });
        });

        // ENTER en el search -> submit
        if ($search) {
            $search.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    // Evita que algunos navegadores mantengan ?paged
                    // (al usar form GET solo se envían los campos del form)
                    form.submit();
                }
            });
        }
    })();
</script>