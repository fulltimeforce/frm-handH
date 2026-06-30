<?php

const HNH_MODEL_POST_TYPE = 'model'; // CPT Models (para el Post Object model_vehicle)

// ============ SETTINGS (fácil de cambiar si lo necesitas) =============
const HNH_IMPORT_POST_TYPE   = 'vehicles';
const HNH_TAX_CATEGORY       = 'vehicle_category'; // ya no se usa en el import, pero lo dejo por si acaso
const HNH_TAX_BRAND          = 'vehicle_brand';    // taxonomía con las "Makes"
const HNH_MAKE_POST_TYPE     = 'make';
const HNH_MENU_PARENT        = 'edit.php?post_type=vehicles'; // Colgar el import del menú "Vehicles"

// OpenAI
const HNH_OPENAI_API_KEY = 'OPEN_AI_API'; // mejor si lo pones en wp-config.php
const HNH_OPENAI_MODEL   = 'gpt-4.1-mini';        // buen balance costo/calidad
const HNH_OPENAI_ENABLE  = true;                 // apaga/enciende rápido

// CPT que guarda a los miembros del equipo (para el Post Object "assigned_to" / "contact_rep")
const HNH_TEAM_POST_TYPE     = 'team';

// *** MODO ESPECIAL: SOLO ACTUALIZAR FECHAS POR TÍTULO ***
const HNH_UPDATE_DATES_ONLY  = false; // ← pon true si quieres el modo solo fechas
// ======================================================================

