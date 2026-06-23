<?php

add_action('admin_menu', 'hh_add_tracking_menu');

function hh_add_tracking_menu()
{
    // Menú principal: visible para admin + member_team
    add_menu_page(
        'Tracking',
        'Tracking',
        HH_TRACKING_ADMIN_CAP,
        'hh-tracking',
        'hh_tracking_router', // 👈 en vez de dashboard directo
        'dashicons-media-document',
        26
    );

    // Submenu: Evaluation Requests (admin + member_team)
    add_submenu_page(
        'hh-tracking',
        'Evaluation Requests',
        'Evaluation Requests',
        HH_TRACKING_VIEW_CAP,
        'hh-evaluation-requests',
        'hh_evaluation_requests_page'
    );

    // Submenu: Condition Report Request (admin + member_team)
    add_submenu_page(
        'hh-tracking',
        'Condition Report Request',
        'Condition Report Request',
        HH_TRACKING_VIEW_CAP,
        'hh-condition-report-request',
        'hh_condition_report_page'
    );
}

add_action('init', function () {

    // Admin + Member Team pueden VER Tracking
    $roles = ['administrator', 'member_team'];

    foreach ($roles as $role_slug) {
        $role = get_role($role_slug);
        if ($role && !$role->has_cap(HH_TRACKING_VIEW_CAP)) {
            $role->add_cap(HH_TRACKING_VIEW_CAP);
        }
    }
});

add_action('init', function () {
    $role = get_role('member_team');
    if (!$role) return;

    // Solo lectura de entries
    $caps = [
        'gravityforms_view_entries',
        'gravityforms_view_entry_notes',
        'gravityforms_view_forms',
    ];

    foreach ($caps as $cap) {
        if (!$role->has_cap($cap)) {
            $role->add_cap($cap);
        }
    }
}, 20);

/**
 * Si entra un admin al menú principal -> Settings
 * Si entra un member_team -> Evaluation Requests (o lo que prefieras)
 */
function hh_tracking_router()
{
    if (current_user_can(HH_TRACKING_ADMIN_CAP)) {
        hh_tracking_dashboard();
        return;
    }

    // Para member_team (y cualquiera con HH_TRACKING_VIEW_CAP)
    hh_evaluation_requests_page();
}

/**
 * Enqueue CSS/JS solo para pantallas Tracking.
 */
add_action('admin_enqueue_scripts', function ($hook) {

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen) return;

    $allowed = [
        'toplevel_page_hh-tracking',
        'tracking_page_hh-evaluation-requests',
        'tracking_page_hh-condition-report-request',
    ];

    if (!in_array($screen->id, $allowed, true)) return;

    // CSS principal del board
    wp_enqueue_style(
        'hh-tracking-board',
        get_template_directory_uri() . '/tracking/assets/tracking-board.css',
        [],
        '1.0.0'
    );

    // JS solo para Condition Report Requests (modal + ajax)
    if ($screen->id === 'tracking_page_hh-condition-report-request') {
        wp_enqueue_script(
            'hh-condition-report-board',
            get_template_directory_uri() . '/tracking/assets/condition-report-board.js',
            [],
            '1.0.0',
            true
        );

        wp_enqueue_script(
            'hh-condition-report-lead-details',
            get_template_directory_uri() . '/tracking/assets/condition-report-lead-details.js',
            [],
            '1.0.0',
            true
        );
    }

    if ($screen->id === 'tracking_page_hh-evaluation-requests') {
        wp_enqueue_script('jquery-ui-autocomplete');

        wp_enqueue_script(
            'hh-evaluation-requests-board',
            get_template_directory_uri() . '/tracking/assets/evaluation-requests-board.js',
            ['jquery-ui-autocomplete',],
            '1.0.0',
            true
        );

        wp_enqueue_script(
            'hh-evaluation-lead-details',
            get_template_directory_uri() . '/tracking/assets/evaluation-lead-details.js',
            ['hh-evaluation-requests-board'],
            '1.0.0',
            true
        );

        wp_localize_script('hh-evaluation-requests-board', 'HH_EVAL', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'auctionSearchNonce' => wp_create_nonce('hh_auction_search'),
        ]);
    }
});

require_once __DIR__ . '/src/hooks/global-vars.php';
require_once __DIR__ . '/src/views/tracking-dashboard.php';
require_once __DIR__ . '/src/views/condition-report-requests.php';
require_once __DIR__ . '/src/views/evaluation-requests.php';
require_once __DIR__ . '/src/hooks/gravity-forms-hooks.php';
require_once __DIR__ . '/src/hooks/update_when_vehicle_save.php';
require_once __DIR__ . '/src/hooks/ajax/condition-report-requests-functions.php';
require_once __DIR__ . '/src/hooks/ajax/evaluation-requests-functions.php';

require_once __DIR__ . '/src/hooks/excel/export-condition.php';
require_once __DIR__ . '/src/hooks/excel/export-evaluation.php';