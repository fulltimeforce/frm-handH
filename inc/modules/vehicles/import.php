<?php
// ============ SETTINGS (fácil de cambiar si lo necesitas) =============
const HNH_IMPORT_POST_TYPE   = 'vehicles';
const HNH_TAX_CATEGORY       = 'vehicle_category';
const HNH_TAX_BRAND          = 'vehicle_brand';
const HNH_MENU_PARENT        = 'edit.php?post_type=vehicles'; // Colgar el import del menú "Vehicles"

// CPT que guarda a los miembros del equipo (para el Post Object "member_to_contact")
const HNH_TEAM_POST_TYPE     = 'team';

// *** MODO ESPECIAL: SOLO ACTUALIZAR FECHAS POR TÍTULO ***
const HNH_UPDATE_DATES_ONLY  = false; // ← pon false para volver al import normal
// ======================================================================

// === Admin Page: Vehicles → Import Vehicles
add_action('admin_menu', function () {
    add_submenu_page(
        HNH_MENU_PARENT,
        'Import Vehicles',
        'Import Vehicles',
        'manage_options',
        'import-vehicles',
        'vehicles_import_render_page'
    );
});

function vehicles_import_render_page()
{
?>
    <div class="wrap">
        <h1>Import Vehicles</h1>
        <p>Upload a <strong>.xlsx</strong> (from the CRM) or <strong>.csv</strong> (comma-separated). The first row must be the header.</p>
        <?php if (HNH_UPDATE_DATES_ONLY): ?>
            <p><strong>Mode:</strong> <code>HNH_UPDATE_DATES_ONLY = true</code> — this will <u>only</u> update <code>auction_date_latest</code> by matching the post by <em>Title (main)</em>.</p>
            <p>Required columns: <strong>Title (main)</strong>, <strong>Auction date (latest)</strong>. Date will be normalized to <code>Y-m-d H:i</code>.</p>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('vehicles_import_nonce', 'vehicles_import_nonce_f'); ?>
            <input type="file" name="vehicles_file" accept=".xlsx,.csv" required />
            <p><button class="button button-primary">Import</button></p>
        </form>
        <?php
        if (!empty($_FILES['vehicles_file']) && isset($_POST['vehicles_import_nonce_f']) && wp_verify_nonce($_POST['vehicles_import_nonce_f'], 'vehicles_import_nonce')) {
            vehicles_handle_import($_FILES['vehicles_file']);
        }
        ?>
    </div>
<?php
}