const HNH_AI_PROMPT_VERSION = 'v3';

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
    <style>
        .hnh-import-loader {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .35);
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hnh-import-loader__box {
            background: #fff;
            border-radius: 10px;
            padding: 18px 22px;
            min-width: 260px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .2);
        }
    </style>
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
        <div id="hnh-import-loader" class="hnh-import-loader" style="display:none;">
            <div class="hnh-import-loader__box">
                <span class="spinner is-active" style="float:none; margin:0 0 12px 0;"></span>
                <div style="font-size:14px; font-weight:600;">Importing vehicles…</div>
                <div style="font-size:12px; opacity:.8; margin-top:6px;">Please don’t close this tab.</div>
            </div>
        </div>
        <?php
        if (!empty($_FILES['vehicles_file']) && isset($_POST['vehicles_import_nonce_f']) && wp_verify_nonce($_POST['vehicles_import_nonce_f'], 'vehicles_import_nonce')) {
            vehicles_handle_import($_FILES['vehicles_file']);
        }
        ?>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.wrap form[enctype="multipart/form-data"]');
            const loader = document.getElementById('hnh-import-loader');
            if (!form || !loader) return;

            form.addEventListener('submit', function() {
                loader.style.display = 'flex';

                // bloquea el botón para evitar doble submit
                const btn = form.querySelector('button.button-primary');
                if (btn) {
                    btn.disabled = true;
                    btn.textContent = 'Importing...';
                }
            });
        });
    </script>
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

    // caché para users
    $user_cache = [];

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
            $post_id = (int) $existing_id;

            wp_update_post([
                'ID'           => $post_id,
                'post_title'   => $title_value,
                'post_content' => $post_content,
                // no toco el status para no revivir borrados/trash
            ], true);

            $updated++;
        } else {
            // ==== CREATE NUEVO ====
            $post_id = wp_insert_post([
                'post_type'    => HNH_IMPORT_POST_TYPE,
                'post_status'  => 'publish',
                'post_title'   => $title_value,
                'post_content' => $post_content,
            ], true);

            if (is_wp_error($post_id) || !$post_id) {
                $skipped_empty++;
                continue;
            }

            $post_id = (int) $post_id;
            $created++;
        }

        // ===== Extraer Registration No / Chassis No / MOT desde Description =====
        $desc_for_ai  = ($col_description !== -1) ? (string)$row[$col_description] : '';
        $source_text  = trim($title_value . "\n\n" . $desc_for_ai);
        $fields = vehicles_extract_fields_ai($source_text);

        $model_variant = vehicles_extract_model_and_variant_from_title($title_value);

        if (empty($fields['model_name'])) {
            $fields['model_name'] = $model_variant['model'];
        }

        if (empty($fields['variant_name'])) {
            $fields['variant_name'] = $model_variant['variant'];
        }

        if (empty($fields['make_name'])) {
            $fields['make_name'] = vehicles_extract_make_from_title($source_text);
        }

        // registration / chassis
        hnh_update_acf($post_id, 'registration_no', $fields['registration_no']);
        hnh_update_acf($post_id, 'chassis_no', $fields['chassis_no']);

        // NEW: vrn/vin mirrors
        hnh_update_acf($post_id, 'vrn', $fields['registration_no']);
        hnh_update_acf($post_id, 'vin', $fields['chassis_no']);

        // MOT (con tu validador)
        if (!empty($fields['mot'])) {
            $mot_val = vehicles_validate_mot($fields['mot']);
            if ($mot_val !== '') {
                hnh_update_acf($post_id, 'mot', $mot_val);
            }
        }

        // NEW: Year (solo si es válido)
        if (!empty($fields['year'])) {
            hnh_update_acf($post_id, 'year_vehicle', (string)$fields['year']);
        } else {
            // opcional: si quieres limpiar cuando no haya año
            // update_field('year_vehicle', '', $post_id);
        }

        // NEW: Model (Post Object -> ID)
        if (!empty($fields['model_name'])) {
            $model_id = vehicles_get_or_create_model_post($fields['model_name']);
            if ($model_id) {
                hnh_update_acf($post_id, 'model_vehicle', (int)$model_id);
            }
        } else {
            // opcional: limpiar si no se detectó modelo
            // update_field('model_vehicle', null, $post_id);
        }

        // NEW: Variant (texto simple)
        if (!empty($fields['variant_name'])) {
            hnh_update_acf($post_id, 'variant_name', (string)$fields['variant_name']);
        } else {
            // opcional: limpiar si no hay variant
            // update_field('variant_vehicle', '', $post_id);
        }

        // =====================================================================

        // --- ACF: Title (main) + Description ---
        update_field('title_main', $title_value, $post_id);
        if ($col_description !== -1) {
            // guardamos el contenido formateado con la función
            $desc_html = (string)$row[$col_description];
            $desc_html = vehicles_convert_registration_block_to_ul($desc_html);
            update_field('description', $desc_html, $post_id);
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
            $sale_number_raw = trim((string)$row[$col_auction_number]);

            if ($sale_number_raw !== '') {
                $auction_id = vehicles_get_auction_id_by_sale_number($sale_number_raw);

                if ($auction_id) {
                    // ACF Post Object → guardar ID
                    hnh_update_acf($post_id, 'auction_number_latest', (int)$auction_id);
                } else {
                    update_field('auction_number_latest', null, $post_id);
                }
            }
        }

        if ($col_lot_number !== -1) {
            update_field('lot_number_latest', (string)$row[$col_lot_number], $post_id);
        }

        if ($col_status !== -1) {
            update_field('status', (string)$row[$col_status], $post_id);
        }

        // --- CONTACT REP → USER (WP Users) ---
        if ($col_contact_rep !== -1) {
            $contact_raw = trim((string)$row[$col_contact_rep]);
            if ($contact_raw !== '') {
                $user_id = vehicles_get_user_id_by_display($contact_raw, $user_cache);
                if ($user_id) {
                    update_field('contact_rep', (int)$user_id, $post_id);
                }
            }
        }

        if ($col_sold_price !== -1) {
            update_field('sold_price', (string)$row[$col_sold_price], $post_id);
        }

        // --- ARTIST / MAKER / BRAND → CPT "make" + ACF (Post Object ID) ---
        if ($col_artist_brand !== -1) {
            $make_raw = trim((string)$row[$col_artist_brand]);
            if ($make_raw !== '') {
                $make_id = vehicles_get_or_create_make_post($make_raw);
                if ($make_id) {
                    // ACF debe ser Post Object / Relationship que guarde el ID del post "make"
                    update_field('artist_maker_brand', (int)$make_id, $post_id);

                    // (Opcional recomendado) guarda también como meta simple para queries rápidas
                    update_post_meta($post_id, 'make_id', (int)$make_id);
                }
            }
        }

        // NEW: Make (Post Object -> ID) desde IA/título, SOLO si Excel no trae Artist/Maker/Brand
        $excel_has_make = ($col_artist_brand !== -1) && (trim((string)$row[$col_artist_brand]) !== '');

        if (!$excel_has_make && !empty($fields['make_name'])) {
            $make_id = vehicles_get_or_create_make_post($fields['make_name']);
            if ($make_id) {
                hnh_update_acf($post_id, 'artist_maker_brand', (int)$make_id);
                update_post_meta($post_id, 'make_id', (int)$make_id); // opcional
            }
        }

        // --- CATEGORY (all levels) → TAXONOMY vehicle_category + ACF ---
        if ($col_category !== -1) {
            $cat_raw = trim((string)$row[$col_category]);

            // Guardas el texto en ACF como ya lo hacías
            update_field('category_all_levels', $cat_raw, $post_id);

            // Y además lo asignas como taxonomy (para que salga “marcado”)
            if ($cat_raw !== '' && taxonomy_exists(HNH_TAX_CATEGORY)) {
                $cat_term_id = vehicles_get_or_create_category_term($cat_raw);

                if ($cat_term_id) {
                    wp_set_object_terms($post_id, [(int)$cat_term_id], HNH_TAX_CATEGORY, false);
                }
            }
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
            $lot_link = hnh_normalize_lot_link((string)$row[$col_lot_link]);
            update_field('lot_link', $lot_link, $post_id);
        }

        if ($col_title_sub !== -1) {
            update_field('title_sub', (string)$row[$col_title_sub], $post_id);
        }

        // Siempre guardamos el stock_number porque es obligatorio
        update_field('stock_number', $stock_value, $post_id);

        // --- ASSIGNED TO → USER (WP Users) ---
        if ($col_assigned_to !== -1) {
            $assigned_raw = trim((string)$row[$col_assigned_to]);
            if ($assigned_raw !== '') {
                $user_id = vehicles_get_user_id_by_display($assigned_raw, $user_cache);
                if ($user_id) {
                    update_field('assigned_to', (int)$user_id, $post_id);
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

        clean_post_cache($post_id);
        wp_cache_delete($post_id, 'post_meta');
        update_postmeta_cache([$post_id]);

        // Opcional: si ACF tiene cache de valores en tu versión
        if (function_exists('acf_flush_value_cache')) {
            acf_flush_value_cache($post_id);
        }

        // Sync wp_vehicles_search tras guardar todos los campos ACF (save_post corre antes en wp_insert_post)
        if (class_exists('Vehicles_Search_Sync')) {
            Vehicles_Search_Sync::force_sync($post_id, 'import');
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

function hnh_normalize_lot_link($url)
{
    $url = trim((string)$url);

    if ($url === '') {
        return '';
    }

    $url = preg_replace(
        '#https?://www\.handh\.co\.uk/#i',
        'https://auctions.handh.co.uk/',
        $url
    );

    // Quita dobles barras, pero no toca https://
    $url = preg_replace('#(?<!:)/{2,}#', '/', $url);

    return $url;
}

/**
 * Devuelve el ID del CPT "auction" cuyo ACF sale_number coincide.
 * Retorna 0 si no encuentra.
 */
function vehicles_get_auction_id_by_sale_number(string $sale_number): int
{
    $sale_number = trim($sale_number);
    if ($sale_number === '') return 0;

    static $cache = [];

    if (isset($cache[$sale_number])) {
        return (int)$cache[$sale_number];
    }

    $q = new WP_Query([
        'post_type'      => 'auction',
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => [
            [
                'key'   => 'sale_number', // ACF field name
                'value' => $sale_number,
                'compare' => '=',
            ],
        ],
        'no_found_rows'  => true,
    ]);

    if (!empty($q->posts)) {
        return $cache[$sale_number] = (int)$q->posts[0];
    }

    return $cache[$sale_number] = 0;
}

function vehicles_extract_make_from_title(string $source_text): string
{
    // Intenta usar la PRIMERA línea como título (porque tú armaste "title \n\n desc")
    $lines = preg_split("~\R~u", trim($source_text));
    $title = trim((string)($lines[0] ?? ''));
    if ($title === '') return '';

    // Quita año inicial
    $t = preg_replace('~^\s*(18|19|20)\d{2}\s+~', '', $title);
    $t = trim($t);
    if ($t === '') return '';

    // Makes de 2 palabras (ajusta a tu data)
    $two_word_makes = [
        'aston martin',
        'alfa romeo',
        'land rover',
        'rolls royce',
        'mercedes benz',
        'mercedes-benz',
        'mini cooper',
    ];

    $lower = strtolower($t);
    foreach ($two_word_makes as $mk) {
        if (strpos($lower, $mk . ' ') === 0) {
            return ucwords($mk); // "Aston Martin"
        }
    }

    // Fallback: primera palabra
    if (preg_match('~^([^\s]+)~', $t, $m)) {
        return trim($m[1]);
    }

    return '';
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
 * Crea o devuelve un post en el CPT "make" usando el nombre EXACTO.
 * Devuelve 0 si falla.
 */
function vehicles_get_or_create_make_post($label)
{
    $name = trim((string)$label);
    if ($name === '') return 0;

    $pt = defined('HNH_MAKE_POST_TYPE') ? HNH_MAKE_POST_TYPE : 'make';

    static $cache = [];
    $key = strtolower($name);
    if (isset($cache[$key])) return (int)$cache[$key];

    // 1) Buscar por título exacto (case-insensitive) dentro del CPT make
    $existing = get_page_by_title($name, OBJECT, $pt);
    if ($existing && !is_wp_error($existing)) {
        return $cache[$key] = (int)$existing->ID;
    }

    // 2) Buscar por slug
    $slug = sanitize_title($name);
    $q = new WP_Query([
        'post_type'      => $pt,
        'name'           => $slug,
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ]);
    if (!empty($q->posts)) {
        return $cache[$key] = (int)$q->posts[0];
    }

    // 3) Crear el make
    $new_id = wp_insert_post([
        'post_type'   => $pt,
        'post_status' => 'publish',
        'post_title'  => $name,
        'post_name'   => $slug,
    ], true);

    if (is_wp_error($new_id) || !$new_id) {
        return $cache[$key] = 0;
    }

    return $cache[$key] = (int)$new_id;
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
 * Extrae: registration_no, chassis_no, mot, year, model_name
 * - Primero intenta regex (barato) para year + los campos clásicos.
 * - Si IA está activa, la usa como mejora/fallback para year/model.
 */
function vehicles_extract_fields_ai(string $source_text): array
{
    $source_text = trim($source_text);

    // Fallback “barato”: usa tu extractor actual (reg/chassis/mot) sobre el texto
    $fallback_triplet = vehicles_extract_from_description($source_text);

    // Año por regex primero
    $year = vehicles_extract_year_from_text($source_text);

    $make_guess = vehicles_extract_make_from_title($source_text);

    $fallback = [
        'registration_no' => $fallback_triplet['registration_no'] ?? '',
        'chassis_no'      => $fallback_triplet['chassis_no'] ?? '',
        'mot'             => $fallback_triplet['mot'] ?? '',
        'year'            => $year,        // puede ser '' si no hay
        'model_name'      => '',           // por regex no lo sacamos
        'variant_name'    => '',
        'make_name'          => $make_guess
    ];

    // Si IA apagada o sin API key -> devuelve fallback
    if (!defined('HNH_OPENAI_ENABLE') || !HNH_OPENAI_ENABLE) return $fallback;
    $apiKey = defined('HNH_OPENAI_API_KEY') ? HNH_OPENAI_API_KEY : '';
    if (!$apiKey) return $fallback;
    if ($source_text === '') return $fallback;

    // Cache por hash (incluye year/model)
    $hash = md5(HNH_AI_PROMPT_VERSION . '|' . $source_text);
    $cache_key = 'veh_ai_fields_' . $hash;

    $cached = get_transient($cache_key);
    if (
        is_array($cached)
        && array_key_exists('registration_no', $cached)
        && array_key_exists('chassis_no', $cached)
        && array_key_exists('mot', $cached)
        && array_key_exists('year', $cached)
        && array_key_exists('model_name', $cached)
        && array_key_exists('variant_name', $cached)
        && array_key_exists('make_name', $cached)
    ) {
        // valida year por si acaso
        $cached['year'] = vehicles_validate_year($cached['year']);
        return $cached;
    }

    $result = vehicles_openai_extract_fields($source_text, $apiKey);
    if (!is_array($result)) return $fallback;

    $out = [
        'registration_no' => trim((string)($result['registration_no'] ?? '')),
        'chassis_no'      => trim((string)($result['chassis_no'] ?? '')),
        'mot'             => trim((string)($result['mot'] ?? '')),
        'year'            => trim((string)($result['year'] ?? '')),
        'model_name'      => trim((string)($result['model_name'] ?? '')),
        'variant_name'    => trim((string)($result['variant_name'] ?? '')),
        'make_name'       => trim((string)($result['make_name'] ?? '')),
    ];

    // Si IA no devolvió nada útil, fallback
    $all_empty = ($out['registration_no'] === '' && $out['chassis_no'] === '' && $out['mot'] === '' && $out['year'] === '' && $out['model_name'] === '' && $out['variant_name'] === '' && $out['make_name'] === '');
    if ($all_empty) return $fallback;

    // Normalizaciones/validaciones
    $out['year'] = vehicles_validate_year($out['year']); // '' si no es válido

    // Si regex ya encontró año y la IA devolvió otro inválido o vacío, conserva el de regex
    if ($fallback['year'] !== '' && $out['year'] === '') {
        $out['year'] = $fallback['year'];
    }

    // Si IA no encuentra reg/chassis/mot, conserva fallback
    if ($out['registration_no'] === '') $out['registration_no'] = $fallback['registration_no'];
    if ($out['chassis_no'] === '')      $out['chassis_no']      = $fallback['chassis_no'];
    if ($out['mot'] === '')             $out['mot']             = $fallback['mot'];
    if ($out['make_name'] === '')         $out['make_name']         = $fallback['make_name'];
    if ($out['variant_name'] === '') $out['variant_name'] = $fallback['variant_name'];

    set_transient($cache_key, $out, 30 * DAY_IN_SECONDS);

    return $out;
}

function vehicles_extract_year_from_text(string $text): string
{
    $text = trim($text);
    if ($text === '') return '';

    // Busca años de 4 dígitos (evita 192 / 202 / 3)
    if (preg_match_all('~\b(18\d{2}|19\d{2}|20\d{2}|2100)\b~', $text, $m)) {
        // Si hay varios, normalmente el primero del título/desc suele ser el año del vehículo.
        foreach ($m[1] as $candidate) {
            $valid = vehicles_validate_year($candidate);
            if ($valid !== '') return $valid;
        }
    }
    return '';
}

function vehicles_validate_year($raw): string
{
    $s = trim((string)$raw);
    if ($s === '') return '';

    // solo 4 dígitos
    if (!preg_match('~^\d{4}$~', $s)) return '';

    $y = (int)$s;

    // rango razonable de “año de vehículo”
    // (primer coche ~1886; ajusta si quieres)
    if ($y < 1800 || $y > 2100) return '';

    return (string)$y;
}

function vehicles_openai_extract_fields(string $sourceText, string $apiKey): ?array
{
    $model = defined('HNH_OPENAI_MODEL') ? HNH_OPENAI_MODEL : 'gpt-4.1-mini';

    $prompt = "Extract these fields from the vehicle TEXT. Return ONLY valid JSON with keys:
registration_no, chassis_no, mot, year, make_name, model_name, variant_name.

IMPORTANT CONTEXT:
The input TEXT contains the vehicle Title first, followed by the Description (HTML).

Rules:
- If a field is not present, return an empty string.
- registration_no: value after labels like 'Registration No', 'Registration Number', 'VRN'.
- chassis_no: value after labels like 'Chassis No', 'Chassis Number', OR 'Frame No', OR 'VIN'. If multiple identifiers exist, prefer the one that represents the chassis identifier.
- mot: return either 'Exempt' or 'Month YYYY' (e.g., 'September 2027') if possible; otherwise return an empty string.
- year: must be a 4-digit year such as 1954, 2007, or 2019. Reject numbers with 1–3 digits (e.g., 192, 202, 3) and return an empty string if not a real year.

- make_name:
  - PRIMARY SOURCE: the vehicle Title.
  - The Title usually follows the structure: \"{YEAR} {MAKE} {MODEL}\".
  - To extract the make_name from the title:
    1) Remove the leading 4-digit year.
    2) The next word(s) represent the make/brand.
  - IMPORTANT: some makes are 2 words (examples: \"Aston Martin\", \"Alfa Romeo\", \"Land Rover\", \"Rolls Royce\", \"Mercedes Benz\", \"Mercedes-Benz\").
  - If the make is clearly present in the Description as a brand/make, you may use it as SECONDARY SOURCE.
  - Do NOT guess or invent a make. If unclear, return empty string.

- model_name:
  - PRIMARY SOURCE: the vehicle Title.
  - The Title usually follows the structure: \"{YEAR} {MAKE} {MODEL} {VARIANT}\".

  STEPS TO DETERMINE model_name:
  1) Remove the leading 4-digit year.
  2) Remove the make/brand (which may consist of one or two words).
  3) From the remaining text, identify ONLY the core model designation.

  The model designation is usually a short identifier such as:
  \"DB6\", \"XK120\", \"VBB1\", \"Li150\", \"450\", \"911\", \"E-Type\", or a short numeric combination like \"Speed 20\".

  IMPORTANT RULES:
  - model_name MUST be the shortest model identifier possible.
  - model_name MUST NOT include trim, series, coachbuilder, or body style.
  - Words such as \"Series\", \"Tourer\", \"Spider\", \"Coupe\", \"GT\", \"Scrambler\", \"Vanden\", \"Plas\", etc. are NOT part of the model and belong to the variant.
  - model_name should normally be 1–2 tokens (e.g. \"450\", \"DB6\", \"Speed 20\", \"Li150\").

  Examples:
  \"1965 Lambretta Li150 Series 3\" → model_name = \"Li150\"
  \"1934 Alvis Speed 20 SC Vanden Plas Tourer\" → model_name = \"Speed 20\"
  \"1970 Ducati 450 Scrambler\" → model_name = \"450\"

- variant_name:
  - PRIMARY SOURCE: the vehicle Title.

  STEPS TO DETERMINE variant_name:
  1) Extract the model_name first.
  2) Remove the year, make, and model_name from the Title.
  3) The remaining words are the variant_name.

  Examples:
  \"1965 Lambretta Li150 Series 3\"
  → model_name = \"Li150\"
  → variant_name = \"Series 3\"

  \"1934 Alvis Speed 20 SC Vanden Plas Tourer\"
  → model_name = \"Speed 20\"
  → variant_name = \"SC Vanden Plas Tourer\"

  \"1970 Ducati 450 Scrambler\"
  → model_name = \"450\"
  → variant_name = \"Scrambler\"

  If nothing remains after removing the model_name, return an empty string.

  IMPORTANT:
  - variant_name MUST NOT contain the model_name.
  - variant_name MUST NOT repeat the model_name.
  - If the remaining text equals model_name, return an empty string.

- Return JSON ONLY. No extra keys, no markdown, no explanation.

TEXT:
" . $sourceText;

    $body = [
        'model' => $model,
        'input' => $prompt,
    ];

    $res = wp_remote_post('https://api.openai.com/v1/responses', [
        'timeout' => 60,
        'headers' => [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ],
        'body' => wp_json_encode($body),
    ]);

    if (is_wp_error($res)) return null;

    $code = wp_remote_retrieve_response_code($res);
    $raw  = wp_remote_retrieve_body($res);
    if ($code < 200 || $code >= 300 || !$raw) return null;

    $json = json_decode($raw, true);
    if (!is_array($json)) return null;

    $text = '';
    if (!empty($json['output_text']) && is_string($json['output_text'])) {
        $text = $json['output_text'];
    } elseif (!empty($json['output']) && is_array($json['output'])) {
        foreach ($json['output'] as $item) {
            if (!empty($item['content']) && is_array($item['content'])) {
                foreach ($item['content'] as $c) {
                    if (($c['type'] ?? '') === 'output_text' && !empty($c['text'])) {
                        $text .= $c['text'];
                    }
                }
            }
        }
    }

    $text = trim($text);
    if ($text === '') return null;

    error_log('[VEHICLES AI RAW TEXT] ' . $text);

    $text = vehicles_extract_json_object($text);
    if ($text === '') return null;

    $data = json_decode($text, true);
    if (!is_array($data)) return null;

    return $data;
}

