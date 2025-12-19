<?php

// Evita que rompa si GF no está activo
if (!class_exists('GFAPI')) {
    return;
}

require_once __DIR__ . '/../services/EvaluationRequestService.php';
require_once __DIR__ . '/../services/ConditionReportRequestService.php';

/**
 * Hook: se dispara cuando Gravity Forms ya guardó la entry.
 */
add_action('gform_after_submission', 'hh_tracking_after_gf_submission', 10, 2);

function hh_tracking_after_gf_submission($entry, $form)
{
    $form_id = isset($form['id']) ? (int) $form['id'] : 0;
    if ($form_id <= 0) {
        return;
    }

    // IDs configurados en tu módulo Tracking (wp_options)
    $eval_form_id = (int) get_option('hh_eval_form_id', 0);
    $cond_form_id = (int) get_option('hh_cond_form_id', 0);

    // Si el formulario no está registrado en Tracking, no hacemos nada
    if ($form_id !== $eval_form_id && $form_id !== $cond_form_id) {
        return;
    }

    $gf_entry_id = isset($entry['id']) ? (int) $entry['id'] : 0;
    $created_at  = isset($entry['date_created']) ? (string) $entry['date_created'] : current_time('mysql');

    /**
     * =====================================
     * 1) Evaluation Requests
     * =====================================
     */
    if ($form_id === $eval_form_id) {

        // Asignación automática al Sales Manager (configurado)
        $sales_manager_user_id = (int) get_option('hh_sales_manager_user_id', 0);

        // TODO: mapear desde GF fields (cuando lo definas)
        $lot_id    = 0;
        $lot_year  = null;
        $lot_make  = null;
        $lot_model = null;

        try {
            $service = new EvaluationRequestService();
            $service->create(
                $gf_entry_id,
                $created_at,
                $sales_manager_user_id,
                $lot_id,
                $lot_year,
                $lot_make,
                $lot_model
            );
        } catch (Throwable $e) {
            error_log('[HH Tracking] Eval Request insert failed. Entry ID: ' . $gf_entry_id . ' | ' . $e->getMessage());
        }

        return;
    }

    /**
     * =====================================
     * 2) Condition Report Requests
     * =====================================
     */
    if ($form_id === $cond_form_id) {

        // ✅ Validación: debe venir el parametro vehicle en la URL
        $vehicle_id = isset($_GET['vehicle']) ? absint($_GET['vehicle']) : 0;

        if (!$vehicle_id) {
            error_log('[HH Tracking] Condition Report NOT saved. Missing ?vehicle= in URL. Entry ID: ' . $gf_entry_id);
            return;
        }

        // ✅ Validación: debe existir el post y ser CPT vehicles
        $vehicle_post = get_post($vehicle_id);

        if (!$vehicle_post) {
            error_log('[HH Tracking] Condition Report NOT saved. Vehicle post not found. vehicle=' . $vehicle_id . ' | Entry ID: ' . $gf_entry_id);
            return;
        }

        if ($vehicle_post->post_type !== 'vehicles') {
            error_log('[HH Tracking] Condition Report NOT saved. Vehicle post type mismatch. vehicle=' . $vehicle_id . ' post_type=' . $vehicle_post->post_type . ' | Entry ID: ' . $gf_entry_id);
            return;
        }

        /**
         * Traer ACF del vehicle:
         * - year_vehicle (text)
         * - artist_maker_brand (taxonomy field)
         * - model_vehicle (post object field)
         */
        $lot_year  = get_field('year_vehicle', $vehicle_id);

        // Make (Taxonomy)
        $lot_make = null;
        $make_term = get_field('artist_maker_brand', $vehicle_id);

        // ACF taxonomy puede devolver objeto/array/int dependiendo config:
        if (is_object($make_term) && !empty($make_term->name)) {
            $lot_make = (string) $make_term->name;
        } elseif (is_array($make_term) && isset($make_term['name'])) {
            $lot_make = (string) $make_term['name'];
        } elseif (is_numeric($make_term)) {
            $term_obj = get_term((int) $make_term);
            $lot_make = ($term_obj && !is_wp_error($term_obj)) ? (string) $term_obj->name : null;
        }

        // Model (Post Object)
        $lot_model = null;
        $model_post = get_field('model_vehicle', $vehicle_id);

        // ACF post_object puede devolver objeto o ID
        if (is_object($model_post) && isset($model_post->ID)) {
            $lot_model = (string) get_the_title($model_post->ID);
        } elseif (is_numeric($model_post) && (int)$model_post > 0) {
            $lot_model = (string) get_the_title((int) $model_post);
        }

        // TODO: asignación automática al especialista del lot (según tu requerimiento)
        $assigned_user_id = 0;

        // Por ahora: auction data vacía (lo completamos cuando lo conectes con Auctions/Lots)
        $auction_id   = 0;
        $lot_number   = null;
        $auction_name = null;

        // ✅ lot_id es el ID del vehicle CPT
        $lot_id = $vehicle_id;

        try {
            $service = new ConditionReportRequestService();
            $service->create(
                $gf_entry_id,
                $created_at,
                $assigned_user_id,
                $auction_id,
                $lot_id,
                $lot_number,
                $auction_name,
                $lot_year ? (string) $lot_year : null,
                $lot_make,
                $lot_model
            );
        } catch (Throwable $e) {
            error_log('[HH Tracking] Condition Report insert failed. Entry ID: ' . $gf_entry_id . ' | ' . $e->getMessage());
        }

        return;
    }
}
