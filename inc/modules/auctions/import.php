<?php
// ====== Ajustes ======
const AU_IMPORT_POST_TYPE = 'auction';
const AU_MENU_PARENT      = 'edit.php?post_type=auction';

// ====== Menú: Auctions → Import Auctions ======
add_action('admin_menu', function () {
    add_submenu_page(
        AU_MENU_PARENT,
        'Import Auctions',
        'Import Auctions',
        'manage_options',
        'import-auctions',
        'au_import_render_page'
    );
});

function au_import_render_page()
{ ?>
    <div class="wrap">
        <h1>Import Auctions</h1>
        <p>Upload a <strong>.xlsx</strong> (from the CRM) or <strong>.csv</strong> (comma-separated). The first row must be the header.</p>
        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('au_import_nonce', 'au_import_nonce_f'); ?>
            <input type="file" name="au_file" accept=".xlsx,.csv" required />
            <p><button class="button button-primary">Import</button></p>
        </form>
        <?php
        if (!empty($_FILES['au_file']) && isset($_POST['au_import_nonce_f']) && wp_verify_nonce($_POST['au_import_nonce_f'], 'au_import_nonce')) {
            au_handle_import($_FILES['au_file']);
        }
        ?>
    </div>
<?php }

// ====== Importador ======
function au_handle_import($file)
{
    if (!current_user_can('manage_options')) wp_die('No permission.');

    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo '<div class="notice notice-error"><p>Upload error.</p></div>';
        return;
    }

    $uploaded = wp_handle_upload($file, ['test_form' => false]);
    if (!empty($uploaded['error'])) {
        echo '<div class="notice notice-error"><p>' . esc_html($uploaded['error']) . '</p></div>';
        return;
    }

    $path = $uploaded['file'];
    $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    // Lee filas
    $rows = [];
    if ($ext === 'xlsx') {
        if (!class_exists('AU_SimpleXLSX')) au_include_simplexlsx();
        $xlsx = AU_SimpleXLSX::parse($path);
        if (!$xlsx) {
            echo '<div class="notice notice-error"><p>Could not read XLSX: ' . esc_html(AU_SimpleXLSX::parseError()) . '</p></div>';
            return;
        }
        $rows = $xlsx->rows();
    } elseif ($ext === 'csv') {
        if (($h = fopen($path, 'r')) !== false) {
            while (($data = fgetcsv($h, 0, ',', '"', '\\')) !== false) $rows[] = $data;
            fclose($h);
        }
    } else {
        echo '<div class="notice notice-error"><p>Unsupported extension.</p></div>';
        return;
    }

    if (count($rows) < 2) {
        echo '<div class="notice notice-warning"><p>Not enough data.</p></div>';
        return;
    }

    // Normaliza largo por encabezado
    $headers   = $rows[0];
    $headerLen = is_array($headers) ? count($headers) : 0;
    while ($headerLen > 0 && trim((string)$headers[$headerLen - 1]) === '') {
        array_pop($headers);
        $headerLen--;
    }
    if ($headerLen === 0) {
        echo '<div class="notice notice-error"><p>Empty header.</p></div>';
        return;
    }

    foreach ($rows as $i => $r) {
        if (!is_array($r)) $r = [];
        $cnt = count($r);
        if ($cnt < $headerLen) $rows[$i] = array_pad($r, $headerLen, '');
        elseif ($cnt > $headerLen) $rows[$i] = array_slice($r, 0, $headerLen);
    }

    // Mapa posicional → nombres ACF (slugificados)
    $map_by_index = [];
    foreach ($headers as $colIdx => $label) {
        $label = sanitize_text_field($label);
        $name  = au_sanitize_field_name($label);
        $map_by_index[$colIdx] = $name ?: null;
    }

    // Columna título
    $title_col = au_find_header(['Auction name', 'Auction Name', 'Name', 'Title (main)', 'Title'], $headers);

    // Columnas de fecha (para convertir)
    $date_like_cols = [];
    foreach ($headers as $colIdx => $label) {
        if (stripos($label, 'date') !== false) $date_like_cols[] = $colIdx;
    }

    $created = 0;
    $skipped_empty = 0;

    for ($i = 1; $i < count($rows); $i++) {
        $row = $rows[$i];

        // Salta filas vacías
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

        // Título desde "Auction name"
        $post_title = ($title_col !== -1) ? wp_strip_all_tags((string)$row[$title_col]) : '';
        if ($post_title === '') $post_title = 'Auction ' . $i;

        // Crea post
        $post_id = wp_insert_post([
            'post_type'    => AU_IMPORT_POST_TYPE,
            'post_status'  => 'publish',
            'post_title'   => $post_title,
            'post_content' => '',
        ], true);
        if (is_wp_error($post_id)) {
            $skipped_empty++;
            continue;
        }

        // Guarda cada columna como su campo ACF posicional
        foreach ($headers as $colIdx => $header_label) {
            $field_name = $map_by_index[$colIdx] ?? null;
            if (!$field_name) continue;

            $value = (string)$row[$colIdx];

            if (in_array($colIdx, $date_like_cols, true)) {
                $value = au_excel_serial_to_datetime($value, 'Y-m-d H:i');
            }

            update_field($field_name, $value, $post_id);
        }

        $created++;
    }

    echo '<div class="notice notice-success"><p>'
        . 'Import completed. Created: <strong>' . (int)$created . '</strong> '
        . '| Skipped (empty rows/errors): ' . (int)$skipped_empty
        . '</p></div>';
}