// === Import Handler ===
function vehicles_handle_import($file)
{
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to perform this action.');
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo '<div class="notice notice-error"><p>File upload error.</p></div>';
        return;
    }

    // Upload to /uploads
    $uploaded = wp_handle_upload($file, ['test_form' => false]);
    if (!empty($uploaded['error'])) {
        echo '<div class="notice notice-error"><p>' . esc_html($uploaded['error']) . '</p></div>';
        return;
    }

    $path = $uploaded['file'];
    $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    // Read rows: [ [col0, col1, ...], ... ]
    $rows = [];
    if ($ext === 'xlsx') {
        if (!class_exists('SimpleXLSX')) {
            vehicles_include_simplexlsx();
        }
        $xlsx = SimpleXLSX::parse($path);
        if (!$xlsx) {
            echo '<div class="notice notice-error"><p>Could not read XLSX: ' . esc_html(SimpleXLSX::parseError()) . '</p></div>';
            return;
        }
        $rows = $xlsx->rows(); // preserves middle gaps by cell refs
    } elseif ($ext === 'csv') {
        if (($handle = fopen($path, 'r')) !== false) {
            while (($data = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
                $rows[] = $data;
            }
            fclose($handle);
        }
    } else {
        echo '<div class="notice notice-error"><p>Unsupported extension: ' . esc_html($ext) . '</p></div>';
        return;
    }

    if (count($rows) < 2) {
        echo '<div class="notice notice-warning"><p>Not enough data (header + rows required).</p></div>';
        return;
    }

    // === Normalize to EXACT header length
    $headers_raw = $rows[0];
    $headerLen   = is_array($headers_raw) ? count($headers_raw) : 0;

    // recorta celdas vacías al final del header
    while ($headerLen > 0 && trim((string)$headers_raw[$headerLen - 1]) === '') {
        array_pop($headers_raw);
        $headerLen--;
    }
    if ($headerLen === 0) {
        echo '<div class="notice notice-error"><p>Header row is empty.</p></div>';
        return;
    }

    // normaliza cada fila a la longitud exacta del header
    foreach ($rows as $i => $r) {
        if (!is_array($r)) $r = [];
        $count = count($r);
        if ($count < $headerLen) {
            $rows[$i] = array_pad($r, $headerLen, '');
        } elseif ($count > $headerLen) {
            $rows[$i] = array_slice($r, 0, $headerLen);
        }
    }

    // *** MODO SOLO FECHAS ***
    if (HNH_UPDATE_DATES_ONLY) {
        vehicles_update_dates_only($rows, $headers_raw);
        return;
    }

    // ============== MODO IMPORT NORMAL (tu lógica original) =================

    // === Map by COLUMN INDEX (positional)
    $map_by_index = [];
    foreach ($headers_raw as $colIdx => $label) {
        $label = sanitize_text_field($label);
        $name  = vehicles_sanitize_field_name($label);
        $map_by_index[$colIdx] = $name ?: null;
    }

    // ---------------------------------------------
    // Headers importantes (Title SOLO desde "Title (main)")
    // ---------------------------------------------
    $title_col      = vehicles_find_header(['Title (main)'], $headers_raw);
    if ($title_col === -1) {
        echo '<div class="notice notice-error"><p>Required header <strong>Title (main)</strong> not found. Import aborted.</p></div>';
        return;
    }

    $content_col    = vehicles_find_header(['Description', 'Desc'], $headers_raw);
    $image_url_col  = vehicles_find_header(['Image URL (main image)', 'Image URL', 'Main image URL'], $headers_raw);
    $stock_col      = vehicles_find_header(['Stock number', 'Stock Number'], $headers_raw);

    // Category columns
    $category1_col  = vehicles_find_header(['Category 1', 'Category1'], $headers_raw);
    $category2_col  = vehicles_find_header(['Category 2', 'Category2'], $headers_raw);

    // Brand column (Artist/Maker/Brand)
    $brand_col      = vehicles_find_header(['Artist/Maker/Brand', 'Artist', 'Maker', 'Brand'], $headers_raw);

    // Contact/Rep o Assigned to → ACF Post Object "member_to_contact" (CPT team)
    $contact_col    = vehicles_find_header([
        'Contact/Rep or Assigned to',
        'Contact/Rep',
        'Assigned to',
        'Assigned To',
        'Member to Contact'
    ], $headers_raw);

    $created = 0;
    $skipped_empty = 0;
    $skipped_existing = 0;
    $skipped_no_stock = 0;
    $skipped_no_title = 0;

    $seen_stocks_in_file = [];

    // Term caches
    $category_cache = []; // [CamelName => term_id]
    $brand_cache    = []; // [CamelName => term_id]

    for ($i = 1; $i < count($rows); $i++) {
        $row = $rows[$i];

        // Empty row?
        $nonEmpty = false;
        foreach ($row as $cell) {
            if ((string)$cell !== '') {
                $nonEmpty = true;
                break;
            }
        }
        if (!$nonEmpty) {
            $skipped_empty++;
            continue;
        }

        // --- Título obligatorio desde "Title (main)"
        $title_value = wp_strip_all_tags(trim((string)$row[$title_col]));
        if ($title_value === '') {
            $skipped_no_title++;
            continue;
        }

        // Stock number (unique)
        $stock_value = '';
        if ($stock_col !== -1) {
            $stock_value = strtoupper(trim((string)$row[$stock_col]));
            $stock_value = preg_replace('/\s+/', '', $stock_value);
        }
        if ($stock_value === '') {
            $skipped_no_stock++;
            continue;
        }

        if (isset($seen_stocks_in_file[$stock_value])) {
            $skipped_existing++;
            continue;
        }
        $seen_stocks_in_file[$stock_value] = true;

        if (vehicles_published_exists_by_stock_number($stock_value)) {
            $skipped_existing++;
            continue;
        }

        // Contenido (pretty)
        $raw_desc     = $content_col !== -1 ? (string)$row[$content_col] : '';
        $post_content = vehicles_format_description($raw_desc);

        // Crear post (título SIN fallback)
        $post_id = wp_insert_post([
            'post_type'    => HNH_IMPORT_POST_TYPE,
            'post_status'  => 'publish',
            'post_title'   => $title_value, // solo Title (main)
            'post_content' => $post_content,
        ], true);
        if (is_wp_error($post_id)) {
            $skipped_empty++;
            continue;
        }

        /** ===== Extraer y guardar ACF desde Description ===== */
        $desc_triplet = vehicles_extract_from_description($raw_desc);
        update_field('registration_no', $desc_triplet['registration_no'], $post_id);
        update_field('chassis_no',      $desc_triplet['chassis_no'],      $post_id);
        // update_field('mot',             $desc_triplet['mot'],             $post_id);
        /** =================================================== */

        // Guardar ACF fields posicionalmente (todas las columnas normalizadas)
        foreach ($headers_raw as $colIdx => $header_label) {
            $field_name = $map_by_index[$colIdx] ?? null;
            if (!$field_name) continue;

            // Evita escribir el Post Object "member_to_contact" con texto crudo.
            if ($field_name === 'member_to_contact') continue;

            $value = (string)$row[$colIdx];
            $lower = strtolower((string)$header_label);

            if (strpos($lower, 'date') !== false) {
                $value = vehicles_excel_serial_to_datetime($value, 'Y-m-d H:i');
            }
            update_field($field_name, $value, $post_id);
        }
        update_field('stock_number', $stock_value, $post_id);

        // === Member to Contact (Post Object -> Team) ===
        static $team_cache = []; // cache por nombre normalizado
        $contact_raw = ($contact_col !== -1) ? trim((string)$row[$contact_col]) : '';
        if ($contact_raw !== '') {
            $team_id = vehicles_get_team_id_by_display($contact_raw, $team_cache);
            if ($team_id) {
                // ACF acepta IDk
                // update_field('member_to_contact', $team_id, $post_id);
            }
        }

        // Featured image from URL
        if ($image_url_col !== -1) {
            $img_url = trim((string)$row[$image_url_col]);
            if ($img_url !== '') vehicles_set_featured_image_from_url($post_id, $img_url);
        }

        // === Vehicle Categories (Category 1 / Category 2) ===
        $assigned_category_ids = [];
        if (taxonomy_exists(HNH_TAX_CATEGORY)) {
            $raw1 = ($category1_col !== -1) ? trim((string)$row[$category1_col]) : '';
            $raw2 = ($category2_col !== -1) ? trim((string)$row[$category2_col]) : '';
            foreach ([$raw1, $raw2] as $raw) {
                if ($raw === '') continue;
                $camel = vehicles_to_camelcase($raw);
                $tid   = vehicles_get_or_create_term($camel, HNH_TAX_CATEGORY, $category_cache);
                if ($tid) $assigned_category_ids[] = (int)$tid;
            }
            if (!empty($assigned_category_ids)) {
                wp_set_object_terms($post_id, $assigned_category_ids, HNH_TAX_CATEGORY, false);
            }
        }

        // === Vehicle Brand (Artist/Maker/Brand) → taxonomy HNH_TAX_BRAND ===
        if (taxonomy_exists(HNH_TAX_BRAND) && $brand_col !== -1) {
            $raw_brand = trim((string)$row[$brand_col]);
            if ($raw_brand !== '') {
                $brand_camel   = vehicles_to_camelcase($raw_brand);
                $brand_term_id = vehicles_get_or_create_term($brand_camel, HNH_TAX_BRAND, $brand_cache);
                if ($brand_term_id) {
                    wp_set_object_terms($post_id, (int)$brand_term_id, HNH_TAX_BRAND, false);

                    // Vincular brand → category (meta 'linked_vehicle_category')
                    $primary_cat_id = 0;
                    if (!empty($assigned_category_ids)) {
                        $primary_cat_id = (int)$assigned_category_ids[0]; // toma Category 1 (o la primera que llegó)
                    } else {
                        $linked = (int) get_term_meta($brand_term_id, 'linked_' . HNH_TAX_CATEGORY, true);
                        if ($linked) {
                            $primary_cat_id = $linked;
                            wp_set_object_terms($post_id, [$primary_cat_id], HNH_TAX_CATEGORY, true);
                        }
                    }
                    if ($primary_cat_id && !get_term_meta($brand_term_id, 'linked_' . HNH_TAX_CATEGORY, true)) {
                        update_term_meta($brand_term_id, 'linked_' . HNH_TAX_CATEGORY, $primary_cat_id);
                    }
                }
            }
        }

        $created++;
    }

    echo '<div class="notice notice-success"><p>'
        . 'Import completed. '
        . 'Created: <strong>' . intval($created) . '</strong> '
        . '| Skipped (existing by published stock number): ' . intval($skipped_existing) . ' '
        . '| Skipped (empty rows/errors): ' . intval($skipped_empty) . ' '
        . '| Skipped (no stock number): ' . intval($skipped_no_stock) . ' '
        . '| Skipped (no title): ' . intval($skipped_no_title)
        . '</p></div>';
}

