<?php
// === Auctions CSV Importer (sin plugins) ===
// Requisitos: ACF PRO activo, CPT 'auction' ya registrado.

// ------------- Ajustes principales -------------
/**
 * Mapa: cabecera del CSV => nombre del campo ACF o columna WP
 * Asegúrate de que las cabeceras del CSV coincidan (sensibles a mayúsculas/minúsculas).
 * Si tu CRM cambia los títulos, ajústalos aquí.
 */
function hh_auction_csv_field_map() {
    return [
        // WP core
        'Title'         => 'post_title',
        'Description'   => 'post_content',

        // ACF (usa field NAME; ACF lo resuelve)
        'StockNumber'   => 'stock_number',
        'SubTitle'      => 'subtitle',
        'Provenance'    => 'provenance',
        'Dimensions'    => 'dimensions',
        'Low estimate'  => 'low_estimate',
        'High estimate' => 'high_estimate',
        'SoldPrice'     => 'sold_price',
        'Result'        => 'result',
        'Buyers Premium'=> 'buyers_premium',
        'Buyer Id'      => 'buyer_id',
        'Buyer Ref'     => 'buyer_ref',
        'Buyer Name'    => 'buyer_name',
        'Buyer Email'   => 'buyer_email',
        'PaddleNumber'  => 'paddle_number',
        'Vendor Id'     => 'vendor_id',
        'Vendor Ref'    => 'vendor_ref',
        'Vendor Name'   => 'vendor_name',
        'Vendor Email'  => 'vendor_email',
        // Si luego agregas taxonomías, añádelas aquí y manéjalas en la sección TAX.
    ];
}

// Clave única para crear/actualizar (cambia si prefieres otra, p. ej. VIN)
function hh_auction_unique_key() {
    return 'StockNumber';
}

// ------------- UI: botón arriba de la lista -------------
add_action('restrict_manage_posts', function() {
    global $typenow;
    if ($typenow !== 'auction') return;

    $url = add_query_arg([
        'page' => 'hh-auction-import',
        'post_type' => 'auction',
    ], admin_url('edit.php'));

    echo '<a href="' . esc_url($url) . '" class="button button-primary" style="margin-left:8px;">Import CSV</a>';
});

// ------------- Submenú: página de importación -------------
add_action('admin_menu', function() {
    add_submenu_page(
        'edit.php?post_type=auction',
        'Import CSV',
        'Import CSV',
        'manage_options',
        'hh-auction-import',
        'hh_auction_import_page_render'
    );
});

function hh_auction_import_page_render() {
    if (!current_user_can('manage_options')) {
        wp_die('No tienes permisos suficientes.');
    }

    // Procesamiento si enviaron el formulario
    if (isset($_POST['hh_auction_do_import']) && check_admin_referer('hh_auction_import_nonce', 'hh_auction_import_nonce')) {
        hh_auction_handle_csv_upload_and_import();
    }

    ?>
    <div class="wrap">
        <h1>Importar Auctions desde CSV</h1>
        <p>Sube un archivo <strong>.csv</strong> exportado desde tu CRM (la primera fila debe contener las cabeceras).</p>

        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('hh_auction_import_nonce', 'hh_auction_import_nonce'); ?>
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><label for="hh_csv_file">Archivo CSV</label></th>
                        <td><input type="file" name="hh_csv_file" id="hh_csv_file" accept=".csv" required></td>
                    </tr>
                    <tr>
                        <th scope="row">Modo de estado</th>
                        <td>
                            <label><input type="radio" name="hh_post_status" value="publish" checked> Publicar</label>
                            &nbsp;&nbsp;
                            <label><input type="radio" name="hh_post_status" value="draft"> Borrador</label>
                        </td>
                    </tr>
                </tbody>
            </table>

            <p class="submit">
                <button type="submit" name="hh_auction_do_import" class="button button-primary">Importar</button>
            </p>
        </form>
    </div>
    <?php
}

