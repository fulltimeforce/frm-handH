<?php
// tracking/src/hooks/excel-export-evaluation.php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

add_action('admin_post_hh_export_evaluation_requests', function () {

    if (!current_user_can(HH_TRACKING_VIEW_CAP)) {
        wp_die('Forbidden', 403);
    }

    check_admin_referer('hh_export_evaluation_requests');

    // Autoload PhpSpreadsheet
    $autoload = __DIR__ . '/../../libs/phpspreadsheet/vendor/autoload.php';
    if (!file_exists($autoload)) {
        wp_die('PhpSpreadsheet autoload not found.', 500);
    }
    require_once $autoload;

    global $wpdb;

    // Tabla real
    $table = $wpdb->prefix . 'hh_eval_requests';

    $rows = $wpdb->get_results("SELECT * FROM {$table} ORDER BY created_at DESC");

    if (!is_array($rows) || count($rows) === 0) {
        wp_die('No evaluation requests found to export.', 200);
    }

    /* ================= Helpers ================= */

    $fmtDate = static function ($dt) {
        if (!$dt) return '';
        $ts = strtotime($dt);
        return $ts ? date_i18n('Y-m-d H:i:s', $ts) : '';
    };

    $yesNo = static fn($v) => ((int)$v === 1) ? 'Yes' : 'No';

    $getAuctionTitle = static function ($auction_id): string {
        $auction_id = (int)$auction_id;
        return $auction_id ? (string) get_the_title($auction_id) : '';
    };

    $didConsign = static function ($status): string {
        $consignedStatuses = ['consignment_confirmed', 'in_progress', 'finalised'];
        return in_array((string)$status, $consignedStatuses, true) ? 'Yes' : 'No';
    };

    /* ================= Spreadsheet ================= */

    $headers = [
        'N°',
        'Submitted At',
        'Vehicle Year',
        'Vehicle Make',
        'Vehicle Model',
        'Fit For Auction (Yes/No)',
        'Vehicle Value (Lot Valuation)',
        'Not Consigned Reason',
        'Recommended Auction',
        'Status',
        'Assigned Staff Member',
        'Ultimately Resulted in Consignment (Yes/No)',
    ];

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Evaluation Requests');

    // Header row
    foreach ($headers as $i => $header) {
        $sheet->setCellValue(
            Coordinate::stringFromColumnIndex($i + 1) . '1',
            $header
        );
    }

    $lastCol = Coordinate::stringFromColumnIndex(count($headers));
    $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
    $sheet->freezePane('A2');
    $sheet->setAutoFilter("A1:{$lastCol}1");

    /* ================= Data ================= */

    $rowNum = 2;
    $counter = 1;

    foreach ($rows as $row) {
        $status = (string)($row->status ?? '');

        $assigned_user = '';
        if (!empty($row->assigned_user_id)) {
            $u = get_user_by('id', (int)$row->assigned_user_id);
            $assigned_user = $u ? $u->display_name : '';
        }

        $fit_for_auction = (int)($row->fit_for_auction ?? 0);
        if ($status === 'not_consigned') {
            $fit_for_auction = 0;
        }

        $recommended_id = (int)($row->recommended_auction_id ?? 0);

        $data = [
            $counter,                                   // N°
            $fmtDate($row->created_at ?? ''),
            (string)($row->lot_year ?? ''),
            (string)($row->lot_make ?? ''),
            (string)($row->lot_model ?? ''),
            $yesNo($fit_for_auction),
            (string)($row->lot_valuation ?? ''),
            (string)($row->not_consigned_reason ?? ''),
            $recommended_id ? $getAuctionTitle($recommended_id) : '',
            $status,
            $assigned_user,
            $didConsign($status),
        ];

        foreach ($data as $i => $value) {
            $sheet->setCellValue(
                Coordinate::stringFromColumnIndex($i + 1) . $rowNum,
                $value
            );
        }

        $rowNum++;
        $counter++;
    }

    // Auto-size columns
    for ($i = 1; $i <= count($headers); $i++) {
        $sheet->getColumnDimension(
            Coordinate::stringFromColumnIndex($i)
        )->setAutoSize(true);
    }

    /* ================= Output ================= */

    $filename = 'evaluation-requests-' . date('Y-m-d_H-i') . '.xlsx';

    while (ob_get_level()) {
        ob_end_clean();
    }

    nocache_headers();
    header('X-Content-Type-Options: nosniff');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    (new Xlsx($spreadsheet))->save('php://output');
    exit;
});