/**
 * *** SPECIAL MODE ***
 * Actualiza SOLO el campo ACF 'auction_date_latest' buscando por título exacto.
 * Requiere columnas: 'Title (main)' y 'Auction date (latest)'.
 */
function vehicles_update_dates_only(array $rows, array $headers_raw)
{
    $title_col = vehicles_find_header(['Title (main)'], $headers_raw);
    $date_col  = vehicles_find_header(['Auction date (latest)'], $headers_raw);

    if ($title_col === -1 || $date_col === -1) {
        echo '<div class="notice notice-error"><p>Required headers not found. Needed: <strong>Title (main)</strong> and <strong>Auction date (latest)</strong>.</p></div>';
        return;
    }

    $updated = 0;
    $skipped_empty = 0;
    $skipped_no_post = 0;
    $skipped_bad_date = 0;

    for ($i = 1; $i < count($rows); $i++) {
        $row = $rows[$i];

        // Detecta fila vacía
        $nonEmpty = false;
        foreach ($row as $cell) {
            if ((string)$cell !== '') {
                $nonEmpty = true;
                break;
            }
        }
        if (!$nonEmpty) {
            $skipped_empty++;
            continue;
        }

        $title = wp_strip_all_tags(trim((string)$row[$title_col]));
        $date_raw = trim((string)$row[$date_col]);

        if ($title === '' || $date_raw === '') {
            $skipped_empty++;
            continue;
        }

        // Normaliza a 'Y-m-d H:i'
        $date_norm = vehicles_excel_serial_to_datetime($date_raw, 'Y-m-d H:i');
        if ($date_norm === '') {
            $skipped_bad_date++;
            continue;
        }

        // Buscar post por título EXACTO en CPT vehicles
        $post = get_page_by_title($title, OBJECT, HNH_IMPORT_POST_TYPE);
        if (!$post || $post->post_status !== 'publish') {
            // Si no está publish, intenta cualquier estado
            if (!$post) {
                $q = new WP_Query([
                    'post_type'      => HNH_IMPORT_POST_TYPE,
                    'title'          => $title, // WordPress no soporta 'title' de fábrica, pero mantenemos por claridad
                    'post_status'    => 'any',
                    'posts_per_page' => 1,
                    'fields'         => 'ids',
                    's'              => $title, // fallback fuzzy si hay variaciones menores
                    'no_found_rows'  => true,
                ]);
                if (!empty($q->posts)) {
                    $post_id = (int) $q->posts[0];
                } else {
                    $post_id = 0;
                }
            } else {
                $post_id = (int) $post->ID;
            }
        } else {
            $post_id = (int) $post->ID;
        }

        if (!$post_id) {
            $skipped_no_post++;
            continue;
        }

        // Actualiza ACF (o meta si no existe ACF)
        if (function_exists('update_field')) {
            update_field('auction_date_latest', $date_norm, $post_id);
        } else {
            update_post_meta($post_id, 'auction_date_latest', $date_norm);
        }

        $updated++;
    }

    echo '<div class="notice notice-success"><p>'
        . 'Dates update finished. '
        . 'Updated: <strong>' . intval($updated) . '</strong> '
        . '| Skipped (empty rows): ' . intval($skipped_empty) . ' '
        . '| Skipped (no matching post): ' . intval($skipped_no_post) . ' '
        . '| Skipped (invalid date): ' . intval($skipped_bad_date)
        . '</p></div>';
}

