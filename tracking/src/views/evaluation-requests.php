<?php

function hh_evaluation_requests_page()
{
    if (!current_user_can(HH_TRACKING_VIEW_CAP)) {
        wp_die('You do not have permission to access this page.');
    }

    global $wpdb;

    $form_id = (int) get_option('hh_eval_form_id', 0);
    $table   = $wpdb->prefix . 'hh_eval_requests';

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

    // Traer leads desde BD (orden más reciente primero)
    $rows = $wpdb->get_results("SELECT * FROM {$table} ORDER BY created_at DESC");
    if (!is_array($rows)) {
        $rows = [];
    }

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

                                $gf_entry_id = isset($r->gf_entry_id) ? (int) $r->gf_entry_id : 0;
                                $date        = $format_date($r->created_at ?? '');

                                $lot_year  = isset($r->lot_year) ? (string) $r->lot_year : '';
                                $lot_make  = isset($r->lot_make) ? (string) $r->lot_make : '';
                                $lot_model = isset($r->lot_model) ? (string) $r->lot_model : '';

                                $title = trim($lot_make . ' ' . $lot_model);
                                if ($title === '') {
                                    $title = 'Evaluation Request';
                                }

                                // link a GF entry
                                $url = '#';
                                if ($form_id > 0 && $gf_entry_id > 0) {
                                    $url = admin_url('admin.php?page=gf_entries&view=entry&id=' . $form_id . '&lid=' . $gf_entry_id);
                                }

                            ?>
                                <div class="hh-card hh-card--link">
                                    <p class="hh-card__title">
                                        <?php echo esc_html($title); ?>
                                        <?php if (!empty($lot_year)) : ?>
                                            <small style="display:block;">Year: <?php echo esc_html($lot_year); ?></small>
                                        <?php endif; ?>
                                    </p>

                                    <div class="hh-card__meta">
                                        <a class="hh-pill" data-id="<?php echo esc_html($gf_entry_id ?: '—'); ?>" href="<?php echo esc_url($url); ?>" target="_blank">
                                            View Entry
                                        </a>
                                        <span class="hh-pill">Date: <?php echo esc_html($date); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

            <?php endforeach; ?>

        </div>
    </div>
<?php
}
