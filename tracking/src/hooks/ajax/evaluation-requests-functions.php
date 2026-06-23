<?php

require_once __DIR__ . '/../../services/EvaluationRequestService.php';

/**
 * AJAX: mover Evaluation Request de "new" => "client_contacted"
 */
add_action('wp_ajax_hh_eval_request_pass_client_contacted', function () {

    if (!current_user_can(HH_TRACKING_VIEW_CAP)) {
        wp_send_json_error(['message' => 'No permission.'], 403);
    }

    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
    if (!wp_verify_nonce($nonce, 'hh_eval_request_update')) {
        wp_send_json_error(['message' => 'Invalid nonce.'], 400);
    }

    $request_id = isset($_POST['request_id']) ? absint($_POST['request_id']) : 0;
    if ($request_id <= 0) {
        wp_send_json_error(['message' => 'Invalid request id.'], 400);
    }

    try {
        $service = new EvaluationRequestService();

        $ok = $service->passToClientContacted($request_id);

        if (!$ok) {
            wp_send_json_error(['message' => 'Request not updated (maybe not in New anymore).'], 400);
        }

        wp_send_json_success(['message' => 'Updated.']);
    } catch (Throwable $e) {
        error_log('[HH Tracking] AJAX passToClientContacted failed: ' . $e->getMessage());
        wp_send_json_error(['message' => 'Server error.'], 500);
    }
});


/**
 * AJAX: mover Evaluation Request de "client_contacted" => "assigned" asignando especialista.
 */
add_action('wp_ajax_hh_eval_request_pass_assigned', function () {

    if (!current_user_can(HH_TRACKING_VIEW_CAP)) {
        wp_send_json_error(['message' => 'No permission.'], 403);
    }

    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
    if (!wp_verify_nonce($nonce, 'hh_eval_request_update')) {
        wp_send_json_error(['message' => 'Invalid nonce.'], 400);
    }

    $request_id       = isset($_POST['request_id']) ? absint($_POST['request_id']) : 0;
    $assigned_user_id = isset($_POST['assigned_user_id']) ? absint($_POST['assigned_user_id']) : 0;

    if ($request_id <= 0) {
        wp_send_json_error(['message' => 'Invalid request id.'], 400);
    }

    // si no eligió usuario -> NO hacemos nada (se queda client_contacted)
    if ($assigned_user_id <= 0) {
        wp_send_json_success(['message' => 'No changes applied.']);
    }

    try {
        $service = new EvaluationRequestService();

        $ok = $service->passToAssigned($request_id, $assigned_user_id);

        if (!$ok) {
            wp_send_json_error(['message' => 'Request not updated (maybe not in Client Contacted anymore, invalid user, or db error).'], 400);
        }

        wp_send_json_success(['message' => 'Updated.']);
    } catch (Throwable $e) {
        error_log('[HH Tracking] AJAX passToAssigned failed: ' . $e->getMessage());
        wp_send_json_error(['message' => 'Server error.'], 500);
    }
});

/**
 * AJAX: mover Evaluation Request de "assigned" a "under_review" (solo status).
 */
add_action('wp_ajax_hh_eval_request_pass_under_review', function () {

    if (!current_user_can(HH_TRACKING_VIEW_CAP)) {
        wp_send_json_error(['message' => 'No permission.'], 403);
    }

    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
    if (!wp_verify_nonce($nonce, 'hh_eval_request_update')) {
        wp_send_json_error(['message' => 'Invalid nonce.'], 400);
    }

    $request_id = isset($_POST['request_id']) ? absint($_POST['request_id']) : 0;
    if ($request_id <= 0) {
        wp_send_json_error(['message' => 'Invalid request id.'], 400);
    }

    try {
        $service = new EvaluationRequestService();

        // Solo debe pasar si está en assigned (como hiciste en condition report)
        $ok = $service->passToUnderReview($request_id);

        if (!$ok) {
            wp_send_json_error(['message' => 'Request not updated (maybe not in Assigned anymore, or db error).'], 400);
        }

        wp_send_json_success(['message' => 'Updated.']);
    } catch (Throwable $e) {
        error_log('[HH Tracking] AJAX passToUnderReview failed: ' . $e->getMessage());
        wp_send_json_error(['message' => 'Server error.'], 500);
    }
});