/**
 * Excel/Texto → string fecha normalizada (por defecto 'Y-m-d H:i').
 * - Detecta explícitamente dd/mm/yyyy [+ hh:mm[:ss]]
 * - Soporta / - .
 * - Convierte serial Excel válido (días desde 1899-12-30).
 * - Fallback europeo controlado.
 */
function vehicles_excel_serial_to_datetime($value, $format = 'Y-m-d H:i')
{
    if ($value === '' || $value === null) return '';

    // Normaliza espacios
    $s = trim((string)$value);
    $s = preg_replace('~\s+~', ' ', $s);

    // 1) dd/mm/yyyy (o dd-mm-yyyy / dd.mm.yyyy) con hora opcional HH:mm[:ss]
    if (preg_match('~(?<!\d)(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{2,4})(?:\s+(\d{1,2}):(\d{2})(?::(\d{2}))?)?~', $s, $m)) {
        $d  = (int)$m[1];
        $mo = (int)$m[2];
        $y  = (int)$m[3];
        if ($y < 100) $y += ($y >= 70 ? 1900 : 2000);
        $H  = isset($m[4]) ? (int)$m[4] : 0;
        $i  = isset($m[5]) ? (int)$m[5] : 0;
        $sec = isset($m[6]) ? (int)$m[6] : 0;

        try {
            $tz = wp_timezone();
            $dt = new DateTime(sprintf('%04d-%02d-%02d %02d:%02d:%02d', $y, $mo, $d, $H, $i, $sec), $tz);
            return $dt->format($format);
        } catch (Exception $e) { /* sigue */
        }
    }

    // 2) Serial Excel (número de días desde 1899-12-30). Limita rango razonable.
    if (is_numeric($s)) {
        $num = (float)$s;
        if ($num > 0 && $num < 100000) {
            try {
                $tz   = wp_timezone();
                $base = new DateTime('1899-12-30 00:00:00', $tz); // corrige bug 1900
                $days = (int) floor($num);
                $frac = max(0, $num - $days);
                $seconds = (int) round($frac * 86400);
                $base->modify('+' . $days . ' days');
                if ($seconds) $base->modify('+' . $seconds . ' seconds');
                return $base->format($format);
            } catch (Exception $e) { /* sigue */
            }
        }
    }

    // 3) Fallback europeo controlado (dd-mm-yyyy[ hh:mm[:ss]])
    if (preg_match('~^(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{2,4})(?:\s+(\d{1,2}):(\d{2})(?::(\d{2}))?)?$~', $s, $m)) {
        $d  = (int)$m[1];
        $mo = (int)$m[2];
        $y  = (int)$m[3];
        if ($y < 100) $y += ($y >= 70 ? 1900 : 2000);
        $H  = isset($m[4]) ? (int)$m[4] : 0;
        $i  = isset($m[5]) ? (int)$m[5] : 0;
        $sec = isset($m[6]) ? (int)$m[6] : 0;
        try {
            $tz = wp_timezone();
            $dt = new DateTime(sprintf('%04d-%02d-%02d %02d:%02d:%02d', $y, $mo, $d, $H, $i, $sec), $tz);
            return $dt->format($format);
        } catch (Exception $e) { /* ignore */
        }
    }

    // 4) Nada funcionó
    return '';
}

