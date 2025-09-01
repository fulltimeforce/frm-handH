<?php
// === Admin Page: Auctions → Import Auctions (strict mapping + published-only stock check) ===
add_action('admin_menu', function () {
    add_submenu_page(
        'edit.php?post_type=auction',
        'Import Auctions',
        'Import Auctions',
        'manage_options',
        'import-auctions',
        'auctions_import_render_page'
    );
});

function auctions_import_render_page()
{
?>
    <div class="wrap">
        <h1>Import Auctions</h1>
        <p>Upload a <strong>.xlsx</strong> (from the CRM) or <strong>.csv</strong> (comma-separated). The first row must be the header.</p>
        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('auctions_import_nonce', 'auctions_import_nonce_f'); ?>
            <input type="file" name="auctions_file" accept=".xlsx,.csv" required />
            <p><button class="button button-primary">Import</button></p>
        </form>
        <?php
        if (!empty($_FILES['auctions_file']) && isset($_POST['auctions_import_nonce_f']) && wp_verify_nonce($_POST['auctions_import_nonce_f'], 'auctions_import_nonce')) {
            auctions_handle_import($_FILES['auctions_file']);
        }
        ?>
    </div>
<?php
}

// === Import Handler ===
function auctions_handle_import($file)
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
            auctions_include_simplexlsx();
        }
        $xlsx = SimpleXLSX::parse($path);
        if (!$xlsx) {
            echo '<div class="notice notice-error"><p>Could not read XLSX: ' . esc_html(SimpleXLSX::parseError()) . '</p></div>';
            return;
        }
        $rows = $xlsx->rows(); // preserves middle gaps by cell refs
    } elseif ($ext === 'csv') {
        if (($handle = fopen($path, 'r')) !== false) {
            // keep empty fields as empty strings in their exact position
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

    // Trim trailing empty header cells
    while ($headerLen > 0 && trim((string)$headers_raw[$headerLen - 1]) === '') {
        array_pop($headers_raw);
        $headerLen--;
    }
    if ($headerLen === 0) {
        echo '<div class="notice notice-error"><p>Header row is empty.</p></div>';
        return;
    }

    // Pad / slice all rows to exactly headerLen
    foreach ($rows as $i => $r) {
        if (!is_array($r)) $r = [];
        $count = count($r);
        if ($count < $headerLen) {
            $rows[$i] = array_pad($r, $headerLen, '');
        } elseif ($count > $headerLen) {
            $rows[$i] = array_slice($r, 0, $headerLen);
        }
    }

    // === Map by COLUMN INDEX (positional, avoids collisions)
    $map_by_index = [];
    foreach ($headers_raw as $colIdx => $label) {
        $label = sanitize_text_field($label);
        $name  = auctions_sanitize_field_name($label);
        $map_by_index[$colIdx] = $name ?: null; // skip unnamed headers
    }

    // Useful columns (by header text)
    $title_col     = auctions_find_header(['Title (main)', 'Title', 'Name'], $headers_raw);
    $content_col   = auctions_find_header(['Description', 'Desc'], $headers_raw);
    $image_url_col = auctions_find_header(['Image URL (main image)', 'Image URL', 'Main image URL'], $headers_raw);
    $stock_col     = auctions_find_header(['Stock number', 'Stock Number'], $headers_raw);

    $created = 0;
    $skipped_empty = 0;
    $skipped_existing = 0;
    $skipped_no_stock = 0;

    $seen_stocks_in_file = [];

    // Iterate row 2, 3, 4, ...
    for ($i = 1; $i < count($rows); $i++) {
        $row = $rows[$i];

        // Completely empty row?
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

        // Stock number (normalize: trim, uppercase, remove inner spaces)
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

        // Skip if already exists among PUBLISHED only
        if (auctions_published_exists_by_stock_number($stock_value)) {
            $skipped_existing++;
            continue;
        }

        // Title / Content
        $post_title   = $title_col !== -1 ? wp_strip_all_tags((string)$row[$title_col]) : '';
        if ($post_title === '') {
            $post_title = 'Auction ' . $i;
        }
        $post_content = $content_col !== -1 ? (string)$row[$content_col] : '';

        $post_id = wp_insert_post([
            'post_type'    => 'auction',
            'post_status'  => 'publish',
            'post_title'   => $post_title,
            'post_content' => $post_content,
        ], true);

        if (is_wp_error($post_id)) {
            $skipped_empty++;
            continue;
        }

        // Save every column by exact index (empty stays empty)
        foreach ($headers_raw as $colIdx => $header_label) {
            $field_name = $map_by_index[$colIdx] ?? null; // <-- positional mapping
            if (!$field_name) continue;

            $value = (string)$row[$colIdx];

            // Rules by header *text* (formatting only; DOES NOT change mapping)
            $lower = strtolower((string)$header_label);

            // Any column containing "date": Excel serial/text -> 'Y-m-d H:i'
            if (strpos($lower, 'date') !== false) {
                $value = auctions_excel_serial_to_datetime($value, 'Y-m-d H:i');
            }

            // Keep receipt_number & stock_number as text (no casting)
            if (in_array($field_name, ['receipt_number', 'stock_number'], true)) {
                // leave $value untouched (besides date rule which won't match)
            }

            update_field($field_name, $value, $post_id);
        }

        // Ensure normalized stock number is present
        update_field('stock_number', $stock_value, $post_id);

        // Featured image from "Image URL (main image)"
        if ($image_url_col !== -1) {
            $img_url = trim((string)$row[$image_url_col]);
            if ($img_url !== '') {
                auctions_set_featured_image_from_url($post_id, $img_url);
            }
        }

        $created++;
    }

    echo '<div class="notice notice-success"><p>'
        . 'Import completed. '
        . 'Created: <strong>' . intval($created) . '</strong> '
        . '| Skipped (existing by published stock number): ' . intval($skipped_existing) . ' '
        . '| Skipped (empty rows/errors): ' . intval($skipped_empty) . ' '
        . '| Skipped (no stock number): ' . intval($skipped_no_stock)
        . '</p></div>';
}

// === Excel serial (days from 1899-12-30) -> formatted string ===
function auctions_excel_serial_to_datetime($value, $format = 'Y-m-d H:i')
{
    if ($value === '' || $value === null) return '';

    // Try common text formats first
    $s = trim((string)$value);

    // dd/mm/YYYY HH:ii
    $dt = DateTime::createFromFormat('d/m/Y H:i', $s, wp_timezone());
    if ($dt instanceof DateTime) return $dt->format($format);

    // dd/mm/YYYY
    $dt = DateTime::createFromFormat('d/m/Y', $s, wp_timezone());
    if ($dt instanceof DateTime) return $dt->format($format);

    // Generic parse
    $ts = strtotime($s);
    if ($ts !== false) return date($format, $ts);

    // Excel serial with time fraction
    if (is_numeric($value)) {
        $base = new DateTime('1899-12-30 00:00:00', wp_timezone()); // Excel base
        $days = (int) floor($value);
        $frac = max(0, (float)$value - $days);
        $seconds = (int) round($frac * 86400);

        $base->modify('+' . $days . ' days');
        if ($seconds) $base->modify('+' . $seconds . ' seconds');

        return $base->format($format);
    }

    return '';
}

// === Exists check among PUBLISHED only ===
function auctions_published_exists_by_stock_number($stock)
{
    $q = new WP_Query([
        'post_type'      => 'auction',
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
function auctions_find_header(array $candidates, array $headers)
{
    foreach ($candidates as $c) {
        $i = array_search($c, $headers, true);
        if ($i !== false) return $i;
    }
    return -1;
}

function auctions_sanitize_field_name($label)
{
    $name = strtolower($label);
    $name = preg_replace('~[^a-z0-9]+~', '_', $name);
    return trim($name, '_');
}

// === Featured image from remote URL ===
function auctions_set_featured_image_from_url($post_id, $url)
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

// === XLSX reader that preserves empty middle cells (uses cell refs) ===
function auctions_include_simplexlsx()
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

            // sharedStrings
            $shared = [];
            if (($idx = $zip->locateName('xl/sharedStrings.xml')) !== false) {
                $xml = simplexml_load_string($zip->getFromIndex($idx));
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $shared[] = (string)$si->t;
                    } elseif (isset($si->r)) {
                        $buf = '';
                        foreach ($si->r as $r) {
                            $buf .= (string)$r->t;
                        }
                        $shared[] = $buf;
                    } else {
                        $shared[] = '';
                    }
                }
            }

            // First worksheet
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
                    $colIndex = self::colIndexFromRef($ref); // 0-based

                    $t  = (string)$c['t']; // type
                    $v  = (string)$c->v;   // value
                    $val = '';

                    if ($t === 's') {
                        $idx = (int)$v;
                        $val = $shared[$idx] ?? '';
                    } elseif ($t === 'inlineStr' && isset($c->is->t)) {
                        $val = (string)$c->is->t;
                    } else {
                        $val = $v; // number/date/plain
                    }

                    $r[$colIndex] = $val; // place at correct column (preserves gaps)
                }

                if (!empty($r)) {
                    ksort($r);
                    $max = max(array_keys($r));                 // última columna usada en esta fila
                    $rowVals = array_fill(0, $max + 1, '');    // crea 0..max con '' (preserva vacíos al INICIO)
                    foreach ($r as $idx => $val) {
                        $rowVals[$idx] = $val;                 // coloca cada celda en su índice real
                    }
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
            return $n - 1; // 0-based
        }
    }
}
