<?php
// ============ SETTINGS (fácil de cambiar si lo necesitas) =============
const HNH_IMPORT_POST_TYPE   = 'vehicles';
const HNH_TAX_CATEGORY       = 'vehicle_category'; // ya no se usa en el import, pero lo dejo por si acaso
const HNH_TAX_BRAND          = 'vehicle_brand';    // taxonomía con las "Makes"
const HNH_MENU_PARENT        = 'edit.php?post_type=vehicles'; // Colgar el import del menú "Vehicles"

// CPT que guarda a los miembros del equipo (para el Post Object "assigned_to" / "contact_rep")
const HNH_TEAM_POST_TYPE     = 'team';

// *** MODO ESPECIAL: SOLO ACTUALIZAR FECHAS POR TÍTULO ***
const HNH_UPDATE_DATES_ONLY  = false; // ← pon true si quieres el modo solo fechas
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

    // ============== MODO IMPORT NORMAL (create / update por stock_number) =================

    // --- LOCALIZA TODAS LAS CABECERAS QUE NOS INTERESAN ---
    $col_title_main         = vehicles_find_header(['Title (main)'], $headers_raw);                      // obligatorio
    $col_description        = vehicles_find_header(['Description'], $headers_raw);                       // opcional
    $col_auction_latest     = vehicles_find_header(['Auction (latest)'], $headers_raw);                  // opcional
    $col_auction_date       = vehicles_find_header(['Auction date (latest)'], $headers_raw);             // opcional
    $col_auction_number     = vehicles_find_header(['Auction number (latest)'], $headers_raw);           // opcional
    $col_lot_number         = vehicles_find_header(['Lot number (latest)'], $headers_raw);               // opcional
    $col_status             = vehicles_find_header(['Status'], $headers_raw);                            // opcional
    $col_contact_rep        = vehicles_find_header(['Contact/Rep'], $headers_raw);                       // opcional
    $col_sold_price         = vehicles_find_header(['Sold Price', 'Sold price'], $headers_raw);          // opcional
    $col_artist_brand       = vehicles_find_header(['Artist/Maker/Brand'], $headers_raw);                // opcional
    $col_assigned_to        = vehicles_find_header(['Assigned to', 'Assigned To'], $headers_raw);        // opcional
    $col_category           = vehicles_find_header(['Category', 'Category (all levels)'], $headers_raw); // opcional
    $col_estimate_range     = vehicles_find_header(['Estimate (range)'], $headers_raw);                  // opcional
    $col_footnote           = vehicles_find_header(['Footnote'], $headers_raw);                          // opcional
    $col_stock_number       = vehicles_find_header(['Stock Number', 'Stock number'], $headers_raw);      // obligatorio
    $col_estimate_high      = vehicles_find_header(['Estimate (high)'], $headers_raw);                   // opcional
    $col_estimate_low       = vehicles_find_header(['Estimate (low)'], $headers_raw);                    // opcional
    $col_image_url          = vehicles_find_header(['Image URL (main image)', 'Image URL'], $headers_raw); // opcional
    $col_lot_link           = vehicles_find_header(['Lot Link', 'Lot link'], $headers_raw);              // opcional
    $col_title_sub          = vehicles_find_header(['Title (sub)'], $headers_raw);                       // opcional

    // --- CABECERAS OBLIGATORIAS ---
    $missing_required = [];
    if ($col_title_main === -1)   $missing_required[] = 'Title (main)';
    if ($col_stock_number === -1) $missing_required[] = 'Stock Number';

    if (!empty($missing_required)) {
        echo '<div class="notice notice-error"><p>'
            . 'Import aborted. Missing required header(s): <strong>'
            . esc_html(implode(', ', $missing_required))
            . '</strong>.'
            . '</p></div>';
        return;
    }

    $created               = 0;
    $updated               = 0;
    $skipped_empty         = 0;
    $skipped_no_stock      = 0;
    $skipped_no_title      = 0;
    $skipped_duplicate_row = 0;

    $seen_stocks_in_file = [];

    // caché para CPT team
    $team_cache = [];

    for ($i = 1; $i < count($rows); $i++) {
        $row = $rows[$i];

        // --- ¿fila completamente vacía? ---
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

        // --- TÍTULO (obligatorio) ---
        $title_value = wp_strip_all_tags(trim((string)$row[$col_title_main]));
        if ($title_value === '') {
            $skipped_no_title++;
            continue;
        }

        // --- STOCK NUMBER (obligatorio) ---
        $stock_value = strtoupper(trim((string)$row[$col_stock_number]));
        $stock_value = preg_replace('/\s+/', '', $stock_value);
        if ($stock_value === '') {
            $skipped_no_stock++;
            continue;
        }

        // Duplicado dentro del propio archivo (misma fila de stock repetida)
        if (isset($seen_stocks_in_file[$stock_value])) {
            $skipped_duplicate_row++;
            continue;
        }
        $seen_stocks_in_file[$stock_value] = true;

        // --- CONTENIDO (desde Description, si está) ---
        $raw_desc     = ($col_description !== -1) ? (string)$row[$col_description] : '';
        $post_content = vehicles_format_description($raw_desc);

        // ¿Ya existe un Vehicle con ese stock_number?
        $existing_id = vehicles_get_post_id_by_stock_number($stock_value);

        if ($existing_id) {
            // ==== UPDATE EXISTENTE ====
            $post_id = $existing_id;

            wp_update_post([
                'ID'           => $post_id,
                'post_title'   => $title_value,
                'post_content' => $post_content,
                // no toco el status para no revivir borrados/trash
            ]);

            $updated++;
        } else {
            // ==== CREATE NUEVO ====
            $post_id = wp_insert_post([
                'post_type'    => HNH_IMPORT_POST_TYPE,
                'post_status'  => 'publish',
                'post_title'   => $title_value,
                'post_content' => $post_content,
            ], true);

            if (is_wp_error($post_id)) {
                $skipped_empty++;
                continue;
            }

            $created++;
        }

        // ===== Extraer Registration No / Chassis No / MOT desde Description =====
        $desc_triplet = vehicles_extract_from_description($raw_desc);
        update_field('registration_no', $desc_triplet['registration_no'], $post_id);
        update_field('chassis_no',      $desc_triplet['chassis_no'],      $post_id);

        if (!empty($desc_triplet['mot'])) {
            $mot_val = vehicles_validate_mot($desc_triplet['mot']);
            if ($mot_val !== '') {
                update_field('mot', $mot_val, $post_id);
            }
        }
        // =====================================================================

        // --- ACF: Title (main) + Description ---
        update_field('title_main', $title_value, $post_id);
        if ($col_description !== -1) {
            // guardamos el HTML / texto EXACTO del Excel en el ACF
            update_field('description', (string)$row[$col_description], $post_id);
        }

        // --- CAMPOS ACF SEGÚN CABECERAS PRESENTES ---

        if ($col_auction_latest !== -1) {
            update_field('auction_latest', (string)$row[$col_auction_latest], $post_id);
        }

        if ($col_auction_date !== -1) {
            $date_raw  = (string)$row[$col_auction_date];
            $date_norm = vehicles_excel_serial_to_datetime($date_raw, 'Y-m-d H:i');
            if ($date_norm !== '') {
                update_field('auction_date_latest', $date_norm, $post_id);
            }
        }

        if ($col_auction_number !== -1) {
            update_field('auction_number_latest', (string)$row[$col_auction_number], $post_id);
        }

        if ($col_lot_number !== -1) {
            update_field('lot_number_latest', (string)$row[$col_lot_number], $post_id);
        }

        if ($col_status !== -1) {
            update_field('status', (string)$row[$col_status], $post_id);
        }

        // --- CONTACT REP → Post Object (CPT team) ---
        if ($col_contact_rep !== -1) {
            $contact_raw = trim((string)$row[$col_contact_rep]);
            if ($contact_raw !== '') {
                $team_id = vehicles_get_team_id_by_display($contact_raw, $team_cache);
                if ($team_id) {
                    update_field('contact_rep', $team_id, $post_id);
                }
            }
        }

        if ($col_sold_price !== -1) {
            update_field('sold_price', (string)$row[$col_sold_price], $post_id);
        }

        // --- ARTIST / MAKER / BRAND → TAXONOMY vehicle_brand + ACF (ID del término) ---
        if ($col_artist_brand !== -1) {
            $brand_raw = trim((string)$row[$col_artist_brand]);
            if ($brand_raw !== '') {
                $brand_term_id = vehicles_get_or_create_brand_term($brand_raw);
                if ($brand_term_id) {
                    // Guardar ID del término en el ACF
                    update_field('artist_maker_brand', (int)$brand_term_id, $post_id);

                    // Y asignarlo como taxonomy al vehicle
                    if (taxonomy_exists(HNH_TAX_BRAND)) {
                        wp_set_object_terms($post_id, [(int)$brand_term_id], HNH_TAX_BRAND, false);
                    }
                }
            }
        }

        if ($col_category !== -1) {
            update_field('category_all_levels', (string)$row[$col_category], $post_id);
        }

        if ($col_estimate_range !== -1) {
            update_field('estimate_range', (string)$row[$col_estimate_range], $post_id);
        }

        if ($col_footnote !== -1) {
            update_field('footnote', (string)$row[$col_footnote], $post_id);
        }

        if ($col_estimate_high !== -1) {
            update_field('estimate_high', (string)$row[$col_estimate_high], $post_id);
        }

        if ($col_estimate_low !== -1) {
            update_field('estimate_low', (string)$row[$col_estimate_low], $post_id);
        }

        if ($col_lot_link !== -1) {
            update_field('lot_link', (string)$row[$col_lot_link], $post_id);
        }

        if ($col_title_sub !== -1) {
            update_field('title_sub', (string)$row[$col_title_sub], $post_id);
        }

        // Siempre guardamos el stock_number porque es obligatorio
        update_field('stock_number', $stock_value, $post_id);

        // --- ASSIGNED TO → Post Object (CPT team) ---
        if ($col_assigned_to !== -1) {
            $assigned_raw = trim((string)$row[$col_assigned_to]);
            if ($assigned_raw !== '') {
                $team_id = vehicles_get_team_id_by_display($assigned_raw, $team_cache);
                if ($team_id) {
                    update_field('assigned_to', $team_id, $post_id);
                }
            }
        }

        // --- IMAGEN DESTACADA DESDE URL ---
        if ($col_image_url !== -1) {
            $img_url = trim((string)$row[$col_image_url]);
            if ($img_url !== '') {
                vehicles_set_featured_image_from_url($post_id, $img_url);
                // además guardamos la URL en el ACF
                update_field('image_url_main_image', $img_url, $post_id);
            }
        }
    }

    echo '<div class="notice notice-success"><p>'
        . 'Import completed. '
        . 'Created: <strong>' . intval($created) . '</strong> '
        . '| Updated (same Stock Number): <strong>' . intval($updated) . '</strong> '
        . '| Skipped (duplicate rows in file): ' . intval($skipped_duplicate_row) . ' '
        . '| Skipped (empty rows/errors): ' . intval($skipped_empty) . ' '
        . '| Skipped (no stock number): ' . intval($skipped_no_stock) . ' '
        . '| Skipped (no title): ' . intval($skipped_no_title)
        . '</p></div>';
}