function vehicles_get_or_create_model_post(string $label): int
{
    $name = trim($label);
    if ($name === '') return 0;

    $pt = defined('HNH_MODEL_POST_TYPE') ? HNH_MODEL_POST_TYPE : 'model';

    static $cache = [];
    $key = strtolower($name);
    if (isset($cache[$key])) return (int)$cache[$key];

    // 1) Buscar por título exacto
    $existing = get_page_by_title($name, OBJECT, $pt);
    if ($existing && !is_wp_error($existing)) {
        return $cache[$key] = (int)$existing->ID;
    }

    // 2) Buscar por slug
    $slug = sanitize_title($name);
    $q = new WP_Query([
        'post_type'      => $pt,
        'name'           => $slug,
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ]);
    if (!empty($q->posts)) {
        return $cache[$key] = (int)$q->posts[0];
    }

    // 3) Crear
    $new_id = wp_insert_post([
        'post_type'   => $pt,
        'post_status' => 'publish',
        'post_title'  => $name,
        'post_name'   => $slug,
    ], true);

    if (is_wp_error($new_id) || !$new_id) {
        return $cache[$key] = 0;
    }

    return $cache[$key] = (int)$new_id;
}

function hnh_update_acf($post_id, $field_name, $value)
{
    if (!function_exists('acf_get_field')) {
        // si ACF no está cargado por alguna razón
        update_post_meta($post_id, $field_name, $value);
        return;
    }

    // intenta obtener el field object (así consigues el field_key)
    $field = acf_get_field($field_name);

    if ($field && !empty($field['key'])) {
        // usa field key (más seguro)
        update_field($field['key'], $value, $post_id);
    } else {
        // fallback
        update_field($field_name, $value, $post_id);
    }
}

