<?php
// ============ SETTINGS ============
const HNH_AU_POST_TYPE   = 'auction';
const HNH_AU_MENU_PARENT = 'edit.php?post_type=auction';
// =================================

// Submenú: Auctions → Import Auctions
add_action('admin_menu', function () {
    add_submenu_page(
        HNH_AU_MENU_PARENT,
        'Import Auctions',
        'Import Auctions',
        'manage_options',
        'import-auctions',
        'hnh_au_import_render_page'
    );
});

function hnh_au_import_render_page()
{ ?>
    <div class="wrap">
        <h1>Import Auctions</h1>
        <p>Upload a <strong>.xlsx</strong> (from the CRM) or <strong>.csv</strong>. First row is the header.</p>
        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('hnh_au_import_nonce', 'hnh_au_import_nonce_f'); ?>
            <input type="file" name="au_file" accept=".xlsx,.csv" required />
            <p><button class="button button-primary">Import</button></p>
        </form>
        <?php
        if (
            !empty($_FILES['au_file']) && isset($_POST['hnh_au_import_nonce_f'])
            && wp_verify_nonce($_POST['hnh_au_import_nonce_f'], 'hnh_au_import_nonce')
        ) {
            hnh_au_handle_import($_FILES['au_file']);
        }
        ?>
    </div>
<?php }

// ================== IMPORT ==================
function hnh_au_handle_import($file)
{
    if (!current_user_can('manage_options')) wp_die('No permission');
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

    // Leer filas
    $rows = [];
    if ($ext === 'xlsx') {
        if (!class_exists('HNH_SimpleXLSX_AU')) hnh_au_include_simplexlsx();
        $xlsx = HNH_SimpleXLSX_AU::parse($path);
        if (!$xlsx) {
            echo '<div class="notice notice-error"><p>Could not read XLSX: ' . esc_html(HNH_SimpleXLSX_AU::parseError()) . '</p></div>';
            return;
        }
        $rows = $xlsx->rows();
    } elseif ($ext === 'csv') {
        if (($handle = fopen($path, 'r')) !== false) {
            while (($data = fgetcsv($handle, 0, ',', '"', '\\')) !== false) $rows[] = $data;
            fclose($handle);
        }
    } else {
        echo '<div class="notice notice-error"><p>Unsupported extension: ' . esc_html($ext) . '</p></div>';
        return;
    }

    if (count($rows) < 2) {
        echo '<div class="notice notice-warning"><p>Not enough data.</p></div>';
        return;
    }

    // Normalizar a largo del header
    $headers_raw = $rows[0];
    $headerLen = is_array($headers_raw) ? count($headers_raw) : 0;
    while ($headerLen > 0 && trim((string)$headers_raw[$headerLen - 1]) === '') {
        array_pop($headers_raw);
        $headerLen--;
    }
    if ($headerLen === 0) {
        echo '<div class="notice notice-error"><p>Empty header row.</p></div>';
        return;
    }

    foreach ($rows as $i => $r) {
        if (!is_array($r)) $r = [];
        $count = count($r);
        if ($count < $headerLen) $rows[$i] = array_pad($r, $headerLen, '');
        elseif ($count > $headerLen) $rows[$i] = array_slice($r, 0, $headerLen);
    }

    // Mapeo posicional -> ACF field name (sanitizado)
    $map_by_index = [];
    foreach ($headers_raw as $colIdx => $label) {
        $label = (string)$label;
        $map_by_index[$colIdx] = hnh_au_sanitize_field_name($label) ?: null;
    }

    // Columnas útiles por texto del encabezado
    $name_col = hnh_au_find_header(['Auction name', 'Name', 'Title'], $headers_raw);

    // Contadores
    $created = 0;
    $skipped_empty = 0;

    // NEW: preparar índice de Venues una sola vez
    $venue_index = hnh_au_prepare_venue_index(); // <-- NUEVO

    for ($i = 1; $i < count($rows); $i++) {
        $row = $rows[$i];

        // Fila completamente vacía
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

        // Título = Auction name (si existe), si no, "Auction N"
        $post_title = $name_col !== -1 ? wp_strip_all_tags((string)$row[$name_col]) : '';
        if ($post_title === '') $post_title = 'Auction ' . $i;

        $post_id = wp_insert_post([
            'post_type'    => HNH_AU_POST_TYPE,
            'post_status'  => 'publish',
            'post_title'   => $post_title,
            'post_content' => '',
        ], true);
        if (is_wp_error($post_id)) {
            $skipped_empty++;
            continue;
        }

        // Guardar TODOS los campos ACF por posición
        foreach ($headers_raw as $colIdx => $header_label) {
            $field_name = $map_by_index[$colIdx] ?? null;
            if (!$field_name) continue;

            $value = (string)$row[$colIdx];
            $hl = strtolower(trim((string)$header_label));

            // Fechas: cualquier encabezado que contenga 'date', 'until' o 'time'
            if ($value !== '' && (
                strpos($hl, 'date') !== false ||
                strpos($hl, 'until') !== false ||
                strpos($hl, 'time') !== false
            )) {
                // ⇨ Guardar SIEMPRE como "YYYY-MM-DD HH:MM:SS" (texto plano)
                $value = hnh_au_to_mysql_datetime($value);
            }

            update_field($field_name, $value, $post_id);
        }

        // NEW: intentar asociar automáticamente el Venue al campo ACF `template_venue`
        $guessed_venue_id = hnh_au_guess_venue_id($post_title, $venue_index);   // <-- NUEVO
        if ($guessed_venue_id) {
            update_field('template_venue', (int)$guessed_venue_id, $post_id);    // <-- NUEVO (Page Link guarda ID)
        }

        $created++;
    }

    echo '<div class="notice notice-success"><p>'
        . 'Import completed. Created: <strong>' . intval($created) . '</strong> '
        . '| Skipped (empty rows/errors): ' . intval($skipped_empty)
        . '</p></div>';
}