/**
 * Devuelve el ID de un Vehicle por stock_number (cualquier estado). 0 si no existe.
 */
function vehicles_get_post_id_by_stock_number($stock)
{
    if ($stock === '') return 0;

    $q = new WP_Query([
        'post_type'      => HNH_IMPORT_POST_TYPE,
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => [
            [
                'key'   => 'stock_number',
                'value' => $stock,
            ],
        ],
        'no_found_rows'  => true,
    ]);

    if (!empty($q->posts)) {
        return (int)$q->posts[0];
    }
    return 0;
}

/**
 * Valida/normaliza el valor de MOT.
 * Acepta:
 *  - "Exempt" (insensible a mayúsculas) -> "Exempt"
 *  - "Mes Año" (full o abreviado: Jan/January, Sep/Sept/September) -> "Month YYYY"
 * Si no cumple, retorna ''.
 */
function vehicles_validate_mot($raw)
{
    $s = trim((string)$raw);
    if ($s === '') return '';

    // 1) Exempt
    if (preg_match('~^exempt$~i', $s)) {
        return 'Exempt';
    }

    // 2) Month + Year (admite abreviaturas)
    // Map de abrevs -> mes completo
    $month_map = [
        'jan' => 'January',
        'january'   => 'January',
        'feb' => 'February',
        'february'  => 'February',
        'mar' => 'March',
        'march'     => 'March',
        'apr' => 'April',
        'april'     => 'April',
        'may' => 'May',
        'jun' => 'June',
        'june'      => 'June',
        'jul' => 'July',
        'july'      => 'July',
        'aug' => 'August',
        'august'    => 'August',
        'sep' => 'September',
        'sept'      => 'September',
        'september' => 'September',
        'oct' => 'October',
        'october'   => 'October',
        'nov' => 'November',
        'november'  => 'November',
        'dec' => 'December',
        'december'  => 'December',
    ];

    // mes (abreviado o completo) + espacios + año de 4 dígitos
    if (preg_match('~^\s*([A-Za-z]{3,9})\s+(\d{4})\s*$~', $s, $m)) {
        $mon_key = strtolower($m[1]);
        $year    = (int) $m[2];

        // Rango razonable de año (ajústalo si quieres)
        if ($year < 1950 || $year > 2100) return '';

        if (isset($month_map[$mon_key])) {
            return $month_map[$mon_key] . ' ' . $year; // normaliza a mes completo
        }
    }

    // Si no coincide con ninguno, no guardes nada
    return '';
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

        $title    = wp_strip_all_tags(trim((string)$row[$title_col]));
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
                    'title'          => $title,
                    'post_status'    => 'any',
                    'posts_per_page' => 1,
                    'fields'         => 'ids',
                    's'              => $title,
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
 * - Soporta / - . . 
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

// CamelCase para términos (ya casi no lo usamos, pero lo dejo por si acaso)
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
 * Crea o devuelve un término en la taxonomía de marcas (vehicle_brand),
 * usando el nombre EXACTO que viene del Excel.
 * Devuelve 0 si algo falla.
 */
function vehicles_get_or_create_brand_term($label)
{
    $name = trim((string)$label);
    if ($name === '' || !taxonomy_exists(HNH_TAX_BRAND)) return 0;

    static $cache = [];

    $key = strtolower($name);
    if (isset($cache[$key])) {
        return (int)$cache[$key];
    }

    // 1) Buscar por nombre
    $term = get_term_by('name', $name, HNH_TAX_BRAND);
    if ($term && !is_wp_error($term)) {
        $cache[$key] = (int)$term->term_id;
        return (int)$term->term_id;
    }

    // 2) Crear nuevo término
    $args = [
        'slug' => sanitize_title($name),
    ];
    $res = wp_insert_term($name, HNH_TAX_BRAND, $args);
    if (!is_wp_error($res) && !empty($res['term_id'])) {
        $term_id      = (int)$res['term_id'];
        $cache[$key]  = $term_id;
        return $term_id;
    }

    // 3) Fallback: quizá el slug ya existía
    $term = get_term_by('slug', sanitize_title($name), HNH_TAX_BRAND);
    if ($term && !is_wp_error($term)) {
        $cache[$key] = (int)$term->term_id;
        return (int)$term->term_id;
    }

    return 0;
}

/**
 * XLSX reader (preserves gaps)
 */
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
                    if ($t === 's') {
                        $val = $shared[(int)$v] ?? '';
                    } elseif ($t === 'inlineStr' && isset($c->is->t)) {
                        $val = (string)$c->is->t;
                    } else {
                        $val = $v;
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
    ];

    foreach ($patterns as $key => $regex) {
        if (preg_match($regex, $text, $m)) {
            $val = trim($m[1]);
            $val = preg_split('~\s*(?:Registration\s*(?:No\.?|Number)?|Chassis\s*(?:No\.?|Number)?|MOT(?:\s*Expiry(?:\s*Date)?)?)\s*:~i', $val, 2)[0];
            $out[$key] = trim($val, " \t\n\r\0\x0B\xC2\xA0");
        }
    }

    // --- MOT: prioriza Expiry Date -> Expiry -> MOT
    $mot_val = '';
    if (preg_match('~\bMOT\s*Expiry\s*Date\s*:\s*([^\r\n]+)~i', $text, $m1)) {
        $mot_val = trim($m1[1]);
    } elseif (preg_match('~\bMOT\s*Expiry\s*:\s*([^\r\n]+)~i', $text, $m2)) {
        $mot_val = trim($m2[1]);
    } elseif (preg_match('~\bMOT\s*:\s*([^\r\n]+)~i', $text, $m3)) {
        $mot_val = trim($m3[1]);
    }

    if ($mot_val !== '') {
        $mot_val = preg_split('~\s*(?:Registration\s*(?:No\.?|Number)?|Chassis\s*(?:No\.?|Number)?|MOT(?:\s*Expiry(?:\s*Date)?)?)\s*:~i', $mot_val, 2)[0];
        $mot_val = trim($mot_val, " \t\n\r\0\x0B\xC2\xA0");

        // valida/normaliza (Exempt o Month YYYY)
        if (function_exists('vehicles_validate_mot')) {
            $mot_val = vehicles_validate_mot($mot_val);
        } else {
            $mot_val = preg_match('~^exempt$~i', $mot_val) ? 'Exempt' : '';
        }
        $out['mot'] = $mot_val;
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

/**
 * Featured image desde URL
 */
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