/**
 * Pretty-format CRM raw description into HTML for WP editor (bold labels + bullets → list + paragraphs).
 */
function vehicles_format_description($raw)
{
    $s = trim((string)$raw);
    if ($s === '') return '';

    // Normaliza saltos de línea
    $s = str_replace(["\r\n", "\r"], "\n", $s);

    // Lista de etiquetas que queremos resaltar
    $labels = ['Registration No', 'Frame No', 'Chassis No', 'Engine No', 'MOT', 'VIN', 'Mileage', 'Color', 'Colour'];

    // Resalta etiquetas con <strong>
    foreach ($labels as $label) {
        $pattern = '~(?<=^|\n|\A)(' . preg_quote($label, '~') . '):\s*~i';
        $s = preg_replace($pattern, '<strong>$1:</strong> ', $s);
    }

    // Separar por saltos de línea
    $lines = preg_split("~\n~", $s);

    $ul_lines = [];
    $p_lines  = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;

        // Si la línea empieza con alguna de las etiquetas principales, va a <li>
        $found_label = false;
        foreach (['Registration No', 'Chassis No', 'MOT'] as $main_label) {
            if (stripos($line, $main_label . ':') === 0) {
                $ul_lines[] = '<li>' . $line . '</li>';
                $found_label = true;
                break;
            }
        }

        if (!$found_label) {
            $p_lines[] = '<p>' . nl2br(esc_html($line)) . '</p>';
        }
    }

    $html = '';
    if (!empty($p_lines)) {
        $html .= implode("\n", $p_lines);
    }
    if (!empty($ul_lines)) {
        $html .= "\n<ul>\n" . implode("\n", $ul_lines) . "\n</ul>";
    }

    return $html;
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