/**
 * AJAX: decisión de consignment desde "under_review" =>
 *  - Si accepted=1  -> status = "consignment_confirmed", fit_for_auction = 1, not_consigned_reason = NULL
 *  - Si accepted=0  -> status = "not_consigned",         fit_for_auction = 0, not_consigned_reason = (reason)
 *
 * Reglas:
 * - Solo permite si el registro está en "under_review".
 * - Si accepted=0, el campo "reason" es obligatorio.
 * - Permisos: Admin/Sales Manager o el usuario asignado (assigned_user_id).
 */
add_action('wp_ajax_hh_eval_request_set_consignment_result', function () {
    if (!current_user_can(HH_TRACKING_VIEW_CAP)) {
        wp_send_json_error(['message' => 'Forbidden'], 403);
    }

    check_ajax_referer('hh_eval_request_update', 'nonce');

    $requestId = isset($_POST['request_id']) ? absint($_POST['request_id']) : 0;
    $accepted  = isset($_POST['accepted']) ? (int) $_POST['accepted'] : 0;
    $reason    = isset($_POST['reason']) ? sanitize_textarea_field(wp_unslash($_POST['reason'])) : '';

    $lotValuation = null;
    $recommendedAuctionId = null;

    if ($accepted === 1) {
        $lotValuation = isset($_POST['lot_valuation']) ? sanitize_text_field(wp_unslash($_POST['lot_valuation'])) : '';
        $recommendedAuctionId = isset($_POST['recommended_auction_id']) ? absint($_POST['recommended_auction_id']) : 0;

        // valida numeric con decimales "."
        if ($lotValuation === '' || !preg_match('/^\d+(\.\d+)?$/', $lotValuation)) {
            wp_send_json_error(['message' => 'Invalid lot valuation.'], 400);
        }
        if (!$recommendedAuctionId) {
            wp_send_json_error(['message' => 'Recommended auction is required.'], 400);
        }
    } else {
        // NO => reason obligatorio
        if (strlen(trim($reason)) < 3) {
            wp_send_json_error(['message' => 'Reason is required when selecting No.'], 400);
        }
        $reason = trim($reason);
    }

    if (!$requestId) {
        wp_send_json_error(['message' => 'Invalid request id.'], 400);
    }

    try {
        $service = new EvaluationRequestService();
        $service->setConsignmentResult(
            $requestId,
            $accepted === 1,
            $accepted === 1 ? null : $reason,
            $accepted === 1 ? $lotValuation : null,
            $accepted === 1 ? $recommendedAuctionId : null
        );

        wp_send_json_success(['message' => 'Updated']);
    } catch (Throwable $e) {
        error_log('[HH Tracking] Consignment update failed: ' . $e->getMessage());
        wp_send_json_error(['message' => 'Server error.'], 500);
    }
});

/**
 * AJAX: mover Evaluation Request de "consignment_confirmed" => "in_progress" (solo status).
 * Reglas:
 * - Solo permite si el registro está en "consignment_confirmed".
 * - Permisos: Admin/Sales Manager o el usuario asignado (assigned_user_id).
 */
add_action('wp_ajax_hh_eval_request_pass_in_progress', function () {
    if (!current_user_can(HH_TRACKING_VIEW_CAP)) {
        wp_send_json_error(['message' => 'Forbidden'], 403);
    }

    check_ajax_referer('hh_eval_request_update', 'nonce');

    $request_id = isset($_POST['request_id']) ? (int) $_POST['request_id'] : 0;
    if ($request_id <= 0) {
        wp_send_json_error(['message' => 'Invalid request_id']);
    }

    $service = new EvaluationRequestService();

    // Debe existir
    $row = $service->getById($request_id);
    if (!$row) {
        wp_send_json_error(['message' => 'Request not found']);
    }

    // Permisos: admin / sales manager / assigned
    $current_user_id = get_current_user_id();
    $sales_manager_id = (int) get_option('hh_sales_manager_user_id', 0);
    $assigned_user_id = (int) ($row->assigned_user_id ?? 0);

    $can = current_user_can(HH_TRACKING_ADMIN_CAP) || ($current_user_id === $sales_manager_id) || ($assigned_user_id > 0 && $current_user_id === $assigned_user_id);
    if (!$can) {
        wp_send_json_error(['message' => 'You cannot update this request'], 403);
    }

    // Solo desde consignment_confirmed
    $current_status = (string) ($row->status ?? '');
    if ($current_status !== 'consignment_confirmed') {
        wp_send_json_error(['message' => 'Invalid status transition']);
    }

    $ok = $service->updateStatus($request_id, 'in_progress');
    if (!$ok) {
        wp_send_json_error(['message' => 'DB update failed']);
    }

    wp_send_json_success(['message' => 'Moved to In Progress']);
});

