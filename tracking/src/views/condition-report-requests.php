<?php

function hh_condition_report_page()
{
    if (!current_user_can(HH_TRACKING_VIEW_CAP)) {
        wp_die('You do not have permission to access this page.');
    }

    global $wpdb;

    $current_user_id = get_current_user_id();
    $form_id = (int) get_option('hh_cond_form_id', 0);
    $table   = $wpdb->prefix . 'hh_condition_requests';

    $columns = [
        'new' => [
            'title' => 'New',
            'desc'  => 'Form submitted, not yet reviewed',
        ],
        'in_progress' => [
            'title' => 'In Progress',
            'desc'  => 'Specialist preparing the condition report / gathering required information',
        ],
        'completed' => [
            'title' => 'Completed',
            'desc'  => 'Condition report finalised and shared with the client',
        ],
    ];

    // Si es Member Team → no ve la columna "New"
    if (current_user_can(HH_TRACKING_VIEW_JUST_MT)) {
        unset($columns['new']);
    }

    $rows = $wpdb->get_results("SELECT * FROM {$table} ORDER BY created_at DESC");
    if (!is_array($rows)) $rows = [];

    $grouped = [
        'new' => [],
        'in_progress' => [],
        'completed' => [],
    ];

    foreach ($rows as $r) {
        $status = isset($r->status) ? (string) $r->status : 'new';
        if (!isset($grouped[$status])) $status = 'new';
        $grouped[$status][] = $r;
    }

    $format_date = function ($dt) {
        if (empty($dt)) return '—';
        $ts = strtotime($dt);
        if (!$ts) return '—';
        return date_i18n('M j, Y', $ts);
    };

    // Nonce para AJAX
    $nonce = wp_create_nonce('hh_condition_request_update');

?>
    <div class="wrap">
        <div class="hh-topbar">
            <div>
                <h1>Condition Report Requests</h1>
                <p class="description">
                    Leads stored in <code><?php echo esc_html($table); ?></code>
                    <?php if ($form_id > 0): ?>
                        (linked Gravity Form ID: <?php echo esc_html($form_id); ?>)
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="hh-board">

            <?php foreach ($columns as $key => $col): ?>
                <?php $items = $grouped[$key] ?? []; ?>

                <section class="hh-col" data-col="<?php echo esc_attr($key); ?>">
                    <header class="hh-col__header">
                        <h2 class="hh-col__title"><?php echo esc_html($col['title']); ?></h2>
                        <span class="hh-col__count"><?php echo esc_html(count($items)); ?></span>
                    </header>

                    <p class="hh-col__desc"><?php echo esc_html($col['desc']); ?></p>

                    <div class="hh-col__list">
                        <?php if (!empty($items)) : ?>
                            <?php foreach ($items as $r) :

                                $lot_title = '';
                                $assigned_user_name = '';

                                $lot_id  = isset($r->lot_id) ? (int) $r->lot_id : 0;

                                if (!empty($lot_id) && $lot_id > 0) {
                                    $vehicle_post = get_post($lot_id);

                                    if ($vehicle_post && $vehicle_post->post_type === 'vehicles') {
                                        $lot_title = get_the_title($lot_id);
                                    }
                                }

                                $request_id  = isset($r->id) ? (int) $r->id : 0;
                                $gf_entry_id = isset($r->gf_entry_id) ? (int) $r->gf_entry_id : 0;
                                $date        = $format_date($r->created_at ?? '');

                                $assigned_user_id = isset($r->assigned_user_id) ? (int) $r->assigned_user_id : 0;

                                $lot_year  = isset($r->lot_year) ? (string) $r->lot_year : '';
                                $lot_make  = isset($r->lot_make) ? (string) $r->lot_make : '';
                                $lot_model = isset($r->lot_model) ? (string) $r->lot_model : '';

                                // $lot_title = trim($lot_make . ' ' . $lot_model);
                                if ($lot_title === '') $lot_title = 'Condition Report Request';

                                $url = '#';
                                if ($form_id > 0 && $gf_entry_id > 0) {
                                    $url = admin_url('admin.php?page=gf_entries&view=entry&id=' . $form_id . '&lid=' . $gf_entry_id);
                                }


                                if (!empty($assigned_user_id) && $assigned_user_id > 0) {
                                    $assigned_user = get_user_by('id', (int) $assigned_user_id);

                                    if ($assigned_user) {
                                        $assigned_user_name = $assigned_user->display_name;
                                    }
                                }

                                $is_assigned_to_me = ($current_user_id > 0 && (int)$assigned_user_id === (int)$current_user_id);

                            ?>
                                <div class="hh-card hh-card--link" data-request-id="<?php echo esc_attr($request_id); ?>" data-nonce="<?php echo esc_attr($nonce); ?>">
                                    <p class="hh-card__title">
                                        <b><?php echo esc_html($lot_title); ?></b>
                                        <hr>
                                        <?php if (!empty($assigned_user_name) && $key != 'new') : ?>
                                            <small style="display:block;margin:0;">
                                                <b>Assigned to: <?php echo esc_html($assigned_user_name); ?></b>
                                            </small>
                                        <?php endif; ?>

                                        <?php if (!empty($lot_make)) : ?>
                                            <small style="display:block;">Make: <?php echo esc_html($lot_make); ?></small>
                                        <?php endif; ?>
                                        <?php if (!empty($lot_model)) : ?>
                                            <small style="display:block;">Model: <?php echo esc_html($lot_model); ?></small>
                                        <?php endif; ?>
                                        <?php if (!empty($lot_year)) : ?>
                                            <small style="display:block;">Year: <?php echo esc_html($lot_year); ?></small>
                                        <?php endif; ?>
                                    </p>

                                    <div class="hh-card__meta">
                                        <a class="hh-pill" data-id="<?php echo esc_html($gf_entry_id ?: '—'); ?>" href="<?php echo esc_url($url); ?>" target="_blank">
                                            View Entry
                                        </a>

                                        <span class="hh-pill">Date: <?php echo esc_html($date); ?></span>

                                        <?php if ($key === 'new') : ?>
                                            <button type="button"
                                                class="hh-pill hh-4-state hh-pass-in-progress"
                                                data-request-id="<?php echo esc_attr($request_id); ?>">
                                                Pass to In Progress
                                            </button>
                                        <?php endif; ?>

                                        <?php if ($key === 'in_progress' && $is_assigned_to_me) : ?>
                                            <button type="button"
                                                class="hh-pill hh-4-state hh-pass-completed"
                                                data-request-id="<?php echo esc_attr($request_id); ?>">
                                                Pass to Completed
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endforeach; ?>

        </div>
    </div>

    <!-- Modal -->
    <div id="hh-cr-modal" class="hh-modal" style="display:none;">
        <div class="hh-modal__backdrop"></div>
        <div class="hh-modal__panel" role="dialog" aria-modal="true" aria-labelledby="hh-cr-modal-title">
            <h2 id="hh-cr-modal-title" style="margin-top:0;">Assign specialist</h2>
            <p class="description" style="margin-top:-6px;">
                Select a user to assign. If you don’t select one, it will remain in <strong>New</strong>.
            </p>

            <input type="hidden" id="hh-cr-request-id" value="0" />

            <label for="hh-cr-assigned-user" style="display:block; margin: 10px 0 6px;">
                Assigned user
            </label>
            <select id="hh-cr-assigned-user" class="regular-text">
                <option value="0">— Select user —</option>
                <?php foreach (HH_TEAM_USERS as $u): ?>
                    <option value="<?php echo (int) $u->ID; ?>">
                        <?php echo esc_html($u->display_name . ' (' . $u->user_login . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div style="margin-top:14px; display:flex; gap:8px; justify-content:flex-end;">
                <button type="button" class="button" id="hh-cr-cancel">Cancel</button>
                <button type="button" class="button button-primary" id="hh-cr-save">Save</button>
            </div>

            <p id="hh-cr-msg" style="margin:10px 0 0; color:#b32d2e; display:none;"></p>
        </div>
    </div>
<?php
}
