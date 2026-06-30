<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Export Makes & Models to XLSX (admin only).
 *
 * Export 1 (grouped):
 *  - Row with Make title in column A
 *  - Following rows: related Model titles in column B (column A empty)
 *  - Blank separator row between groups
 *  - Final group: "Without relation" + orphan models
 *
 * Export 2 (flat permalinks):
 *  - Columns: Make | Permalink | Model | Permalink
 *  - All makes in A-B and all models in C-D, aligned by row index
 */
final class Makes_Models_Export_Module
{
    private const MAKE_POST_TYPE  = 'make';
    private const MODEL_POST_TYPE = 'model';
    private const BRAND_FIELD     = 'brand';
    private const CAPABILITY      = 'manage_options';
    private const MENU_SLUG       = 'export-makes-models';
    private const NONCE_ACTION    = 'makes_models_export_nonce';
    private const NONCE_FIELD     = 'makes_models_export_nonce_f';
    private const BATCH_SIZE      = 500;
    private const WITHOUT_RELATION_LABEL = 'Without relation';

    public static function init(): void
    {
        add_action('admin_menu', [__CLASS__, 'register_menu']);
        add_action('admin_init', [__CLASS__, 'maybe_handle_export']);
    }

    public static function register_menu(): void
    {
        add_submenu_page(
            'edit.php?post_type=' . self::MAKE_POST_TYPE,
            __('Export Makes & Models', 'textdomain'),
            __('Export Makes & Models', 'textdomain'),
            self::CAPABILITY,
            self::MENU_SLUG,
            [__CLASS__, 'render_page']
        );
    }

    public static function maybe_handle_export(): void
    {
        if (!is_admin() || !current_user_can(self::CAPABILITY)) {
            return;
        }

        $page = isset($_GET['page']) ? sanitize_key((string) $_GET['page']) : '';
        if (self::MENU_SLUG !== $page) {
            return;
        }

        if (!empty($_POST['makes_models_permalinks_export'])) {
            check_admin_referer(self::NONCE_ACTION, self::NONCE_FIELD);
            self::handle_permalinks_export();
            return;
        }

        if (empty($_POST['makes_models_export'])) {
            return;
        }

        check_admin_referer(self::NONCE_ACTION, self::NONCE_FIELD);

        self::handle_export();
    }

    public static function render_page(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die(esc_html__('You do not have permission to access this page.', 'textdomain'));
        }

        $make_count  = self::count_published_posts(self::MAKE_POST_TYPE);
        $model_count = self::count_published_posts(self::MODEL_POST_TYPE);
?>
        <div class="wrap">
            <h1><?php echo esc_html__('Export Makes & Models', 'textdomain'); ?></h1>

            <p>
                <?php echo esc_html__('Download an Excel file (.xlsx) with two columns: Make and Model.', 'textdomain'); ?><br>
                <?php echo esc_html__('Models are grouped under their related Make. Models without a Make appear under "Without relation".', 'textdomain'); ?>
            </p>

            <form method="post" action="">
                <?php wp_nonce_field(self::NONCE_ACTION, self::NONCE_FIELD); ?>
                <input type="hidden" name="makes_models_export" value="1">
                <p>
                    <button type="submit" class="button button-primary">
                        <?php echo esc_html__('Download Grouped Excel', 'textdomain'); ?>
                    </button>
                </p>
            </form>

            <hr>

            <p>
                <?php echo esc_html__('Download a flat Excel file (.xlsx) with four columns: Make, Permalink, Model and Permalink.', 'textdomain'); ?><br>
                <?php echo esc_html__('All makes are listed in the first two columns and all models in the last two, without grouping.', 'textdomain'); ?>
            </p>

            <form method="post" action="">
                <?php wp_nonce_field(self::NONCE_ACTION, self::NONCE_FIELD); ?>
                <input type="hidden" name="makes_models_permalinks_export" value="1">
                <p>
                    <button type="submit" class="button button-secondary">
                        <?php echo esc_html__('Download Permalinks Excel', 'textdomain'); ?>
                    </button>
                </p>
            </form>