// ====== Utilidades ======
function au_find_header(array $cands, array $headers)
{
    foreach ($cands as $c) {
        $i = array_search($c, $headers, true);
        if ($i !== false) return $i;
    }
    return -1;
}

function au_sanitize_field_name($label)
{
    $name = strtolower($label);
    $name = preg_replace('~[^a-z0-9]+~', '_', $name);
    return trim($name, '_');
}

/**
 * Conversor robusto de fecha/hora:
 * - dd/mm/YYYY HH:mm[:ss] (soporta dobles espacios)
 * - YYYY-mm-dd HH:mm[:ss]
 * - mm/dd/YYYY HH:mm[:ss]
 * - Serial Excel con . o , decimal (45919.5 / 45919,5)
 * - Serial Excel como "DÍAS HH:MM[:SS]" (p.e. "45919 00:05")
 */
function au_excel_serial_to_datetime($value, $format = 'Y-m-d H:i')
{
    if ($value === '' || $value === null) return '';

    $tz = wp_timezone();
    $s  = trim((string)$value);
    if ($s === '') return '';

    // Normaliza espacios múltiples
    $s = preg_replace('/\s+/', ' ', $s);

    // 1) Serial Excel "NNNNN[.fraction]" con punto o coma
    if (preg_match('/^\d+(?:[.,]\d+)?$/', $s)) {
        $f = (float) str_replace(',', '.', $s);
        if ($f <= 0) return '';
        $base = new DateTime('1899-12-30 00:00:00', $tz);
        $days = (int) floor($f);
        $frac = max(0, $f - $days);
        $seconds = (int) round($frac * 86400);
        $base->modify("+{$days} days");
        if ($seconds) $base->modify("+{$seconds} seconds");
        return $base->format($format);
    }

    // 2) Serial Excel "DÍAS HH:MM[:SS]" separado por espacio (p.e. "45919 00:05" / "45919 12:00:00")
    if (preg_match('/^(\d+)\s+(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $s, $m)) {
        $days = (int) $m[1];
        $h    = (int) $m[2];
        $min  = (int) $m[3];
        $sec  = isset($m[4]) ? (int)$m[4] : 0;
        if ($days <= 0 && $h === 0 && $min === 0 && $sec === 0) return '';
        $base = new DateTime('1899-12-30 00:00:00', $tz);
        $base->modify("+{$days} days");
        $seconds = $h * 3600 + $min * 60 + $sec;
        if ($seconds) $base->modify("+{$seconds} seconds");
        return $base->format($format);
    }

    // 3) Formatos explícitos comunes (incluye segundos)
    $formats = [
        'd/m/Y H:i:s',
        'd/m/Y H:i',
        'd/m/Y',
        'Y-m-d H:i:s',
        'Y-m-d',
        'm/d/Y H:i:s',
        'm/d/Y H:i',
        'm/d/Y',
    ];
    foreach ($formats as $f) {
        $dt = DateTime::createFromFormat($f, $s, $tz);
        if ($dt instanceof DateTime) {
            // Asegura que no haya falsos positivos con warnings
            $errs = DateTime::getLastErrors();
            if ($errs['warning_count'] === 0 && $errs['error_count'] === 0) {
                return $dt->format($format);
            }
        }
    }

    // 4) Último recurso: strtotime (puede depender de locale/servidor)
    $ts = strtotime($s);
    if ($ts !== false) return wp_date($format, $ts, $tz);

    return '';
}

// ====== Lector XLSX que preserva vacíos intermedios ======
function au_include_simplexlsx()
{
    if (class_exists('AU_SimpleXLSX')) return;
    class AU_SimpleXLSX
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
                self::$error = 'ZipArchive not available';
                return false;
            }
            $zip = new ZipArchive();
            if ($zip->open($filename) !== true) {
                self::$error = 'Cannot open zip';
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
                    $col = $this->colIndexFromRef($ref);
                    $t = (string)$c['t'];
                    $v = (string)$c->v;
                    $val = '';
                    if ($t === 's') {
                        $val = $shared[(int)$v] ?? '';
                    } elseif ($t === 'inlineStr' && isset($c->is->t)) {
                        $val = (string)$c->is->t;
                    } else {
                        $val = $v;
                    }
                    $r[$col] = $val;
                }
                if (!empty($r)) {
                    ksort($r);
                    $max = max(array_keys($r));
                    $rowVals = array_fill(0, $max + 1, '');
                    foreach ($r as $i => $val) $rowVals[$i] = $val;
                    $rows[] = $rowVals;
                } else {
                    $rows[] = [];
                }
            }
            $this->rows = $rows;
            $zip->close();
            return true;
        }
        private function colIndexFromRef($ref)
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