/**
 * Busca un usuario por el texto del Excel.
 * Devuelve user_id o 0 si no encuentra.
 *
 * Orden:
 * 1) display_name exacto (case-insensitive)
 * 2) first_name + last_name exacto
 * 3) búsqueda flexible por display_name/user_login/user_email
 */
function vehicles_get_user_id_by_display($raw, array &$cache = [])
{
    $name = vehicles_normalize_person_name($raw);
    if ($name === '') return 0;

    $key = strtolower($name);
    if (isset($cache[$key])) return (int)$cache[$key];

    // 1) display_name exacto
    $users = get_users([
        'search'         => $name,
        'search_columns' => ['display_name'],
        'number'         => 20,
        'fields'         => ['ID', 'display_name'],
    ]);

    foreach ($users as $u) {
        if (strcasecmp((string)$u->display_name, $name) === 0) {
            return $cache[$key] = (int)$u->ID;
        }
    }

    // 2) first_name + last_name exacto (ok si tienes pocos users)
    $users = get_users([
        'number' => 500,
        'fields' => ['ID'],
    ]);

    foreach ($users as $u) {
        $first = trim((string)get_user_meta($u->ID, 'first_name', true));
        $last  = trim((string)get_user_meta($u->ID, 'last_name', true));
        $full  = trim($first . ' ' . $last);

        if ($full !== '' && strcasecmp($full, $name) === 0) {
            return $cache[$key] = (int)$u->ID;
        }
    }

    // 3) búsqueda flexible
    $users = get_users([
        'search'         => '*' . $name . '*',
        'search_columns' => ['display_name', 'user_login', 'user_email'],
        'number'         => 1,
        'fields'         => ['ID'],
    ]);

    if (!empty($users)) {
        return $cache[$key] = (int)$users[0]->ID;
    }

    return $cache[$key] = 0;
}

