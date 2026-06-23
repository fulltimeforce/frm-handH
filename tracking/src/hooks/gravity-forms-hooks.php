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
        // $sales_manager_user_id = (int) get_option('hh_sales_manager_user_id', 0);
        $sales_manager_user_id = 0;

        // TODO: mapear desde GF fields (cuando lo definas)
        $lot_id    = 0;
        $lot_name  = null;
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
                $lot_name,
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
         * - artist_maker_brand (post object field)
         * - model_vehicle (post object field)
         */
        $lot_year  = get_field('year_vehicle', $vehicle_id);

        // Make (Post Object)
        $lot_make = null;
        $make_post = get_field('artist_maker_brand', $vehicle_id);

        // ACF post_object puede devolver objeto o ID
        if (is_object($make_post) && isset($make_post->ID)) {
            $lot_make = (string) get_the_title($make_post->ID);
        } elseif (is_numeric($make_post) && (int)$make_post > 0) {
            $lot_make = (string) get_the_title((int) $make_post);
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
        $auction_object  = get_field('auction_number_latest', $vehicle_id);

        $lot_number = get_field('lot_number_latest', $vehicle_id);
        $lot_number = ($lot_number !== false && $lot_number !== '') ? (string) $lot_number : null;

        $auction_name = null;
        $auction_id = null;

        if ($auction_object) {

            $auction_id = $auction_object->ID;

            if ($auction_id > 0) {
                $auction_name = hh_get_auction_title_by_sale_number($auction_id);
                if ($auction_name === '') {
                    error_log('[HH Tracking] Auction title not found. sale_number=' . $auction_id);
                }
            }
        }

        // ✅ lot_id es el ID del vehicle CPT
        $lot_id = $vehicle_id;

        $lot_name = $vehicle_id ? get_the_title($vehicle_id) : null;

        try {
            $service = new ConditionReportRequestService();
            $service->create(
                $gf_entry_id,
                $created_at,
                $assigned_user_id,
                $auction_id,
                $lot_id,
                $lot_name,
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

/**
 * Devuelve el título del post "auction" cuyo ACF 'sale_number' coincide con $saleNumber.
 * Cachea resultados para no repetir queries.
 */
function hh_get_auction_title_by_sale_number($auction_id): string
{
    static $cache = [];

    $auction_id = (int) $auction_id;
    if ($auction_id <= 0) return '';

    if (!isset($cache[$auction_id])) {
        $cache[$auction_id] = get_post_status($auction_id) === 'publish'
            ? (string) get_the_title($auction_id)
            : '';
    }

    return $cache[$auction_id];
}