// === Exists check among PUBLISHED vehicles only ===
function vehicles_published_exists_by_stock_number($stock)
{
    $q = new WP_Query([
        'post_type'      => HNH_IMPORT_POST_TYPE,
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => [
            [
                'key'   => 'stock_number',
                'value' => $stock,
            ],
        ],
    ]);
    return !empty($q->posts);
}

// === Helpers ===
function vehicles_find_header(array $candidates, array $headers)
{
    foreach ($candidates as $c) {
        $i = array_search($c, $headers, true);
        if ($i !== false) return $i;
    }
    return -1;
}

function vehicles_sanitize_field_name($label)
{
    $name = strtolower($label);
    $name = preg_replace('~[^a-z0-9]+~', '_', $name);
    return trim($name, '_');
}

// CamelCase para términos
function vehicles_to_camelcase($value)
{
    $v = trim((string)$value);
    if ($v === '') return '';
    $v = preg_replace('/[^\p{L}\p{Nd}]+/u', ' ', $v);
    $v = mb_convert_case($v, MB_CASE_TITLE, 'UTF-8');
    $v = str_replace(' ', '', $v);
    return $v;
}

/**
 * Get or create a taxonomy term by CamelCase name (with small cache).
 */
function vehicles_get_or_create_term($camelName, $taxonomy, array &$cache)
{
    if ($camelName === '' || !taxonomy_exists($taxonomy)) return 0;
    if (isset($cache[$camelName])) return (int)$cache[$camelName];

    $existing = get_term_by('name', $camelName, $taxonomy);
    if ($existing && !is_wp_error($existing)) {
        $cache[$camelName] = (int)$existing->term_id;
        return (int)$existing->term_id;
    }

    $res = wp_insert_term($camelName, $taxonomy, ['slug' => sanitize_title($camelName)]);
    if (!is_wp_error($res) && isset($res['term_id'])) {
        $cache[$camelName] = (int)$res['term_id'];
        return (int)$res['term_id'];
    }

    $existing = get_term_by('name', $camelName, $taxonomy);
    if ($existing && !is_wp_error($existing)) {
        $cache[$camelName] = (int)$existing->term_id;
        return (int)$existing->term_id;
    }
    return 0;
}