/**
 * AJAX: mover Evaluation Request de "in_progress" => "finalised"
 * guardando lot_id + lot_year/lot_make/lot_model + sold/sold_price.
 */
add_action('wp_ajax_hh_eval_request_pass_finalised', function () {

    check_ajax_referer('hh_eval_request_update', 'nonce');

    if (!current_user_can(HH_TRACKING_VIEW_CAP)) {
        wp_send_json_error(['message' => 'Permission denied.'], 403);
    }

    $request_id = isset($_POST['request_id']) ? absint($_POST['request_id']) : 0;
    $vehicle_id = isset($_POST['vehicle_id']) ? absint($_POST['vehicle_id']) : 0;

    if (!$request_id || !$vehicle_id) {
        wp_send_json_error(['message' => 'Missing request_id or vehicle_id.'], 400);
    }

    // Validar que exista el vehicle
    $vehicle = get_post($vehicle_id);
    if (!$vehicle || $vehicle->post_type !== 'vehicles') {
        wp_send_json_error(['message' => 'Invalid vehicle.'], 400);
    }

    try {
        $service = new EvaluationRequestService();
        $ok = $service->finaliseWithVehicle($request_id, $vehicle_id, get_current_user_id());

        if (!$ok) {
            wp_send_json_error(['message' => 'Update failed.'], 500);
        }

        wp_send_json_success(['message' => 'Updated.']);
    } catch (Throwable $e) {
        wp_send_json_error(['message' => $e->getMessage()], 500);
    }
});






add_action('wp_ajax_hh_eval_vehicle_search', function () {

    check_ajax_referer('hh_eval_request_update', 'nonce');

    if (!current_user_can(HH_TRACKING_VIEW_CAP)) {
        wp_send_json_error(['message' => 'Permission denied.'], 403);
    }

    $term = isset($_POST['term']) ? sanitize_text_field(wp_unslash($_POST['term'])) : '';
    if (strlen($term) < 2) {
        wp_send_json_success([]);
    }

    $q = new WP_Query([
        'post_type'      => 'vehicles',
        'post_status'    => 'publish',
        's'              => $term,
        'posts_per_page' => 20,
        'no_found_rows'  => true,
        'fields'         => 'ids',
    ]);

    $items = [];
    if (!empty($q->posts)) {
        foreach ($q->posts as $id) {
            $items[] = [
                'id'    => (int) $id,
                'label' => get_the_title($id) . " (#{$id})",
                'value' => get_the_title($id),
            ];
        }
    }

    wp_send_json_success($items);
});

add_action('wp_ajax_hh_search_auctions', function () {
    if (!current_user_can(HH_TRACKING_VIEW_CAP)) {
        wp_send_json_error(['message' => 'Forbidden'], 403);
    }

    check_ajax_referer('hh_auction_search', 'nonce');

    $term = isset($_REQUEST['term']) ? sanitize_text_field(wp_unslash($_REQUEST['term'])) : '';
    $term = trim($term);

    $q = new WP_Query([
        'post_type'      => 'auction',
        'post_status'    => 'publish',
        'posts_per_page' => 20,
        's'              => $term,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ]);

    $data = [];
    foreach ($q->posts as $id) {
        $title = get_the_title($id).' (Auction Date: '.get_field('auction_date', $id).')';
        $data[] = [
            'id'    => (int) $id,
            'label' => $title,
            'value' => $title,
        ];
    }

    wp_send_json_success($data);
});