            <p style="margin-top: 24px; opacity: .85;">
                <?php
                printf(
                    /* translators: 1: make count, 2: model count */
                    esc_html__('Currently: %1$d makes and %2$d models (published).', 'textdomain'),
                    (int) $make_count,
                    (int) $model_count
                );
                ?>
            </p>
        </div>
<?php
    }

    private static function handle_export(): void
    {
        self::prepare_export_environment();
        self::require_composer_autoload();

        $makes = self::get_all_makes();
        $grouped = self::group_models_by_make($makes);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Makes & Models');

        $row_index = 1;
        $sheet->fromArray(['Make', 'Model'], null, 'A' . $row_index);
        $row_index++;

        foreach ($makes as $make) {
            $make_id = (int) $make->ID;
            $models  = $grouped['by_make'][$make_id] ?? [];

            $sheet->setCellValue('A' . $row_index, self::decode_title($make->post_title));
            $row_index++;

            foreach ($models as $model_title) {
                $sheet->setCellValue('B' . $row_index, $model_title);
                $row_index++;
            }

            $row_index++;
        }

        $orphans = $grouped['orphans'];
        if (!empty($orphans)) {
            $sheet->setCellValue('A' . $row_index, self::WITHOUT_RELATION_LABEL);
            $row_index++;

            foreach ($orphans as $model_title) {
                $sheet->setCellValue('B' . $row_index, $model_title);
                $row_index++;
            }
        }

        self::stream_xlsx($spreadsheet, 'makes-models-grouped');
    }

    private static function handle_permalinks_export(): void
    {
        self::prepare_export_environment();
        self::require_composer_autoload();

        $make_rows  = self::build_title_permalink_rows(self::get_all_makes());
        $model_rows = self::build_title_permalink_rows(self::get_all_models());

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Permalinks');

        $sheet->fromArray(['Make', 'Permalink', 'Model', 'Permalink'], null, 'A1');

        $total_rows = max(count($make_rows), count($model_rows));
        for ($i = 0; $i < $total_rows; $i++) {
            $row_index = $i + 2;
            $make_row  = $make_rows[$i] ?? ['', ''];
            $model_row = $model_rows[$i] ?? ['', ''];

            $sheet->fromArray([
                $make_row[0],
                $make_row[1],
                $model_row[0],
                $model_row[1],
            ], null, 'A' . $row_index);
        }

        self::stream_xlsx($spreadsheet, 'makes-models-permalinks');
    }

    private static function prepare_export_environment(): void
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');
        wp_suspend_cache_addition(true);

        while (ob_get_level()) {
            ob_end_clean();
        }
    }

    /**
     * @param WP_Post[] $posts
     * @return array<int, array{0: string, 1: string}>
     */
    private static function build_title_permalink_rows(array $posts): array
    {
        $rows = [];

        foreach ($posts as $post) {
            if (!($post instanceof WP_Post)) {
                continue;
            }

            $rows[] = [
                self::decode_title((string) $post->post_title),
                (string) (get_permalink($post) ?: ''),
            ];
        }

        return $rows;
    }

    /**
     * @return WP_Post[]
     */
    private static function get_all_makes(): array
    {
        $posts = get_posts([
            'post_type'              => self::MAKE_POST_TYPE,
            'post_status'            => 'publish',
            'posts_per_page'         => -1,
            'orderby'                => 'title',
            'order'                  => 'ASC',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ]);

        return is_array($posts) ? $posts : [];
    }

    /**
     * @return WP_Post[]
     */
    private static function get_all_models(): array
    {
        $posts = get_posts([
            'post_type'              => self::MODEL_POST_TYPE,
            'post_status'            => 'publish',
            'posts_per_page'         => -1,
            'orderby'                => 'title',
            'order'                  => 'ASC',
            'no_found_rows'          => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => false,
        ]);

        return is_array($posts) ? $posts : [];
    }

    /**
     * @param WP_Post[] $makes
     * @return array{by_make: array<int, string[]>, orphans: string[]}
     */
    private static function group_models_by_make(array $makes): array
    {
        $valid_make_ids = [];
        foreach ($makes as $make) {
            $valid_make_ids[(int) $make->ID] = true;
        }

        $by_make = [];
        $orphans = [];

        $last_id = 0;

        while (true) {
            $ids = self::get_model_ids_after_id($last_id, self::BATCH_SIZE);
            if (empty($ids)) {
                break;
            }

            if (function_exists('update_meta_cache')) {
                update_meta_cache('post', $ids);
            }

            foreach ($ids as $model_id) {
                $model_id = (int) $model_id;
                $title    = self::decode_title((string) get_the_title($model_id));
                $make_id  = self::normalize_make_id(self::get_brand_value($model_id));

                if ($make_id > 0 && isset($valid_make_ids[$make_id])) {
                    $by_make[$make_id][] = $title;
                } else {
                    $orphans[] = $title;
                }
            }

            $last_id = (int) end($ids);
        }

        foreach ($by_make as $make_id => $titles) {
            sort($titles, SORT_NATURAL | SORT_FLAG_CASE);
            $by_make[$make_id] = $titles;
        }

        sort($orphans, SORT_NATURAL | SORT_FLAG_CASE);

        return [
            'by_make' => $by_make,
            'orphans' => $orphans,
        ];
    }

    private static function get_model_ids_after_id(int $after_id, int $limit): array
    {
        global $wpdb;

        $ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts}
                 WHERE post_type = %s AND post_status = 'publish' AND ID > %d
                 ORDER BY ID ASC
                 LIMIT %d",
                self::MODEL_POST_TYPE,
                $after_id,
                $limit
            )
        );

        if (!is_array($ids) || empty($ids)) {
            return [];
        }

        return array_map('intval', $ids);
    }

    private static function get_brand_value(int $model_id)
    {
        if (function_exists('get_field')) {
            return get_field(self::BRAND_FIELD, $model_id);
        }

        return get_post_meta($model_id, self::BRAND_FIELD, true);
    }

    private static function normalize_make_id($brand): int
    {
        if (empty($brand)) {
            return 0;
        }

        if (is_object($brand) && isset($brand->ID)) {
            return (int) $brand->ID;
        }

        if (is_array($brand) && isset($brand['ID'])) {
            return (int) $brand['ID'];
        }

        if (is_numeric($brand)) {
            return (int) $brand;
        }

        return 0;
    }

    private static function decode_title(string $title): string
    {
        return html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private static function count_published_posts(string $post_type): int
    {
        $count = wp_count_posts($post_type);
        return isset($count->publish) ? (int) $count->publish : 0;
    }

    private static function stream_xlsx(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, string $filename_prefix = 'makes-models'): void
    {
        nocache_headers();

        $filename = sprintf('%s-%s.xlsx', sanitize_file_name($filename_prefix), gmdate('Y-m-d-His'));

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private static function require_composer_autoload(): void
    {
        if (class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            return;
        }

        $paths = array_unique([
            get_template_directory() . '/vendor/autoload.php',
            get_stylesheet_directory() . '/vendor/autoload.php',
        ]);

        foreach ($paths as $autoload) {
            if (!is_readable($autoload)) {
                continue;
            }

            require_once $autoload;

            if (class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
                return;
            }
        }

        wp_die(esc_html__('Composer autoload not found or PhpSpreadsheet is not available.', 'textdomain'));
    }
}

Makes_Models_Export_Module::init();