// ------------- Procesamiento: subir y leer CSV -------------
function hh_auction_handle_csv_upload_and_import() {
    if (empty($_FILES['hh_csv_file']['name'])) {
        hh_admin_notice('No se subió ningún archivo.', 'error');
        return;
    }

    // Valida y mueve el archivo al directorio de uploads
    $file = $_FILES['hh_csv_file'];
    $overrides = ['test_form' => false, 'mimes' => ['csv' => 'text/csv']];
    $movefile  = wp_handle_upload($file, $overrides);

    if (!isset($movefile['file'])) {
        hh_admin_notice('Error al subir el CSV: ' . esc_html($movefile['error'] ?? 'desconocido'), 'error');
        return;
    }

    $path = $movefile['file'];

    // Lee CSV en memoria (maneja BOM y detecta delimitador)
    $rows = hh_csv_to_array($path);
    if (!$rows || count($rows) < 2) {
        hh_admin_notice('CSV vacío o sin filas válidas.', 'error');
        return;
    }

    // Primera fila = cabeceras
    $headers = array_map('trim', array_shift($rows));
    $map     = hh_auction_csv_field_map();
    $unique  = hh_auction_unique_key();

    // Validación de cabeceras mínimas
    if (!in_array('Title', $headers, true) || !in_array($unique, $headers, true)) {
        hh_admin_notice('El CSV debe incluir al menos las columnas "Title" y "' . esc_html($unique) . '".', 'error');
        return;
    }

    // Índices de columnas para acceso rápido
    $idx = array_flip($headers);

    $created = 0; $updated = 0; $errors = 0;

    foreach ($rows as $line => $cols) {
        // Normaliza tamaño del array de columnas
        if (count($cols) < count($headers)) {
            $cols = array_pad($cols, count($headers), '');
        }

        // Arma un array asociativo fila=>valor
        $row = [];
        foreach ($headers as $h) {
            $row[$h] = isset($idx[$h]) && isset($cols[$idx[$h]]) ? trim((string)$cols[$idx[$h]]) : '';
        }

        // Saltar filas sin Title ni Unique
        if ($row['Title'] === '' || $row[$unique] === '') {
            continue;
        }

        // Busca si ya existe por meta unique_key (ej: stock_number)
        $existing_id = hh_find_existing_auction_by_unique($map[$unique] ?? 'stock_number', $row[$unique]);

        // Construye datos base del post
        $postarr = [
            'post_type'   => 'auction',
            'post_status' => sanitize_text_field($_POST['hh_post_status'] ?? 'publish'),
            'post_title'  => $row['Title'],
            'post_content'=> $row['Description'] ?? '',
        ];

        if ($existing_id) {
            $postarr['ID'] = $existing_id;
            $pid = wp_update_post($postarr, true);
        } else {
            $pid = wp_insert_post($postarr, true);
        }

        if (is_wp_error($pid)) {
            $errors++;
            continue;
        }

        // Guarda campos ACF (y/o meta) según el mapa
        foreach ($map as $csv_key => $target) {
            if (!isset($row[$csv_key])) continue;

            $value = $row[$csv_key];

            // Campos core ya se guardaron (post_title/post_content)
            if ($target === 'post_title' || $target === 'post_content') continue;

            // Si quieres castear a número en algunos campos:
            $numeric_fields = ['low_estimate','high_estimate','sold_price','buyers_premium'];
            if (in_array($target, $numeric_fields, true)) {
                $value = is_numeric(str_replace([','], '', $value)) ? floatval(str_replace([','], '', $value)) : $value;
            }

            // Guarda con ACF si existe; si no, a meta normal
            if (function_exists('update_field')) {
                // update_field acepta field name; si prefieres field key, cámbialo aquí.
                update_field($target, $value, $pid);
            } else {
                update_post_meta($pid, $target, $value);
            }
        }

        // TAXONOMÍAS (opcional): ejemplo si tu CSV trae 'Auction Category'
        // if (!empty($row['Auction Category'])) {
        //     wp_set_object_terms($pid, array_map('trim', explode('|', $row['Auction Category'])), 'auction_category', false);
        // }

        if ($existing_id) { $updated++; } else { $created++; }
    }

    hh_admin_notice("Importación completada. Creados: $created | Actualizados: $updated | Errores: $errors", 'success');
}

// ------------- Utilidades -------------
function hh_find_existing_auction_by_unique($meta_key, $value) {
    $q = new WP_Query([
        'post_type'      => 'auction',
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'meta_key'       => $meta_key,
        'meta_value'     => $value,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ]);
    return $q->have_posts() ? (int) $q->posts[0] : 0;
}

function hh_csv_to_array($filepath) {
    $delim = hh_detect_csv_delimiter($filepath);
    $out   = [];

    $handle = fopen($filepath, 'r');
    if (!$handle) return $out;

    // Salta BOM si existe
    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") {
        // no había BOM; regresamos el puntero al inicio
        rewind($handle);
    }

    while (($data = fgetcsv($handle, 0, $delim)) !== false) {
        $out[] = $data;
    }
    fclose($handle);
    return $out;
}

function hh_detect_csv_delimiter($filepath) {
    $delims = [",", ";", "\t", "|"];
    $firstLine = '';
    $h = fopen($filepath, 'r');
    if ($h) {
        $firstLine = fgets($h);
        fclose($h);
    }
    $best = ",";
    $bestCount = 0;
    foreach ($delims as $d) {
        $c = substr_count($firstLine, $d);
        if ($c > $bestCount) { $best = $d; $bestCount = $c; }
    }
    return $best;
}

function hh_admin_notice($message, $type = 'info') {
    add_action('admin_notices', function() use ($message, $type) {
        echo '<div class="notice notice-' . esc_attr($type) . ' is-dismissible"><p>' . wp_kses_post($message) . '</p></div>';
    });
}