// ================== HELPERS ==================
function hnh_au_find_header(array $candidates, array $headers)
{
    foreach ($candidates as $c) {
        $i = array_search($c, $headers, true);
        if ($i !== false) return $i;
    }
    return -1;
}

function hnh_au_sanitize_field_name($label)
{
    $name = strtolower((string)$label);
    $name = preg_replace('~[^a-z0-9]+~', '_', $name);
    return trim($name, '_');
}

/**
 * Convierte fechas desde Excel (serial o texto) al formato MySQL:
 *   "YYYY-MM-DD HH:MM:SS"
 * Si no puede interpretarse, devuelve el valor original (trim).
 */
function hnh_au_to_mysql_datetime($value)
{
    if ($value === '' || $value === null) return '';

    $s = trim((string)$value);

    // ¿Numérico puro? (serial Excel)
    if (preg_match('/^\d+(\.\d+)?$/', $s)) {
        $n = (float)$s;
        // base Excel (Windows): 1899-12-30
        $base = new DateTime('1899-12-30 00:00:00', wp_timezone());
        $days = (int) floor($n);
        $seconds = (int) round(($n - $days) * 86400);
        $base->modify("+{$days} days");
        if ($seconds) $base->modify("+{$seconds} seconds");
        return $base->format('Y-m-d H:i:s');
    }

    // Normalizar separadores y espacios
    $s = preg_replace('/\s+/', ' ', $s);
    $s = str_replace(['.', '-'], ['/', '/'], $s);

    // Formatos usuales
    $fmts = [
        'd/m/Y H:i:s',
        'd/m/Y H:i',
        'd/m/Y',
        'd/m/y H:i:s',
        'd/m/y H:i',
        'd/m/y',
        'Y/m/d H:i:s',
        'Y/m/d H:i',
        'Y/m/d',
        'Y-m-d H:i:s',
        'Y-m-d H:i',
        'Y-m-d',
    ];
    foreach ($fmts as $fmt) {
        $dt = DateTime::createFromFormat($fmt, $s, wp_timezone());
        if ($dt instanceof DateTime) {
            if (strpos($fmt, 'H:i') === false) {
                $dt->setTime(0, 0, 0);
            } elseif (strpos($fmt, 'H:i:s') === false) {
                $dt->setTime((int)$dt->format('H'), (int)$dt->format('i'), 0);
            }
            return $dt->format('Y-m-d H:i:s');
        }
    }

    $ts = strtotime($s);
    if ($ts !== false) return date('Y-m-d H:i:s', $ts);

    return trim((string)$value);
}

