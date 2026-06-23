<?php

require_once __DIR__ . '/../../services/ConditionReportRequestService.php';

/**
 * NEW -> IN_PROGRESS (requiere assigned_user_id)
 */
add_action('wp_ajax_hh_condition_request_pass_in_progress', function () {

    if (!current_user_can(HH_TRACKING_VIEW_CAP)) {
        wp_send_json_error(['message' => 'No permission.'], 403);
    }

    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
    if (!wp_verify_nonce($nonce, 'hh_condition_request_update')) {
        wp_send_json_error(['message' => 'Invalid nonce.'], 400);
    }

    $request_id       = isset($_POST['request_id']) ? absint($_POST['request_id']) : 0;
    $assigned_user_id = isset($_POST['assigned_user_id']) ? absint($_POST['assigned_user_id']) : 0;

    // Si no hay usuario, NO cambiamos nada (según tu requerimiento)
    if ($request_id <= 0 || $assigned_user_id <= 0) {
        wp_send_json_success(['message' => 'No changes applied.']);
    }

    // Validación: que el usuario exista
    $u = get_user_by('id', $assigned_user_id);
    if (!$u) {
        wp_send_json_error(['message' => 'User not found.'], 400);
    }

    try {
        $service = new ConditionReportRequestService();
        $ok = $service->passToInProgress($request_id, $assigned_user_id);

        if (!$ok) {
            wp_send_json_error(['message' => 'Request not updated (maybe not in New anymore or db error).'], 400);
        }

        wp_send_json_success(['message' => 'Updated.']);
    } catch (Throwable $e) {
        error_log('[HH Tracking] AJAX passToInProgress failed: ' . $e->getMessage());
        wp_send_json_error(['message' => 'Server error.'], 500);
    }
});


/**
 * IN_PROGRESS -> COMPLETED
 */
add_action('wp_ajax_hh_condition_request_pass_completed', function () {

    if (!current_user_can(HH_TRACKING_VIEW_CAP)) {
        wp_send_json_error(['message' => 'No permission.'], 403);
    }

    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
    if (!wp_verify_nonce($nonce, 'hh_condition_request_update')) {
        wp_send_json_error(['message' => 'Invalid nonce.'], 400);
    }

    $request_id = isset($_POST['request_id']) ? absint($_POST['request_id']) : 0;
    if ($request_id <= 0) {
        wp_send_json_error(['message' => 'Invalid request_id.'], 400);
    }

    try {
        $service = new ConditionReportRequestService();
        $ok = $service->passToCompleted($request_id);

        if (!$ok) {
            wp_send_json_error(['message' => 'Request not updated (maybe not in In Progress anymore or db error).'], 400);
        }

        wp_send_json_success(['message' => 'Updated.']);
    } catch (Throwable $e) {
        error_log('[HH Tracking] AJAX passToCompleted failed: ' . $e->getMessage());
        wp_send_json_error(['message' => 'Server error.'], 500);
    }
});