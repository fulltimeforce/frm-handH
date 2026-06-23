<?php

require_once __DIR__ . '/../services/EvaluationRequestService.php';

function hh_evaluation_requests_page()
{
    if (!current_user_can(HH_TRACKING_VIEW_CAP)) {
        wp_die('You do not have permission to access this page.');
    }

    global $wpdb;

    $form_id = (int) get_option('hh_eval_form_id', 0);
    $table   = $wpdb->prefix . 'hh_eval_requests';
    $current_user_id = get_current_user_id();
    $current_sales_manager = (int) get_option('hh_sales_manager_user_id', 0);

    // Nonce para AJAX (Evaluation)
    $nonce = wp_create_nonce('hh_eval_request_update');

    $columns = [
        'new' => [
            'title' => 'New',
            'desc'  => 'Form submitted, not yet reviewed',
        ],
        'client_contacted' => [
            'title' => 'Client Contacted',
            'desc'  => 'Initial contact made with the client',
        ],
        'assigned' => [
            'title' => 'Assigned to Specialist',
            'desc'  => 'Assigned to a specialist; specialist’s name included',
        ],
        'under_review' => [
            'title' => 'Under Review',
            'desc'  => 'Vehicle evaluation and assessing whether the vehicle is suitable for our auctions',
        ],
        'consignment_confirmed' => [
            'title' => 'Consignment Confirmed',
            'desc'  => 'Vehicle accepted for consignment',
        ],
        'not_consigned' => [
            'title' => 'Not Consigned',
            'desc'  => 'Vehicle not accepted; reason recorded',
        ],
        'in_progress' => [
            'title' => 'In Progress',
            'desc'  => 'Consignment details being finalized',
        ],
        'finalised' => [
            'title' => 'Finalised',
            'desc'  => 'Process complete (Entry Form signed by client and received by the specialist)',
        ],
    ];

    // Si es Member Team → no ve la columna "New"
    if ((current_user_can(HH_TRACKING_VIEW_JUST_MT))) {
        unset($columns['new']);
        unset($columns['client_contacted']);
    }

    $service = new EvaluationRequestService();
    $rows = $service->getAll();

    if (!is_array($rows)) $rows = [];

    // Agrupar por status
    $grouped = [];
    foreach ($columns as $statusKey => $_) {
        $grouped[$statusKey] = [];
    }

    foreach ($rows as $r) {
        $status = isset($r->status) ? (string) $r->status : 'new';

        // Si viene cualquier status raro, lo mandamos a new
        if (!isset($grouped[$status])) {
            $status = 'new';
        }

        $grouped[$status][] = $r;
    }

    $format_date = function ($dt) {
        if (empty($dt)) return '—';
        $ts = strtotime($dt);
        if (!$ts) return '—';
        return date_i18n('M j, Y', $ts);
    };

?>
    <div class="wrap">
        <div class="hh-topbar">
            <div>
                <h1>Evaluation Requests</h1>
                <p class="description">
                    Leads stored in <code><?php echo esc_html($table); ?></code>
                    <?php if ($form_id > 0): ?>
                        (linked Gravity Form ID: <?php echo esc_html($form_id); ?>)
                    <?php endif; ?>
                </p>
            </div>
            <div>
            	<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin: 10px 0 0;" class="<?php if (empty($rows)){echo 'disabled';} ?>">
                	<input type="hidden" name="action" value="hh_export_evaluation_requests">
                    <?php wp_nonce_field('hh_export_evaluation_requests'); ?>
					<button type="submit" class="button button-primary">
                    	Export Leads (Excel)
					</button>
				</form>
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
                        <?php if (empty($items)) : ?>
                            <!-- empty -->
                        <?php else : ?>
                            <?php foreach ($items as $r):

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

                                $lot_valuation = isset($r->lot_valuation) ? (string) $r->lot_valuation : '';
                                $sold          = isset($r->sold) ? (int) $r->sold : 0;
                                $sold_price    = isset($r->sold_price) ? (string) $r->sold_price : '';
                                $created_at    = isset($r->created_at) ? (string) $r->created_at : '';

                                $recommended_auction_id = isset($r->recommended_auction_id) ? (int) $r->recommended_auction_id : 0;
                                $auction_title = '';
                                if ($recommended_auction_id > 0) {
                                    $auction_post = get_post($recommended_auction_id);
                                    if ($auction_post && $auction_post->post_type === 'auction') {
                                        $auction_title = get_the_title($recommended_auction_id);
                                    }
                                }

                                $not_consigned_reason = isset($r->not_consigned_reason) ? (string) $r->not_consigned_reason : '';

                                // $lot_title = trim($lot_make . ' ' . $lot_model);
                                if ($lot_title === '') $lot_title = 'Evaluation Request';

                                // link a GF entry
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
                                <?php if ($is_assigned_to_me || current_user_can(HH_TRACKING_ADMIN_CAP)): ?>
                                    <div class="hh-card hh-card--link"
                                        data-request-id="<?php echo esc_attr($request_id); ?>"
                                        data-nonce="<?php echo esc_attr($nonce); ?>"

                                        data-lot-make="<?php echo esc_attr($lot_make); ?>"
                                        data-lot-model="<?php echo esc_attr($lot_model); ?>"
                                        data-lot-year="<?php echo esc_attr($lot_year); ?>"
                                        data-lot-valuation="<?php echo esc_attr($lot_valuation); ?>"

                                        data-sold="<?php echo esc_attr($sold); ?>"
                                        data-sold-price="<?php echo esc_attr($sold_price); ?>"

                                        data-assigned-user="<?php echo esc_attr($assigned_user_name); ?>"
                                        data-created-at="<?php echo esc_attr($created_at); ?>"

                                        data-recommended-auction-id="<?php echo esc_attr($recommended_auction_id); ?>"
                                        data-recommended-auction-title="<?php echo esc_attr($auction_title); ?>"

                                        data-not-consigned-reason="<?php echo esc_attr($not_consigned_reason); ?>">


                                        <p class="hh-card__title">
                                            <b><?php echo esc_html($lot_title); ?></b>
                                            <hr>
                                            <?php if (!empty($assigned_user_name)) : ?>
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
                                            <a class="hh-pill" data-id="<?php echo esc_attr($gf_entry_id ?: '—'); ?>" href="<?php echo esc_url($url); ?>" target="_blank">
                                                View Entry
                                            </a>

                                            <span class="hh-pill">Date: <?php echo esc_html($date); ?></span>

                                            <?php if ($key === 'new') : ?>
                                                <button type="button"
                                                    class="hh-pill hh-4-state hh-eval-pass-client-contacted"
                                                    data-request-id="<?php echo esc_attr($request_id); ?>">
                                                    Move to Client Contacted
                                                </button>
                                            <?php endif; ?>

                                            <?php if ($key === 'client_contacted') : ?>
                                                <button type="button"
                                                    class="hh-pill hh-4-state hh-eval-pass-assigned"
                                                    data-request-id="<?php echo esc_attr($request_id); ?>">
                                                    Move to Assigned to Specialist
                                                </button>
                                            <?php endif; ?>

                                            <?php if ($is_assigned_to_me): ?>
                                                <?php if ($key === 'assigned') : ?>
                                                    <button type="button"
                                                        class="hh-pill hh-4-state hh-eval-pass-under-review"
                                                        data-request-id="<?php echo esc_attr($request_id); ?>">
                                                        Move to Under Review
                                                    </button>
                                                <?php endif; ?>

                                                <?php if ($key === 'under_review') : ?>
                                                    <button type="button"
                                                        class="hh-pill hh-4-state hh-eval-consignment-decision"
                                                        data-request-id="<?php echo esc_attr($request_id); ?>">
                                                        Move to Consignment Decision
                                                    </button>
                                                <?php endif; ?>

                                                <?php if ($key === 'consignment_confirmed') : ?>
                                                    <button type="button"
                                                        class="hh-pill hh-4-state hh-eval-pass-in-progress"
                                                        data-request-id="<?php echo esc_attr($request_id); ?>">
                                                        Move to In Progress
                                                    </button>
                                                <?php endif; ?>

                                                <?php if ($key === 'in_progress') : ?>
                                                    <button type="button"
                                                        class="hh-pill hh-4-state hh-eval-pass-finalised"
                                                        data-request-id="<?php echo esc_attr($request_id); ?>">
                                                        Move to Finalised
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                            <?php if (in_array($key, ['consignment_confirmed', 'not_consigned', 'in_progress', 'finalised'], true)) : ?>
                                                <button type="button" class="hh-lead-details-btn" aria-label="Lead Details">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">
                                                        <path d="M384 224v184a40 40 0 01-40 40H104a40 40 0 01-40-40V168a40 40 0 0140-40h167.48M336 64h112v112M224 288L440 72" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" />
                                                    </svg>
                                                </button>
                                            <?php endif; ?>

                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

            <?php endforeach; ?>

        </div>
    </div>

    <!-- Modal: Assign specialist (Client Contacted -> Assigned) -->
    <div id="hh-eval-modal" class="hh-modal" style="display:none;">
        <div class="hh-modal__backdrop"></div>
        <div class="hh-modal__panel" role="dialog" aria-modal="true" aria-labelledby="hh-eval-modal-title">
            <h2 id="hh-eval-modal-title" style="margin-top:0;">Assign specialist</h2>
            <p class="description" style="margin-top:-6px;">
                Select a user to assign. If you don’t select one, it will remain in <strong>Client Contacted</strong>.
            </p>

            <input type="hidden" id="hh-eval-request-id" value="0" />

            <label for="hh-eval-assigned-user" style="display:block; margin: 10px 0 6px;">
                Assigned user
            </label>

            <select id="hh-eval-assigned-user" class="regular-text">
                <option value="0">— Select user —</option>
                <?php foreach (HH_TEAM_USERS as $u): ?>
                    <option value="<?php echo (int) $u->ID; ?>">
                        <?php echo esc_html($u->display_name . ' (' . $u->user_login . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div style="margin-top:14px; display:flex; gap:8px; justify-content:flex-end;">
                <button type="button" class="button" id="hh-eval-cancel">Cancel</button>
                <button type="button" class="button button-primary" id="hh-eval-save">Save</button>
            </div>

            <p id="hh-eval-msg" style="margin:10px 0 0; color:#b32d2e; display:none;"></p>
        </div>
    </div>

    <!-- Modal: Consignment decision (Under Review -> Confirmed / Not Consigned) -->
    <div id="hh-eval-consignment-modal" class="hh-modal" style="display:none;">
        <div class="hh-modal__backdrop"></div>

        <div class="hh-modal__panel" role="dialog" aria-modal="true" aria-labelledby="hh-eval-consignment-title">
            <h2 id="hh-eval-consignment-title" style="margin-top:0;">Consignment decision</h2>
            <p class="description" style="margin-top:-6px;">
                Is the vehicle accepted for consignment?
            </p>

            <input type="hidden" id="hh-eval-consignment-request-id" value="0" />
            <input type="hidden" id="hh-eval-consignment-accepted" value="1" />

            <div style="display:flex; gap:8px; margin-top:12px;">
                <button type="button" class="button" id="hh-eval-consignment-yes">Yes</button>
                <button type="button" class="button" id="hh-eval-consignment-no">No</button>
            </div>

            <div id="hh-eval-consignment-reason-wrap" style="display:none; margin-top:12px;">
                <label for="hh-eval-consignment-reason" style="display:block; margin: 0 0 6px;">
                    Reason (required)
                </label>
                <textarea id="hh-eval-consignment-reason" class="large-text" rows="4" placeholder="Write the reason..."></textarea>
            </div>

            <div id="hh-eval-consignment-yes-fields" style="display:none; margin-top:12px;">
                <label for="hh-eval-lot-valuation" style="display:block; margin: 0 0 6px;">
                    Lot valuation (required)
                </label>
                <input
                    type="text"
                    id="hh-eval-lot-valuation"
                    class="regular-text"
                    placeholder="e.g. 15000.00"
                    style="width: 100%;" />

                <label for="hh-eval-recommended-auction" style="display:block; margin: 12px 0 6px;">
                    Recommended auction (required)
                </label>

                <input
                    type="text"
                    id="hh-eval-recommended-auction"
                    class="regular-text"
                    placeholder="Start typing to search auctions..."
                    autocomplete="off"
                    style="width:100%;" />

                <input type="hidden" id="hh-eval-recommended-auction-id" value="0" />

            </div>

            <div style="margin-top:14px; display:flex; gap:8px; justify-content:flex-end;">
                <button type="button" class="button" id="hh-eval-consignment-cancel">Cancel</button>
                <button type="button" class="button button-primary" id="hh-eval-consignment-save">Save</button>
            </div>

            <p id="hh-eval-consignment-msg" style="margin:10px 0 0; color:#b32d2e; display:none;"></p>
        </div>
    </div>

    <!-- Modal: Finalise (In Progress -> Finalised) -->
    <div id="hh-eval-finalise-modal" class="hh-modal" style="display:none;">
        <div class="hh-modal__backdrop"></div>

        <div class="hh-modal__panel" role="dialog" aria-modal="true" aria-labelledby="hh-eval-finalise-title">
            <h2 id="hh-eval-finalise-title" style="margin-top:0;">Finalise request</h2>

            <p class="description" style="margin-top:-6px;">
                Search and select the <strong>Vehicle</strong> to attach to this request.
            </p>

            <input type="hidden" id="hh-eval-finalise-request-id" value="0" />
            <input type="hidden" id="hh-eval-finalise-vehicle-id" value="0" />

            <label for="hh-eval-finalise-vehicle-search" style="display:block; margin: 10px 0 6px;">
                Vehicle (type to search)
            </label>

            <input
                type="text"
                id="hh-eval-finalise-vehicle-search"
                class="regular-text"
                placeholder="Start typing vehicle title..."
                autocomplete="off" style="width: 100%;" />

            <!-- <p class="description" style="margin:8px 0 0;">
                Selected ID: <code id="hh-eval-finalise-vehicle-preview">—</code>
            </p> -->

            <div style="margin-top:14px; display:flex; gap:8px; justify-content:flex-end;">
                <button type="button" class="button" id="hh-eval-finalise-cancel">Cancel</button>
                <button type="button" class="button button-primary" id="hh-eval-finalise-save">Save</button>
            </div>

            <p id="hh-eval-finalise-msg" style="margin:10px 0 0; color:#b32d2e; display:none;"></p>
        </div>
    </div>

    <!-- Modal: Lead Details -->
    <div id="hh-lead-details-modal" class="hh-modal" style="display:none;">
        <div class="hh-modal__backdrop"></div>

        <div class="hh-modal__panel" role="dialog" aria-modal="true" aria-labelledby="hh-lead-details-title">
            <h2 id="hh-lead-details-title" style="margin-top:0;">Lead Details</h2>

            <div id="hh-lead-details-body" style="margin-top:10px;"></div>

            <div style="margin-top:14px; display:flex; gap:8px; justify-content:flex-end;">
                <button type="button" class="button button-primary" id="hh-lead-details-close">Close</button>
            </div>
        </div>
    </div>

<?php
}