/* ======== NEW: Venue matching helpers ======== */

/**
 * Carga y normaliza todos los Venues publicados una sola vez.
 * @return array  [ [id, title, norm], ... ]
 */
function hnh_au_prepare_venue_index()
{
    $posts = get_posts([
        'post_type'      => 'venue',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ]);

    $index = [];
    foreach ($posts as $pid) {
        $t = get_the_title($pid);
        $index[] = [
            'id'    => (int)$pid,
            'title' => $t,
            'norm'  => hnh_au_norm($t),
        ];
    }
    return $index;
}

/**
 * Dado el título del Auction, intenta adivinar el Venue id.
 * Usa coincidencia por substring y similar_text con umbral del 55%.
 */
function hnh_au_guess_venue_id(string $auction_title, array $venue_index): int
{
    if (!$auction_title || empty($venue_index)) return 0;

    // parte izquierda antes de " | " como pista principal
    $parts       = explode('|', $auction_title);
    $left_hint   = trim($parts[0] ?? $auction_title);

    $norm_full   = hnh_au_norm($auction_title);
    $norm_left   = hnh_au_norm($left_hint);

    $best_id = 0;
    $best_score = 0;

    foreach ($venue_index as $v) {
        $vn = $v['norm'];

        // match fuerte: el nombre del venue está contenido en el del auction
        if ($vn && (strpos($norm_full, $vn) !== false || strpos($norm_left, $vn) !== false)) {
            return (int)$v['id']; // substring = 100%
        }

        // similitud blanda
        $p1 = 0;
        $p2 = 0;
        similar_text($vn, $norm_full, $p1);
        similar_text($vn, $norm_left, $p2);
        $score = max($p1, $p2);

        if ($score > $best_score) {
            $best_score = $score;
            $best_id = (int)$v['id'];
        }
    }

    // umbral de confianza
    return ($best_score >= 55) ? $best_id : 0;
}

/**
 * Normaliza cadenas para comparar: minúsculas, sin signos, quita palabras comunes.
 */
function hnh_au_norm(string $s): string
{
    // quitar "the " inicial solo para comparación
    $s = preg_replace('~^\s*the\s+~i', '', $s);

    $s = mb_strtolower($s, 'UTF-8');
    // dejar solo letras y números como separadores simples
    $s = preg_replace('~[^\p{L}\p{Nd}]+~u', ' ', $s);

    // palabras comunes que no aportan al match
    $stop = ['the', 'sale', 'auction', 'museum', 'hall'];
    $tokens = array_values(array_filter(explode(' ', $s)));
    $tokens = array_diff($tokens, $stop);

    return trim(implode(' ', $tokens));
}
/* ======== /NEW ======== */

// ================== LECTOR XLSX ==================
function hnh_au_include_simplexlsx()
{
    if (class_exists('HNH_SimpleXLSX_AU')) return;
    class HNH_SimpleXLSX_AU
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
                self::$error = 'Could not open ZIP';
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
                    $val = '';
                    if ($t === 's') {
                        $val = $shared[(int)$v] ?? '';
                    } elseif ($t === 'inlineStr' && isset($c->is->t)) {
                        $val = (string)$c->is->t;
                    } else {
                        $val = $v; // número/fecha o texto plano
                    }
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
