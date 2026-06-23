<?php
// tracking/src/hooks/excel-export.php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

add_action('admin_post_hh_export_condition_report_requests', function () {

    if (!current_user_can(HH_TRACKING_VIEW_CAP)) {
        wp_die('Forbidden', 403);
    }

    check_admin_referer('hh_export_condition_report_requests');

    // ✅ Composer autoload
    $autoload = __DIR__ . '/../../libs/phpspreadsheet/vendor/autoload.php';
    if (!file_exists($autoload)) {
        wp_die('PhpSpreadsheet autoload not found.', 500);
    }
    require_once $autoload;

    global $wpdb;
    $table = $wpdb->prefix . 'hh_condition_requests';
    $rows  = $wpdb->get_results("SELECT * FROM {$table} ORDER BY created_at DESC");

    /* ================= Helpers ================= */

    $fmtDate = static function ($dt) {
        if (!$dt) return '';
        $ts = strtotime($dt);
        return $ts ? date_i18n('Y-m-d H:i:s', $ts) : '';
    };

    $yesNo = static fn($v) => ((int)$v === 1) ? 'Yes' : 'No';

    $getLotNumber = static function (int $lot_id): string {
        if (!$lot_id) return '';
        return (string) get_post_meta($lot_id, 'lot_number_latest', true);
    };

    $getLotName = static function (int $lot_id): string {
        if (!$lot_id) return '';
        $p = get_post($lot_id);
        if (!$p) return '';
        // opcional: validar post_type
        // if ($p->post_type !== 'vehicles') return '';
        return (string) get_the_title($lot_id);
    };

    $getAuctionName = static function (int $lot_id): string {
        if (!$lot_id) return '';

        // OJO: esto es post ID
        $auction_id = get_post_meta($lot_id, 'auction_number_latest', true);
        if ($auction_id === '' || $auction_id === null) return '';

        return get_auction_title_by_sale_number($auction_id);
    };

    $getFinalSalePrice = static function (int $lot_id): string {
        if (!$lot_id) return '';
        return (string) get_post_meta($lot_id, 'sold_price', true);
    };

    /* ================= Spreadsheet ================= */

    $headers = [
        'N°',
        'Status',
        'Submitted At',
        'Lot Number',
        'Lot Name',
        'Auction Name',
        'Vehicle Year',
        'Vehicle Make',
        'Vehicle Model',
        'Contributed to Successful Sale',
        'Final Sale Price',
        'Assigned Staff Member',
    ];

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Condition Report Requests');

    /* ---------- Header ---------- */
    foreach ($headers as $i => $header) {
        $cell = Coordinate::stringFromColumnIndex($i + 1) . '1';
        $sheet->setCellValue($cell, $header);
    }

    $lastCol = Coordinate::stringFromColumnIndex(count($headers));
    $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
    $sheet->freezePane('A2');
    $sheet->setAutoFilter("A1:{$lastCol}1");

    /* ---------- Data ---------- */
    $rowNum = 2;
    $n = 1;

    foreach ($rows as $row) {

        $lot_id = (int) ($row->lot_id ?? 0);

        $assigned_user = '';
        if (!empty($row->assigned_user_id)) {
            $u = get_user_by('id', (int)$row->assigned_user_id);
            $assigned_user = $u ? $u->display_name : '';
        }

        $sale_yes = (int) ($row->contributed_to_sale ?? 0);

        $data = [
            $n,
            (string) ($row->status ?? ''),
            $fmtDate($row->created_at ?? ''),
            $getLotNumber($lot_id),
            $getLotName($lot_id),
            $getAuctionName($lot_id),
            (string) ($row->lot_year ?? ''),
            (string) ($row->lot_make ?? ''),
            (string) ($row->lot_model ?? ''),
            $yesNo($sale_yes),
            $sale_yes ? $getFinalSalePrice($lot_id) : '',
            $assigned_user,
        ];

        foreach ($data as $i => $value) {
            $cell = Coordinate::stringFromColumnIndex($i + 1) . $rowNum;
            $sheet->setCellValue($cell, $value);
        }

        $rowNum++;
        $n++;
    }

    /* ---------- Auto width ---------- */
    for ($i = 1; $i <= count($headers); $i++) {
        $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
    }

    /* ---------- Output ---------- */
    $filename = 'condition-report-requests-' . date('Y-m-d_H-i') . '.xlsx';

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

function get_auction_title_by_sale_number($saleNumber): string
{
    $auction_id = (int) $saleNumber;
    if ($auction_id <= 0) {
        return '';
    }

    static $cache = [];
    if (isset($cache[$auction_id])) {
        return $cache[$auction_id];
    }

    $post = get_post($auction_id);

    if (!$post || $post->post_type !== 'auction' || $post->post_status !== 'publish') {
        // opcional debug
        // error_log('[HH Tracking] Auction not found or invalid ID=' . $auction_id);
        return $cache[$auction_id] = '';
    }

    return $cache[$auction_id] = (string) get_the_title($auction_id);
}