/**
 * Hardcoded aliases for vehicle_category terms that may arrive with different labels in Excel.
 * Returns term_id or 0.
 */
function vehicles_resolve_hardcoded_category_term_id($name)
{
    $normalized = strtolower(preg_replace('/[\s\-_]+/', '', trim((string) $name)));

    $slug_by_normalized = [
        'registrationnumbers' => 'registration-numbers',
    ];

    if (!isset($slug_by_normalized[$normalized]) || !taxonomy_exists(HNH_TAX_CATEGORY)) {
        return 0;
    }

    $term = get_term_by('slug', $slug_by_normalized[$normalized], HNH_TAX_CATEGORY);
    if ($term && !is_wp_error($term)) {
        return (int) $term->term_id;
    }

    return 0;
}

/**
 * Crea o devuelve un término en la taxonomía vehicle_category,
 * usando el nombre que viene del Excel.
 * Si viene tipo "Parent > Child > Cars", tomará el ÚLTIMO nivel ("Cars").
 * Devuelve 0 si algo falla.
 */
function vehicles_get_or_create_category_term($label)
{
    $name = trim((string)$label);
    if ($name === '' || !taxonomy_exists(HNH_TAX_CATEGORY)) return 0;

    // Si viene con niveles: "A > B > C", nos quedamos con el último
    if (strpos($name, '>') !== false) {
        $parts = array_map('trim', explode('>', $name));
        $name  = trim(end($parts));
    }

    static $cache = [];
    $key = strtolower($name);
    if (isset($cache[$key])) return (int)$cache[$key];

    $hardcoded_term_id = vehicles_resolve_hardcoded_category_term_id($name);
    if ($hardcoded_term_id) {
        return $cache[$key] = $hardcoded_term_id;
    }

    // 1) Buscar por nombre exacto
    $term = get_term_by('name', $name, HNH_TAX_CATEGORY);
    if ($term && !is_wp_error($term)) {
        return $cache[$key] = (int)$term->term_id;
    }

    // 2) Crear nuevo término
    $res = wp_insert_term($name, HNH_TAX_CATEGORY, [
        'slug' => sanitize_title($name),
    ]);

    if (!is_wp_error($res) && !empty($res['term_id'])) {
        return $cache[$key] = (int)$res['term_id'];
    }

    // 3) Fallback por slug si ya existía
    $term = get_term_by('slug', sanitize_title($name), HNH_TAX_CATEGORY);
    if ($term && !is_wp_error($term)) {
        return $cache[$key] = (int)$term->term_id;
    }

    return 0;
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
        'chassis_no'      => '~\b(?:Chassis|Frame)\s*(?:No\.?|Number)?\s*:\s*([^\r\n]+)~i',
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

function vehicles_extract_from_description_ai($raw_desc)
{
    $fallback = vehicles_extract_from_description($raw_desc);

    if (!defined('HNH_OPENAI_ENABLE') || !HNH_OPENAI_ENABLE) {
        return $fallback;
    }

    $apiKey = defined('HNH_OPENAI_API_KEY') ? HNH_OPENAI_API_KEY : '';
    if (!$apiKey) {
        return $fallback;
    }

    $text = trim((string)$raw_desc);
    if ($text === '') return $fallback;

    // Cache por hash del texto (para no pagar varias veces)
    $hash = md5($text);
    $cache_key = 'veh_ai_desc_' . $hash;
    $cached = get_transient($cache_key);
    if (is_array($cached) && isset($cached['mot'], $cached['registration_no'], $cached['chassis_no'])) {
        return $cached;
    }

    $result = vehicles_openai_extract_triplet($text, $apiKey);

    // Validación mínima del resultado
    if (!is_array($result)) {
        return $fallback;
    }

    $out = [
        'registration_no' => isset($result['registration_no']) ? trim((string)$result['registration_no']) : '',
        'chassis_no'      => isset($result['chassis_no']) ? trim((string)$result['chassis_no']) : '',
        'mot'             => isset($result['mot']) ? trim((string)$result['mot']) : '',
    ];

    // Si IA devolvió TODO vacío, no sirve
    if ($out['registration_no'] === '' && $out['chassis_no'] === '' && $out['mot'] === '') {
        return $fallback;
    }

    // Guarda cache 30 días (ajústalo)
    set_transient($cache_key, $out, 30 * DAY_IN_SECONDS);

    return $out;
}

function vehicles_extract_model_and_variant_from_title(string $title): array
{
    $t = trim(wp_strip_all_tags($title));
    if ($t === '') {
        return ['model' => '', 'variant' => ''];
    }

    // quitar año
    $t = preg_replace('~^\s*(18|19|20)\d{2}\s+~', '', $t);
    $t = trim($t);

    // quitar marca (primera palabra)
    $t = preg_replace('~^[^\s]+\s+~', '', $t);
    $t = trim($t);

    if ($t === '') {
        return ['model' => '', 'variant' => ''];
    }

    $tokens = preg_split('/\s+/', $t);

    $model = '';
    $variant = '';

    // Caso: Mk VI
    if (isset($tokens[0], $tokens[1]) && strtolower($tokens[0]) === 'mk') {
        $model = $tokens[0] . ' ' . $tokens[1];
        $variant = implode(' ', array_slice($tokens, 2));
    }

    // Caso: Speed 20
    elseif (isset($tokens[1]) && is_numeric($tokens[1])) {
        $model = $tokens[0] . ' ' . $tokens[1];
        $variant = implode(' ', array_slice($tokens, 2));
    }

    // Caso: RMB 2½ Litres
    elseif (isset($tokens[1]) && preg_match('/litre|liter/i', $tokens[2] ?? '')) {
        $model = $tokens[0];
        $variant = implode(' ', array_slice($tokens, 1));
    }

    // Caso general
    else {
        $model = $tokens[0];
        $variant = implode(' ', array_slice($tokens, 1));
    }

    return [
        'model' => trim($model),
        'variant' => trim($variant)
    ];
}

function vehicles_openai_extract_triplet($description, $apiKey)
{
    $model = defined('HNH_OPENAI_MODEL') ? HNH_OPENAI_MODEL : 'gpt-4.1-mini';

    $prompt = "Extract these fields from the vehicle description. Return ONLY valid JSON with keys:
registration_no, chassis_no, mot.

Rules:
- If a field is not present, return an empty string.
- registration_no: value after labels like 'Registration No', 'Registration Number'.
- chassis_no: value after labels like 'Chassis No', 'Chassis Number', OR 'Frame No', 'Frame Number'. If both Chassis and Frame exist, prefer Chassis.
- mot: return either 'Exempt' or 'Month YYYY' (e.g., 'September 2027') if possible; otherwise empty string.
- Do not include any extra keys, text, markdown, or explanation.

DESCRIPTION:
" . $description;

    $body = [
        'model' => $model,
        'input' => $prompt,
    ];

    $res = wp_remote_post('https://api.openai.com/v1/responses', [
        'timeout' => 60,
        'headers' => [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ],
        'body' => wp_json_encode($body),
    ]);

    if (is_wp_error($res)) {
        return null;
    }

    $code = wp_remote_retrieve_response_code($res);
    $raw  = wp_remote_retrieve_body($res);

    if ($code < 200 || $code >= 300 || !$raw) {
        return null;
    }

    $json = json_decode($raw, true);
    if (!is_array($json)) return null;

    // Respuestas típicas: output_text o contenido estructurado.
    // Intentamos extraer "output_text" si existe; si no, buscamos texto en output[...]
    $text = '';
    if (!empty($json['output_text']) && is_string($json['output_text'])) {
        $text = $json['output_text'];
    } elseif (!empty($json['output']) && is_array($json['output'])) {
        // Fallback: recorre bloques
        foreach ($json['output'] as $item) {
            if (!empty($item['content']) && is_array($item['content'])) {
                foreach ($item['content'] as $c) {
                    if (($c['type'] ?? '') === 'output_text' && !empty($c['text'])) {
                        $text .= $c['text'];
                    }
                }
            }
        }
    }

    $text = trim($text);
    if ($text === '') return null;

    // El modelo debe retornar JSON puro, pero por seguridad:
    $text = vehicles_extract_json_object($text);
    if ($text === '') return null;

    $data = json_decode($text, true);
    if (!is_array($data)) return null;

    return $data;
}

function vehicles_extract_json_object($s)
{
    $s = trim((string)$s);
    if ($s === '') return '';

    // Si ya es JSON
    if ($s[0] === '{') return $s;

    // Busca el primer { y el último }
    $start = strpos($s, '{');
    $end   = strrpos($s, '}');
    if ($start === false || $end === false || $end <= $start) return '';

    return substr($s, $start, $end - $start + 1);
}

function vehicles_format_reg_chassis_mot($html) {
    if (preg_match_all('~<p>(.*?)</p>~i', $html, $matches)) {
        foreach ($matches[1] as $idx => $content) {
            if (preg_match('~\b(Registration No|Chassis No|MOT)\b~i', $content)) {
                $lines = preg_split('~<br\s*/?>~i', $content);
                $lis = '';
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line !== '') $lis .= '<li>' . $line . '</li>';
                }
                if ($lis !== '') {
                    $html = str_replace($matches[0][$idx], '<ul>' . $lis . '</ul>', $html);
                }
            }
        }
    }
    return $html;
}

function vehicles_convert_registration_block_to_ul(string $html): string {
    if (preg_match('/<p>(.*?)<\/p>/is', $html, $matches)) {
        $p_content = $matches[1];

        if (stripos($p_content, 'Registration') !== false 
            || stripos($p_content, 'Chassis') !== false 
            || stripos($p_content, 'MOT') !== false) {

            $lines = preg_split('/<br\s*\/?>/i', $p_content, -1, PREG_SPLIT_NO_EMPTY);
            $lines = array_map('trim', $lines);
            if (empty($lines)) return $html;

            $ul = "<ul>\n";
            foreach ($lines as $line) {
                $ul .= "<li>$line</li>\n";
            }
            $ul .= "</ul>";

            return str_replace($matches[0], $ul, $html);
        }
    }
    return $html;
}