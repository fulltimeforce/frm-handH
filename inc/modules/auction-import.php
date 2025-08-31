<?php
// === Admin Page: Auctions → Import Auctions ===
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

function auctions_import_render_page() {
    ?>
    <div class="wrap">
        <h1>Import Auctions</h1>
        <p>Upload a <strong>.xlsx</strong> file (from the CRM) or a <strong>.csv</strong> file (comma-separated). The first row must be the header.</p>
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
function auctions_handle_import($file) {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to perform this action.');
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo '<div class="notice notice-error"><p>File upload error.</p></div>';
        return;
    }

    // Move file to /uploads
    $uploaded = wp_handle_upload($file, ['test_form' => false]);
    if (!empty($uploaded['error'])) {
        echo '<div class="notice notice-error"><p>' . esc_html($uploaded['error']) . '</p></div>';
        return;
    }

    $path = $uploaded['file'];
    $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    // Rows as [ [col1, col2, ...], ... ]
    $rows = [];
    if ($ext === 'xlsx') {
        // Lightweight XLSX reader without external plugins
        if (!class_exists('SimpleXLSX')) {
            auctions_include_simplexlsx();
        }
        $xlsx = SimpleXLSX::parse($path);
        if (!$xlsx) {
            echo '<div class="notice notice-error"><p>Could not read XLSX: ' . esc_html(SimpleXLSX::parseError()) . '</p></div>';
            return;
        }
        $rows = $xlsx->rows();
    } elseif ($ext === 'csv') {
        if (($handle = fopen($path, 'r')) !== false) {
            while (($data = fgetcsv($handle, 0, ',')) !== false) {
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

    // Headers → ACF field names
    $headers_raw = $rows[0];
    $headers = array_map('sanitize_text_field', $headers_raw);

    // Map header → sanitized name (must match the provided ACF JSON)
    $map = [];
    foreach ($headers as $h) {
        $map[$h] = auctions_sanitize_field_name($h);
    }

    // Heuristics for post title and content
    $title_col   = auctions_find_header(['Title (main)', 'Title', 'Name'], $headers_raw);
    $content_col = auctions_find_header(['Description', 'Desc'], $headers_raw);

    $created = 0; $updated = 0; $skipped = 0;

    for ($i = 1; $i < count($rows); $i++) {
        $row = $rows[$i];
        if (!is_array($row) || count(array_filter($row, fn($v) => (string)$v !== '')) === 0) {
            $skipped++; continue;
        }

        // Post title / content
        $post_title   = $title_col !== -1 ? wp_strip_all_tags((string)($row[$title_col] ?? '')) : '';
        if ($post_title === '') { $post_title = 'Auction ' . $i; }

        $post_content = $content_col !== -1 ? (string)($row[$content_col] ?? '') : '';

        // Create post
        $postarr = [
            'post_type'    => 'auction',
            'post_status'  => 'publish',
            'post_title'   => $post_title,
            'post_content' => $post_content,
        ];
        $post_id = wp_insert_post($postarr, true);
        if (is_wp_error($post_id)) {
            $skipped++; continue;
        }

        // Save all fields to ACF
        foreach ($headers_raw as $idx => $header_label) {
            $field_name = $map[$header_label] ?? null;
            if (!$field_name) { continue; }

            $value = $row[$idx] ?? '';

            // Type normalizations (must match the provided ACF field types)
            if ($field_name === 'auction_date_latest') {
                // Try to parse date and store as Y-m-d
                $value = auctions_parse_excel_date($value);
            }
            if (in_array($field_name, ['vendor_client_id','buyer_client_id','sold_price'], true)) {
                $value = is_numeric($value) ? 0 + $value : null;
            }
            if ($field_name === 'catalogued_flag') {
                $value = in_array(strtolower(trim((string)$value)), ['1','true','yes','y','si','sí'], true) ? 1 : 0;
            }

            // ACF: save by "name"
            update_field($field_name, $value, $post_id);
        }

        $created++;
    }

    echo '<div class="notice notice-success"><p>Import completed. Created: <strong>' . intval($created) . '</strong> | Skipped: ' . intval($skipped) . '</p></div>';
}

// === Helpers ===
function auctions_find_header(array $candidates, array $headers) {
    foreach ($candidates as $c) {
        $i = array_search($c, $headers, true);
        if ($i !== false) return $i;
    }
    return -1;
}

function auctions_sanitize_field_name($label) {
    $name = strtolower($label);
    $name = preg_replace('~[^a-z0-9]+~', '_', $name);
    $name = trim($name, '_');
    return $name;
}

function auctions_parse_excel_date($value) {
    // 1) Already Y-m-d or Y/m/d
    if (preg_match('~^\d{4}[-/]\d{2}[-/]\d{2}$~', (string)$value)) {
        return str_replace('/', '-', (string)$value);
    }
    // 2) Excel serial number (days since 1899-12-30)
    if (is_numeric($value)) {
        $base = new DateTime('1899-12-30', wp_timezone());
        $base->modify('+' . intval($value) . ' days');
        return $base->format('Y-m-d');
    }
    // 3) Fallback parsing
    $ts = strtotime((string)$value);
    return $ts ? date('Y-m-d', $ts) : '';
}

// === Simple embedded XLSX reader (MIT) ===
function auctions_include_simplexlsx() {
    if (class_exists('SimpleXLSX')) return;
    class SimpleXLSX {
        private $rows = [];
        private static $error = '';
        public static function parse($filename) {
            $sx = new self();
            if (!$sx->open($filename)) return false;
            return $sx;
        }
        public function rows() { return $this->rows; }
        public static function parseError() { return self::$error; }

        private function open($filename) {
            if (!class_exists('ZipArchive')) { self::$error = 'ZipArchive not available in PHP'; return false; }
            $zip = new ZipArchive();
            if ($zip->open($filename) !== true) { self::$error = 'Could not open ZIP archive'; return false; }

            // sharedStrings
            $shared = [];
            if (($idx = $zip->locateName('xl/sharedStrings.xml')) !== false) {
                $xml = simplexml_load_string($zip->getFromIndex($idx));
                foreach ($xml->si as $si) {
                    $shared[] = (string) $si->t;
                }
            }

            // sheet1 (most exports use a single sheet)
            $sheetIndex = $zip->locateName('xl/worksheets/sheet1.xml');
            if ($sheetIndex === false) { self::$error = 'sheet1.xml not found'; return false; }
            $xml = simplexml_load_string($zip->getFromIndex($sheetIndex));
            $xml->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

            $rows = [];
            foreach ($xml->sheetData->row as $row) {
                $r = [];
                foreach ($row->c as $c) {
                    $v = (string) $c->v;
                    $t = (string) $c['t'];
                    if ($t === 's') { // shared string
                        $idx = (int) $v;
                        $r[] = $shared[$idx] ?? '';
                    } else {
                        $r[] = $v;
                    }
                }
                $rows[] = $r;
            }
            $this->rows = $rows;
            $zip->close();
            return true;
        }
    }
}