// Featured image
function vehicles_set_featured_image_from_url($post_id, $url)
{
    if (!function_exists('download_url')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    if (!function_exists('media_handle_sideload')) {
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }

    $tmp = download_url($url);
    if (is_wp_error($tmp)) return false;

    $name = basename(parse_url($url, PHP_URL_PATH)) ?: 'image.jpg';
    $file = ['name' => sanitize_file_name($name), 'tmp_name' => $tmp];

    $att_id = media_handle_sideload($file, $post_id);
    if (is_wp_error($att_id)) {
        @unlink($tmp);
        return false;
    }
    set_post_thumbnail($post_id, $att_id);
    return true;
}

// XLSX reader (preserves gaps)
function vehicles_include_simplexlsx()
{
    if (class_exists('SimpleXLSX')) return;

    class SimpleXLSX
    {
        private $rows = [];
        private static $error = '';

        public static function parse($filename)
        {
            $sx = new self();
            if (!$sx->open($filename)) return false;
            return $sx;
        }
        public function rows()
        {
            return $this->rows;
        }
        public static function parseError()
        {
            return self::$error;
        }

        private function open($filename)
        {
            if (!class_exists('ZipArchive')) {
                self::$error = 'ZipArchive not available in PHP';
                return false;
            }
            $zip = new ZipArchive();
            if ($zip->open($filename) !== true) {
                self::$error = 'Could not open ZIP archive';
                return false;
            }

            $shared = [];
            if (($idx = $zip->locateName('xl/sharedStrings.xml')) !== false) {
                $xml = simplexml_load_string($zip->getFromIndex($idx));
                foreach ($xml->si as $si) {
                    if (isset($si->t)) $shared[] = (string)$si->t;
                    elseif (isset($si->r)) {
                        $buf = '';
                        foreach ($si->r as $r) {
                            $buf .= (string)$r->t;
                        }
                        $shared[] = $buf;
                    } else $shared[] = '';
                }
            }

            $sheetIndex = $zip->locateName('xl/worksheets/sheet1.xml');
            if ($sheetIndex === false) {
                self::$error = 'sheet1.xml not found';
                return false;
            }
            $xml = simplexml_load_string($zip->getFromIndex($sheetIndex));

            $rows = [];
            foreach ($xml->sheetData->row as $row) {
                $r = [];
                foreach ($row->c as $c) {
                    $ref = isset($c['r']) ? (string)$c['r'] : '';
                    $colIndex = self::colIndexFromRef($ref);
                    $t  = (string)$c['t'];
                    $v  = (string)$c->v;
                    $val = ($t === 's') ? ($shared[(int)$v] ?? '') : (($t === 'inlineStr' && isset($c->is->t)) ? (string)$c->is->t : $v);
                    $r[$colIndex] = $val;
                }
                if (!empty($r)) {
                    ksort($r);
                    $max = max(array_keys($r));
                    $rowVals = array_fill(0, $max + 1, '');
                    foreach ($r as $idx => $val) $rowVals[$idx] = $val;
                    $rows[] = $rowVals;
                } else {
                    $rows[] = [];
                }
            }
            $this->rows = $rows;
            $zip->close();
            return true;
        }

        private static function colIndexFromRef($ref)
        {
            if (!preg_match('/^([A-Z]+)\d+$/i', $ref, $m)) return 0;
            $letters = strtoupper($m[1]);
            $n = 0;
            for ($i = 0; $i < strlen($letters); $i++) {
                $n = $n * 26 + (ord($letters[$i]) - 64);
            }
            return $n - 1;
        }
    }
}

/**
 * Pretty-format CRM raw description into HTML for WP editor (bold labels + bullets → list + paragraphs).
 */
function vehicles_format_description($raw)
{
    $s = trim((string)$raw);
    if ($s === '') return '';
    $s = str_replace(["\r\n", "\r"], "\n", $s);

    $labels = ['Registration No', 'Frame No', 'Chassis No', 'Engine No', 'MOT', 'VIN', 'Mileage', 'Color', 'Colour'];
    foreach ($labels as $label) {
        $pattern = '~(?<=^|\n|\A)(' . preg_quote($label, '~') . '):\s*~i';
        $s = preg_replace($pattern, '<strong>$1:</strong> ', $s);
    }

    $parts = preg_split('~\s*•\s*~u', $s, -1, PREG_SPLIT_NO_EMPTY);
    if ($parts && count($parts) > 1) {
        $intro = trim(array_shift($parts));
        $intro = preg_replace("~\n{2,}~", "\n\n", $intro);
        $intro_html = '';
        foreach (preg_split("~\n{2,}~", $intro) as $para) {
            $intro_html .= '<p>' . nl2br(esc_html(trim($para))) . '</p>';
        }
        $lis = '';
        foreach ($parts as $item) {
            $item = trim($item);
            if ($item === '') continue;
            $lis .= '<li>' . esc_html($item) . '</li>';
        }
        if ($lis !== '') return $intro_html . '<ul>' . $lis . '</ul>';
        return $intro_html;
    }

    $s = preg_replace("~[ \t]+~", ' ', $s);
    $s = preg_replace("~\n{3,}~", "\n\n", $s);
    $html = '';
    foreach (preg_split("~\n{2,}~", $s) as $para) {
        $para = trim($para);
        if ($para === '') continue;
        $html .= '<p>' . nl2br(esc_html($para)) . '</p>';
    }
    return $html;
}

/**
 * Extrae Registration No / Chassis No / MOT desde el HTML/texto de la descripción.
 */
function vehicles_extract_from_description($html)
{
    // Normaliza: <br> -> saltos de línea, elimina etiquetas, decodifica entidades
    $text = (string) $html;
    $text = preg_replace('~<br\s*/?>~i', "\n", $text);
    $text = wp_strip_all_tags($text);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5);
    $text = trim(preg_replace("/[ \t\x{00A0}]+/u", ' ', $text)); // colapsa espacios (incluye NBSP)

    $out = [
        'registration_no' => '',
        'chassis_no'      => '',
        'mot'             => '',
    ];

    // Captura hasta fin de línea
    $patterns = [
        'registration_no' => '~\bRegistration\s*(?:No\.?|Number)?\s*:\s*([^\r\n]+)~i',
        'chassis_no'      => '~\bChassis\s*(?:No\.?|Number)?\s*:\s*([^\r\n]+)~i',
        'mot'             => '~\bMOT\s*:\s*([^\r\n]+)~i',
    ];

    foreach ($patterns as $key => $regex) {
        if (preg_match($regex, $text, $m)) {
            $val = trim($m[1]);
            $val = preg_split('~\s*(?:Registration\s*(?:No\.?|Number)?|Chassis\s*(?:No\.?|Number)?|MOT)\s*:~i', $val, 2)[0];
            $out[$key] = trim($val, " \t\n\r\0\x0B\xC2\xA0");
        }
    }

    return $out;
}

