<?php
/**
 * Admin Vehicles: ORDER BY via wp_vehicles_search (SQL)
 *
 * - Mantiene paginación / counts (FOUND_ROWS) porque sigue siendo WP_Query.
 * - Solo cambia el JOIN y el ORDER BY cuando ordenas por columnas específicas.
 */

if (!defined('ABSPATH')) exit;

add_filter('manage_edit-vehicles_sortable_columns', function(array $sortable) : array {
    /**
     * OJO:
     * - La key (izquierda) debe ser el "slug" de tu columna en la tabla del admin.
     * - El value (derecha) es lo que WP pondrá en ?orderby=...
     */
    $sortable['lot_number']   = 'vs_lot_number';
    $sortable['auction_date'] = 'vs_auction_date';
    $sortable['status']       = 'vs_status';

    // Si quieres forzar title por tu tabla (si guardas un title ahí), podrías:
    // $sortable['title'] = 'vs_title';

    return $sortable;
}, 20);

/**
 * Marcamos el main query del admin para que luego los filtros SQL sepan que aplicar.
 */
add_action('pre_get_posts', function(\WP_Query $q) {
    if (!is_admin() || !$q->is_main_query()) return;
    if ($q->get('post_type') !== 'vehicles') return;

    // Solo actuamos si el orderby es de los nuestros (whitelist)
    $orderby = (string) $q->get('orderby');
    $allowed = ['vs_lot_number', 'vs_auction_date', 'vs_status' /*, 'vs_title'*/];

    if (in_array($orderby, $allowed, true)) {
        // Flag interno para los filtros de SQL
        $q->set('__vs_ordering', 1);

        // Opcional: evita queries pesadas si no necesitas el total.
        // Pero OJO: WP usa found_posts para paginación del admin, normalmente sí conviene tenerlo.
        // $q->set('no_found_rows', false);
    }
}, 20);

/**
 * Modificamos el SQL final: JOIN + ORDER BY.
 */
add_filter('posts_clauses', function(array $clauses, \WP_Query $q) : array {
    if (!is_admin() || !$q->is_main_query()) return $clauses;
    if ((int) $q->get('__vs_ordering') !== 1) return $clauses;
    if ($q->get('post_type') !== 'vehicles') return $clauses;

    global $wpdb;

    $table_vs = $wpdb->prefix . 'vehicles_search';

    // Seguridad: solo permitimos estos orderby
    $orderby = (string) $q->get('orderby');
    $order   = strtoupper((string) $q->get('order'));
    $order   = ($order === 'ASC') ? 'ASC' : 'DESC';

    /**
     * Mapea orderby => expresión SQL segura
     * AJUSTA los nombres de columna según tu wp_vehicles_search real.
     */
    $orderExpr = null;

    switch ($orderby) {
        case 'vs_lot_number':
            // lot_number NUMÉRICO
            // Si en tu tabla ya es INT, puedes solo: vs.lot_number
            $orderExpr = "CAST(vs.lot_number AS UNSIGNED) {$order}";
            break;

        case 'vs_auction_date':
            // auction_date tipo DATETIME / DATE
            // Si es DATETIME real, basta vs.auction_date
            $orderExpr = "vs.auction_date {$order}";
            break;

        case 'vs_status':
            // status texto
            $orderExpr = "vs.status {$order}";
            break;

        // case 'vs_title':
        //     $orderExpr = "vs.title {$order}";
        //     break;
    }

    if (!$orderExpr) return $clauses;

    /**
     * JOIN: enlazamos wp_posts.ID con vs.vehicle_id
     * IMPORTANTE: usamos LEFT JOIN para que si faltan filas en vehicles_search, no desaparezcan posts.
     * Si quieres que SOLO aparezcan los que existen en vehicles_search => usa INNER JOIN.
     */
    $join = " LEFT JOIN {$table_vs} vs ON (vs.vehicle_id = {$wpdb->posts}.ID) ";
    if (strpos($clauses['join'], $join) === false) {
        $clauses['join'] .= $join;
    }

    /**
     * ORDER BY: ponemos el nuestro primero.
     * Y agregamos un fallback estable por ID para evitar “saltos” cuando hay empates.
     */
    $clauses['orderby'] = " {$orderExpr}, {$wpdb->posts}.ID {$order} ";

    return $clauses;
}, 20, 2);