<?php

require_once __DIR__ . '/../services/EvaluationRequestService.php';
require_once __DIR__ . '/../services/ConditionReportRequestService.php';

add_action('acf/save_post', function ($post_id) {
    if (!is_numeric($post_id)) return;

    $post_id = (int) $post_id;
    var_dump($post_id);

    // evitar revisiones/autosaves
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) return;

    // solo CPT vehicles
    if (get_post_type($post_id) !== 'vehicles') return;

    $evaluation_service = new EvaluationRequestService();
    $condition_service = new ConditionReportRequestService();

    $condition_service->syncEvalRequestFromVehicle($post_id);
    $evaluation_service->syncEvalRequestFromVehicle($post_id);
}, 20);