/**
 * Devuelve el ID del post (CPT team) que mejor coincide con el nombre dado.
 * Intenta: título exacto, slug exacto, búsqueda. Usa caché simple por nombre normalizado.
 */
function vehicles_get_team_id_by_display($raw, array &$cache = [])
{
    $name = vehicles_normalize_person_name($raw);
    if ($name === '') return 0;

    if (isset($cache[$name])) return (int)$cache[$name];

    // 1) título exacto (case-insensitive)
    $post = get_page_by_title($name, OBJECT, HNH_TEAM_POST_TYPE);
    if ($post && $post->post_status === 'publish') {
        return $cache[$name] = (int)$post->ID;
    }

    // 2) slug exacto
    $slug = sanitize_title($name);
    $q = new WP_Query([
        'post_type'      => HNH_TEAM_POST_TYPE,
        'name'           => $slug,
        'post_status'    => 'publish',
        'fields'         => 'ids',
        'posts_per_page' => 1,
        'no_found_rows'  => true,
    ]);
    if (!empty($q->posts)) {
        return $cache[$name] = (int)$q->posts[0];
    }

    // 3) búsqueda
    $q = new WP_Query([
        'post_type'      => HNH_TEAM_POST_TYPE,
        's'              => $name,
        'post_status'    => 'publish',
        'fields'         => 'ids',
        'posts_per_page' => 1,
        'no_found_rows'  => true,
    ]);
    if (!empty($q->posts)) {
        return $cache[$name] = (int)$q->posts[0];
    }

    return $cache[$name] = 0;
}

/**
 * Normaliza nombres humanos:
 * - toma el primer nombre si vienen varios (/, &, and, coma, etc.)
 * - elimina paréntesis
 * - convierte "APELLIDO, Nombre" a "Nombre Apellido"
 * - colapsa espacios y aplica Title Case
 */
function vehicles_normalize_person_name($raw)
{
    $s = trim((string)$raw);
    if ($s === '') return '';

    // primer contacto si viene "Nombre A / Nombre B"
    $s = preg_split('~[\/,&|;]|\\band\\b~i', $s, 2)[0];

    // quita paréntesis
    $s = preg_replace('~\([^)]*\)~', '', $s);

    // "APELLIDO, Nombre" → "Nombre Apellido"
    if (preg_match('~^\s*([^,]+),\s*(.+)$~', $s, $m)) {
        $s = trim($m[2] . ' ' . $m[1]);
    }

    $s = preg_replace('~\s+~', ' ', $s);
    $s = mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');

    return trim($s);
}