<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Vehicles Export Module (admin only).
 *
 * Tabs:
 *  1) FILTER BY AUCTION (XLSX)
 *  2) FILTER BY DATES   (XLSX - 2 steps: Filter -> Search)
 *  3) EXPORT IMAGES     (ZIP)
 *
 * Export XLSX:
 *  - Chunk download via hidden form + iframe target + cookie handshake
 *
 * Export URLs XLSX (hidden):
 *  - All published vehicles, 2 columns (permalink + lot_link), batched in memory
 *
 * Export Images ZIP:
 *  - Select ONE auction
 *  - Optional Lot range filter (supports: 2,3,15-20)
 *  - Image size: Large / Medium / Small (single select)
 *  - Images: All images / Main image only (single select)
 *  - Create auction lot image zip -> streams a ZIP
 */
final class Vehicles_Export_Module
{
    // ==================================================
    // Config
    // ==================================================
    private const AUCTION_POST_TYPE      = 'auction';
    private const VEHICLE_AUCTION_META   = 'auction_number_latest'; // vehicles meta (acf/meta)
    private const AUCTION_SALE_META      = 'sale_number';           // auction meta (acf/meta)
    private const AUCTION_DATE_META      = 'auction_date';          // auction date meta (acf/meta) - assumed Y-m-d H:i:s

    private const POST_TYPE   = 'vehicles';
    private const CAPABILITY  = 'manage_options';
    private const MENU_SLUG   = 'export-vehicles';

    private const NONCE_ACTION = 'vehicles_export_nonce';
    private const NONCE_FIELD  = 'vehicles_export_nonce_f';

    private const DEFAULT_CHUNK_SIZE = 500;
    private const MAX_EXPORT_ROWS    = 10000;

    private const PRIVATE_SALES_TOKEN = '__private_sales__';
    private const VEHICLE_TYPE_META   = 'type_of_vehicle';

    // ==================================================
    // Bootstrap
    // ==================================================
    public static function init(): void
    {
        add_action('admin_menu', [__CLASS__, 'register_menu']);
        add_action('admin_init', [__CLASS__, 'maybe_handle_export']);        // XLSX
        add_action('admin_init', [__CLASS__, 'maybe_handle_urls_export']); // URLs XLSX (all vehicles)
        add_action('admin_init', [__CLASS__, 'maybe_handle_images_export']); // ZIP

        // AJAX
        add_action('wp_ajax_vehicles_export_search', [__CLASS__, 'ajax_search']); // vehicles by sale_numbers (or all if empty)
        add_action('wp_ajax_vehicles_export_get_auctions_by_dates', [__CLASS__, 'ajax_get_auctions_by_dates']); // dates step 1 (filter)
    }

    // ==================================================
    // Hooks (Admin)
    // ==================================================
    public static function register_menu(): void
    {
        add_submenu_page(
            'edit.php?post_type=' . self::POST_TYPE,
            'Export Vehicles',
            'Export Vehicles',
            self::CAPABILITY,
            self::MENU_SLUG,
            [__CLASS__, 'render_page']
        );
    }

    // -------------------------
    // XLSX handler
    // -------------------------
    public static function maybe_handle_export(): void
    {
        if (!is_admin()) return;
        if (!current_user_can(self::CAPABILITY)) return;

        $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
        if (self::MENU_SLUG !== $page) return;

        if (empty($_POST['vehicles_export'])) return;

        check_admin_referer(self::NONCE_ACTION, self::NONCE_FIELD);

        self::handle_export();
    }

    // -------------------------
    // URLs XLSX handler (all published vehicles)
    // -------------------------
    public static function maybe_handle_urls_export(): void
    {
        if (!is_admin()) return;
        if (!current_user_can(self::CAPABILITY)) return;

        $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
        if (self::MENU_SLUG !== $page) return;

        if (empty($_POST['vehicles_export_urls'])) return;

        check_admin_referer(self::NONCE_ACTION, self::NONCE_FIELD);

        self::handle_urls_export();
    }

    // -------------------------
    // ZIP Images handler
    // -------------------------
    public static function maybe_handle_images_export(): void
    {
        if (!is_admin()) return;
        if (!current_user_can(self::CAPABILITY)) return;

        $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
        if (self::MENU_SLUG !== $page) return;

        if (empty($_POST['vehicles_export_images'])) return;

        check_admin_referer(self::NONCE_ACTION, self::NONCE_FIELD);

        self::handle_images_export();
    }

    // ==================================================
    // AJAX: vehicles count/chunks by selected auctions
    // - Supports empty selection => ALL vehicles
    // ==================================================
    public static function ajax_search(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_send_json_error(['message' => 'No permission.'], 403);
        }

        $nonce = isset($_POST['nonce']) ? (string) $_POST['nonce'] : '';
        if (!wp_verify_nonce($nonce, self::NONCE_ACTION)) {
            wp_send_json_error(['message' => 'Invalid nonce.'], 400);
        }

        $auction_ids = self::sanitize_auction_ids($_POST['sale_numbers'] ?? []);
        $total = self::count_total_posts($auction_ids);

        wp_send_json_success([
            'total'  => (int) $total,
            'chunks' => self::build_chunks((int) $total, self::DEFAULT_CHUNK_SIZE),
        ]);
    }

    // ==================================================
    // AJAX: dates step 1 (Filter) -> returns auctions list
    // - start/end optional
    // - if both empty => returns ALL auctions
    // ==================================================
    public static function ajax_get_auctions_by_dates(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_send_json_error(['message' => 'No permission.'], 403);
        }

        $nonce = isset($_POST['nonce']) ? (string) $_POST['nonce'] : '';
        if (!wp_verify_nonce($nonce, self::NONCE_ACTION)) {
            wp_send_json_error(['message' => 'Invalid nonce.'], 400);
        }

        $start_raw = isset($_POST['date_start']) ? trim((string) $_POST['date_start']) : '';
        $end_raw   = isset($_POST['date_end']) ? trim((string) $_POST['date_end']) : '';

        $start = ($start_raw !== '') ? self::normalize_date_input_to_mysql_datetime($start_raw, 'start') : null;
        $end   = ($end_raw   !== '') ? self::normalize_date_input_to_mysql_datetime($end_raw, 'end')   : null;

        if (($start_raw !== '' && !$start) || ($end_raw !== '' && !$end)) {
            wp_send_json_error(['message' => 'Please select a valid date (or leave empty).'], 400);
        }

        if ($start && $end && strtotime($end) < strtotime($start)) {
            wp_send_json_error(['message' => 'End date must be after start date.'], 400);
        }

        $auctions = self::get_auctions_by_optional_date_range($start, $end);

        wp_send_json_success([
            'auctions' => $auctions, // [['auction_id'=>'238','label'=>'...'], ...]
        ]);
    }

    // ==================================================
    // UI
    // ==================================================
    public static function render_page(): void
    {
        $auction_options = self::get_auction_options();
        $nonce = wp_create_nonce(self::NONCE_ACTION);
?>
        <div class="wrap">
            <h1>Export Vehicles</h1>

            <p>
                Export Vehicles to an Excel (.xlsx) file.<br>
                <strong>No gallery data included in the XLSX except the Image URLs column.</strong>
            </p>

            <style>
                .vehx-chunks .button {
                    width: 60px;
                }

                .vehx-box {
                    max-width: 980px;
                }

                .vehx-tabs {
                    display: flex;
                    gap: 10px;
                    margin: 14px 0 16px;
                }

                .vehx-tab {
                    border: 1px solid #c3c4c7;
                    background: #fff;
                    padding: 8px 12px;
                    border-radius: 6px;
                    cursor: pointer;
                    user-select: none;
                }

                .vehx-tab.active {
                    border-color: #2271b1;
                    box-shadow: inset 0 0 0 1px #2271b1;
                    font-weight: 600;
                }

                .vehx-panel {
                    display: none;
                }

                .vehx-panel.active {
                    display: block;
                }

                .vehx-auctions {
                    display: flex;
                    gap: 16px;
                    align-items: flex-start;
                }

                .vehx-left {
                    flex: 1 1 auto;
                    min-width: 520px;
                }

                .vehx-right {
                    width: 220px;
                }

                .vehx-searchbar {
                    display: flex;
                    gap: 10px;
                    align-items: center;
                    margin: 8px 0 10px;
                }

                .vehx-searchbar input {
                    width: 100%;
                    max-width: 680px;
                }

                .vehx-list {
                    border: 1px solid #c3c4c7;
                    border-radius: 4px;
                    height: 280px;
                    overflow: auto;
                    background: #fff;
                    padding: 6px 0;
                }

                .vehx-item {
                    padding: 6px 10px;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }

                .vehx-item:hover {
                    background: #f6f7f7;
                }

                .vehx-tags {
                    margin-top: 10px;
                    display: flex;
                    flex-wrap: wrap;
                    gap: 8px;
                }

                .vehx-tag {
                    background: #f0f0f1;
                    border: 1px solid #dcdcde;
                    border-radius: 999px;
                    padding: 6px 10px;
                    display: flex;
                    gap: 8px;
                    align-items: center;
                    max-width: 100%;
                }

                .vehx-tag span {
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    max-width: 820px;
                }

                .vehx-tag button {
                    border: none;
                    background: transparent;
                    cursor: pointer;
                    font-size: 14px;
                    line-height: 1;
                }

                .vehx-actions {
                    margin-top: 16px;
                    display: flex;
                    gap: 10px;
                    align-items: flex-start;
                    flex-direction: column;
                }

                .vehx-results {
                    margin-top: 18px;
                    display: none;
                }

                .vehx-chunks {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 10px;
                    margin-top: 10px;
                }

                .vehx-loader {
                    position: fixed;
                    inset: 0;
                    background: rgba(0, 0, 0, .35);
                    z-index: 999999;
                    display: none;
                    align-items: center;
                    justify-content: center;
                }

                .vehx-loader__box {
                    background: #fff;
                    border-radius: 10px;
                    padding: 18px 22px;
                    min-width: 280px;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, .2);
                    text-align: center;
                }

                .vehx-date-row {
                    display: flex;
                    gap: 12px;
                    align-items: flex-end;
                    margin-top: 10px;
                    flex-wrap: wrap;
                }

                .vehx-date-row label {
                    display: block;
                    font-weight: 600;
                    margin-bottom: 6px;
                }

                .vehx-date-row input[type="date"] {
                    min-width: 220px;
                }

                .vehx-seg {
                    display: inline-flex;
                    border: 1px solid #c3c4c7;
                    border-radius: 6px;
                    overflow: hidden;
                    background: #fff;
                }

                .vehx-seg button {
                    border: 0;
                    background: #fff;
                    padding: 10px 16px;
                    cursor: pointer;
                    min-width: 120px;
                }

                .vehx-seg button+button {
                    border-left: 1px solid #c3c4c7;
                }

                .vehx-seg button.active {
                    background: #135e96;
                    color: #fff;
                    font-weight: 600;
                }

                .vehx-field {
                    margin-top: 14px;
                }

                .vehx-field label {
                    display: block;
                    font-weight: 600;
                    margin-bottom: 6px;
                }

                .vehx-input {
                    width: 100%;
                    max-width: 680px;
                }

                .vehx-help {
                    display: flex;
                    gap: 10px;
                    align-items: flex-start;
                    opacity: .85;
                    margin-top: 8px;
                }
            </style>

            <!-- Loader -->
            <div id="vehx-loader" class="vehx-loader">
                <div class="vehx-loader__box">
                    <span class="spinner is-active" style="float:none; margin:0 0 12px 0;"></span>
                    <div style="font-size:14px; font-weight:600;" id="vehx-loader-text">Loading…</div>
                    <div style="font-size:12px; opacity:.8; margin-top:6px;">Please don’t close this tab.</div>
                </div>
            </div>

            <iframe id="vehx-download-frame" name="vehx-download-frame" style="display:none;"></iframe>

            <div class="vehx-box">

                <!-- Tabs -->
                <div class="vehx-tabs">
                    <div class="vehx-tab active" data-tab="auction">Filter by Auction</div>
                    <div class="vehx-tab" data-tab="dates">Filter by Dates</div>
                    <div class="vehx-tab" data-tab="images">Export Images</div>
                </div>

                <!-- =======================
                     TAB 1: FILTER BY AUCTION
                     ======================= -->
                <div class="vehx-panel active" id="vehx-panel-auction">
                    <h2 class="title">Auctions</h2>

                    <div class="vehx-auctions">
                        <div class="vehx-left">
                            <div class="vehx-searchbar">
                                <input type="text" id="vehx-auction-filter" placeholder="Search…">
                            </div>

                            <div class="vehx-list" id="vehx-auction-list">
                                <div class="vehx-item" data-sale="<?php echo esc_attr(self::PRIVATE_SALES_TOKEN); ?>" data-label="Private Sales">
                                    Private Sales
                                </div>
                                <?php foreach ($auction_options as $auction_id => $label): ?>
                                    <div class="vehx-item"
                                        data-sale="<?php echo esc_attr((string) $auction_id); ?>"
                                        data-label="<?php echo esc_attr($label); ?>">
                                        <?php echo esc_html($label); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="vehx-tags" id="vehx-selected-tags"></div>

                            <div class="vehx-actions">
                                <button type="button" class="button button-primary" id="vehx-search-btn">Search</button>
                                <span id="vehx-status" style="opacity:.8;"></span>
                            </div>

                            <div class="vehx-results" id="vehx-results">
                                <h3 style="margin: 0 0 6px;">Download Chunk #:</h3>
                                <div class="vehx-chunks" id="vehx-chunk-buttons"></div>
                                <button type="button"
                                    id="vehx-export-all-urls-btn"
                                    class="button"
                                    style="display:none;">
                                    Export All URLs
                                </button>
                            </div>
                        </div>

                        <div class="vehx-right">
                            <form method="post" action="" id="vehx-download-form" target="vehx-download-frame" style="display:none;">
                                <?php wp_nonce_field(self::NONCE_ACTION, self::NONCE_FIELD); ?>
                                <input type="hidden" name="vehicles_export" value="1">
                                <input type="hidden" name="vehicles_export_mode" id="vehx-mode" value="auction">
                                <input type="hidden" name="vehicles_export_range" id="vehx-range" value="">
                                <select name="vehicles_export_auctions[]" id="vehx-auctions-hidden" multiple></select>

                                <!-- dates payload (only used for dates mode) -->
                                <input type="hidden" name="vehicles_export_date_start" id="vehx-date-start-hidden" value="">
                                <input type="hidden" name="vehicles_export_date_end" id="vehx-date-end-hidden" value="">
                            </form>

                            <!-- URLs XLSX form (all published vehicles) -->
                            <form method="post" action="" id="vehx-urls-export-form" target="vehx-download-frame" style="display:none;">
                                <?php wp_nonce_field(self::NONCE_ACTION, self::NONCE_FIELD); ?>
                                <input type="hidden" name="vehicles_export_urls" value="1">
                            </form>

                            <!-- Images ZIP form -->
                            <form method="post" action="" id="vehx-images-form" target="vehx-download-frame" style="display:none;">
                                <?php wp_nonce_field(self::NONCE_ACTION, self::NONCE_FIELD); ?>
                                <input type="hidden" name="vehicles_export_images" value="1">
                                <input type="hidden" name="vehx_images_sale_number" id="vehx-images-sale-number" value="">
                                <input type="hidden" name="vehx_images_lot_range" id="vehx-images-lot-range" value="">
                                <input type="hidden" name="vehx_images_size" id="vehx-images-size" value="large">
                                <input type="hidden" name="vehx_images_mode" id="vehx-images-mode" value="all"> <!-- all|main -->
                            </form>
                        </div>
                    </div>
                </div>

                <!-- =======================
                     TAB 2: FILTER BY DATES
                     ======================= -->
                <div class="vehx-panel" id="vehx-panel-dates">
                    <h2 class="title">Auction Dates</h2>

                    <div class="vehx-left" style="min-width:520px;">
                        <div class="vehx-date-row">
                            <div>
                                <label for="vehx-date-start">Start date (optional)</label>
                                <input type="date" id="vehx-date-start">
                            </div>
                            <div>
                                <label for="vehx-date-end">End date (optional)</label>
                                <input type="date" id="vehx-date-end">
                            </div>
                        </div>

                        <div class="vehx-actions">
                            <button type="button" class="button button-primary" id="vehx-filter-dates-btn">Filter</button>
                            <span id="vehx-dates-status" style="opacity:.8;"></span>
                        </div>

                        <div id="vehx-dates-auctions" style="display:none;">
                            <strong><br>Vehicles included in the following auctions:</strong>
                            <div id="vehx-dates-auctions-list" style="margin-top:6px; display:flex; flex-wrap:wrap; gap:8px;"></div>

                            <div class="vehx-actions">
                                <button type="button" class="button button-primary" id="vehx-search-dates-btn" style="display:none;">Search</button>
                            </div>
                        </div>

                        <div class="vehx-results" id="vehx-dates-results">
                            <h3 style="margin: 0 0 6px;">Download Chunk #:</h3>
                            <div class="vehx-chunks" id="vehx-dates-chunk-buttons"></div>
                        </div>
                    </div>
                </div>

                <!-- =======================
                     TAB 3: EXPORT IMAGES (ZIP)
                     ======================= -->
                <div class="vehx-panel" id="vehx-panel-images">
                    <h2 class="title">Export Images</h2>

                    <div class="vehx-left" style="min-width:520px;">
                        <div class="vehx-searchbar">
                            <input type="text" id="vehx-images-auction-filter" placeholder="Search…">
                        </div>

                        <div class="vehx-list" id="vehx-images-auction-list">
                            <div class="vehx-item" data-sale="<?php echo esc_attr(self::PRIVATE_SALES_TOKEN); ?>" data-label="Private Sales">
                                Private Sales
                            </div>
                            <?php foreach ($auction_options as $auction_id => $label): ?>
                                <div class="vehx-item"
                                    data-sale="<?php echo esc_attr((string) $auction_id); ?>"
                                    data-label="<?php echo esc_attr($label); ?>">
                                    <?php echo esc_html($label); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="vehx-tags" id="vehx-images-selected-tag"></div>

                        <div id="vehx-images-options" style="display:none; margin-top: 10px;">

                            <div class="vehx-field">
                                <div class="vehx-help">
                                    <span style="font-size:18px;">ⓘ</span>
                                    <div>
                                        The lot range can be either a comma separated list of lot numbers, or a hyphen separated range (or a combination of both).<br>
                                        e.g. <code>1,2,3,10-20,54</code>
                                    </div>
                                </div>

                                <label for="vehx-images-lot-range-input">Lot range</label>
                                <input class="vehx-input" type="text" id="vehx-images-lot-range-input" placeholder="">
                            </div>

                            <div class="vehx-field">
                                <label>Image size</label>
                                <div class="vehx-seg" id="vehx-images-size-seg">
                                    <button type="button" data-size="large" class="active">Large</button>
                                    <button type="button" data-size="medium">Medium</button>
                                    <button type="button" data-size="small">Small</button>
                                </div>
                            </div>

                            <div class="vehx-field">
                                <label>Images</label>
                                <div class="vehx-seg" id="vehx-images-mode-seg">
                                    <button type="button" data-mode="all" class="active">All images</button>
                                    <button type="button" data-mode="main">Main image only</button>
                                </div>
                            </div>

                            <div class="vehx-actions">
                                <button type="button" class="button button-primary" id="vehx-images-create-zip-btn">
                                    Create auction lot image zip
                                </button>
                                <span id="vehx-images-status" style="opacity:.8;"></span>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <script>
                (function() {
                    const ajaxUrl = "<?php echo esc_js(admin_url('admin-ajax.php')); ?>";
                    const nonce = "<?php echo esc_js($nonce); ?>";

                    // Tabs
                    const tabs = document.querySelectorAll('.vehx-tab');
                    const panelAuction = document.getElementById('vehx-panel-auction');
                    const panelDates = document.getElementById('vehx-panel-dates');
                    const panelImages = document.getElementById('vehx-panel-images');

                    // Shared XLSX download form
                    const hiddenSelect = document.getElementById('vehx-auctions-hidden');
                    const downloadForm = document.getElementById('vehx-download-form');
                    const urlsExportForm = document.getElementById('vehx-urls-export-form');
                    const urlsExportBtn = document.getElementById('vehx-export-all-urls-btn');
                    const rangeInput = document.getElementById('vehx-range');
                    const modeInput = document.getElementById('vehx-mode');
                    const dateStartHidden = document.getElementById('vehx-date-start-hidden');
                    const dateEndHidden = document.getElementById('vehx-date-end-hidden');

                    // Images ZIP form
                    const imagesForm = document.getElementById('vehx-images-form');
                    const imagesSaleNumber = document.getElementById('vehx-images-sale-number');
                    const imagesLotRange = document.getElementById('vehx-images-lot-range');
                    const imagesSize = document.getElementById('vehx-images-size');
                    const imagesMode = document.getElementById('vehx-images-mode');

                    const iframe = document.getElementById('vehx-download-frame');

                    // Loader
                    const loader = document.getElementById('vehx-loader');
                    const loaderText = document.getElementById('vehx-loader-text');

                    function getCookie(name) {
                        const parts = document.cookie.split(';').map(v => v.trim());
                        for (const p of parts) {
                            if (p.startsWith(name + '=')) return decodeURIComponent(p.substring(name.length + 1));
                        }
                        return '';
                    }

                    function deleteCookie(name) {
                        document.cookie = name + '=; Max-Age=0; path=/';
                    }

                    function waitForDownloadThenHideLoader() {
                        const startedAt = Date.now();
                        const maxWaitMs = 600000;
                        const t = setInterval(() => {
                            if (getCookie('vehx_file_download') === '1') {
                                clearInterval(t);
                                deleteCookie('vehx_file_download');
                                hideLoader();
                                return;
                            }
                            if (Date.now() - startedAt > maxWaitMs) {
                                clearInterval(t);
                                hideLoader();
                            }
                        }, 400);
                    }

                    function showLoader(text) {
                        loaderText.textContent = text || 'Loading…';
                        loader.style.display = 'flex';
                    }

                    function hideLoader() {
                        loader.style.display = 'none';
                    }

                    function escapeHtml(str) {
                        return String(str)
                            .replaceAll('&', '&amp;')
                            .replaceAll('<', '&lt;')
                            .replaceAll('>', '&gt;')
                            .replaceAll('"', '&quot;')
                            .replaceAll("'", "&#039;");
                    }

                    iframe.addEventListener('load', () => {
                        setTimeout(() => {
                            hideLoader();
                            try {
                                const doc = iframe.contentDocument || iframe.contentWindow.document;
                                const text = (doc && doc.body) ? (doc.body.innerText || '').trim() : '';
                                if (text) alert(text.substring(0, 300));
                            } catch (e) {}
                        }, 300);
                    });

                    // Tabs behavior
                    tabs.forEach(t => {
                        t.addEventListener('click', () => {
                            tabs.forEach(x => x.classList.remove('active'));
                            t.classList.add('active');

                            const which = t.getAttribute('data-tab');
                            panelAuction.classList.toggle('active', which === 'auction');
                            panelDates.classList.toggle('active', which === 'dates');
                            panelImages.classList.toggle('active', which === 'images');
                        });
                    });

                    // ==========================================================
                    // TAB 1: AUCTION SELECTION (allows empty => export all)
                    // ==========================================================
                    const filterInput = document.getElementById('vehx-auction-filter');
                    const listEl = document.getElementById('vehx-auction-list');
                    const tagWrap = document.getElementById('vehx-selected-tags');
                    const searchBtn = document.getElementById('vehx-search-btn');
                    const statusEl = document.getElementById('vehx-status');
                    const resultsBox = document.getElementById('vehx-results');
                    const chunksWrap = document.getElementById('vehx-chunk-buttons');

                    const selected = new Map(); // sale_number -> label

                    function syncHiddenSelectFromMap(map) {
                        hiddenSelect.innerHTML = '';
                        for (const [sale, label] of map.entries()) {
                            const opt = document.createElement('option');
                            opt.value = sale;
                            opt.selected = true;
                            opt.textContent = label;
                            hiddenSelect.appendChild(opt);
                        }
                    }

                    function renderTags() {
                        tagWrap.innerHTML = '';
                        for (const [sale, label] of selected.entries()) {
                            const tag = document.createElement('div');
                            tag.className = 'vehx-tag';
                            tag.innerHTML = `<span title="${escapeHtml(label)}">${escapeHtml(label)}</span>
                                <button type="button" aria-label="Remove">×</button>`;
                            tag.querySelector('button').addEventListener('click', () => {
                                selected.delete(sale);
                                syncHiddenSelectFromMap(selected);
                                renderTags();
                                clearResults();
                            });
                            tagWrap.appendChild(tag);
                        }
                    }

                    function clearResults() {
                        resultsBox.style.display = 'none';
                        const keep = urlsExportBtn;
                        chunksWrap.innerHTML = '';
                        if (keep) {
                            chunksWrap.appendChild(keep);
                        }
                    }

                    listEl.addEventListener('click', (e) => {
                        const item = e.target.closest('.vehx-item');
                        if (!item) return;

                        const sale = item.getAttribute('data-sale');
                        const label = item.getAttribute('data-label') || sale;
                        if (!sale) return;

                        if (!selected.has(sale)) {
                            selected.set(sale, label);
                            syncHiddenSelectFromMap(selected);
                            renderTags();
                            clearResults();
                        }
                    });

                    filterInput.addEventListener('input', () => {
                        const q = filterInput.value.trim().toLowerCase();
                        const items = listEl.querySelectorAll('.vehx-item');
                        items.forEach(el => {
                            const label = (el.getAttribute('data-label') || '').toLowerCase();
                            el.style.display = (!q || label.includes(q)) ? '' : 'none';
                        });
                    });

                    searchBtn.addEventListener('click', async () => {
                        modeInput.value = 'auction';
                        dateStartHidden.value = '';
                        dateEndHidden.value = '';

                        showLoader('Searching vehicles…');
                        statusEl.textContent = '';
                        clearResults();

                        try {
                            const sales = Array.from(selected.keys());
                            const formData = new FormData();
                            formData.append('action', 'vehicles_export_search');
                            formData.append('nonce', nonce);
                            sales.forEach(s => formData.append('sale_numbers[]', s));

                            const res = await fetch(ajaxUrl, {
                                method: 'POST',
                                credentials: 'same-origin',
                                body: formData
                            });
                            const json = await res.json();

                            if (!json || !json.success) {
                                const msg = (json && json.data && json.data.message) ? json.data.message : 'Search failed.';
                                throw new Error(msg);
                            }

                            const total = parseInt(json.data.total || 0, 10);
                            const chunks = json.data.chunks || [];

                            if (total <= 0 || !chunks.length) {
                                statusEl.textContent = selected.size ? 'No vehicles found for the selected auctions.' : 'No vehicles found.';
                                return;
                            }

                            statusEl.textContent = selected.size ? `Found ${total} vehicles.` : `Found ${total} vehicles (all auctions).`;

                            chunksWrap.innerHTML = '';
                            chunks.forEach((c, idx) => {
                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'button';
                                btn.textContent = String(idx + 1);

                                btn.addEventListener('click', () => {
                                    rangeInput.value = `${c.start}-${c.end}`;
                                    showLoader(`Generating XLSX (chunk ${idx + 1})…`);
                                    waitForDownloadThenHideLoader();
                                    downloadForm.submit();
                                });

                                chunksWrap.appendChild(btn);
                            });

                            if (urlsExportBtn) {
                                chunksWrap.appendChild(urlsExportBtn);
                            }

                            resultsBox.style.display = 'block';
                        } catch (err) {
                            statusEl.textContent = (err && err.message) ? err.message : 'Search failed.';
                        } finally {
                            hideLoader();
                        }
                    });

                    if (urlsExportBtn) {
                        urlsExportBtn.addEventListener('click', () => {
                            showLoader('Generating URLs export (all vehicles)…');
                            waitForDownloadThenHideLoader();
                            urlsExportForm.submit();
                        });
                    }

                    // ==========================================================
                    // TAB 2: DATE RANGE (2 steps: Filter -> Search)
                    // ==========================================================
                    const dateStart = document.getElementById('vehx-date-start');
                    const dateEnd = document.getElementById('vehx-date-end');

                    const filterDatesBtn = document.getElementById('vehx-filter-dates-btn');
                    const searchDatesBtn = document.getElementById('vehx-search-dates-btn');
                    const datesStatus = document.getElementById('vehx-dates-status');

                    const datesResultsBox = document.getElementById('vehx-dates-results');
                    const datesChunksWrap = document.getElementById('vehx-dates-chunk-buttons');

                    const datesAuctionsBox = document.getElementById('vehx-dates-auctions');
                    const datesAuctionsList = document.getElementById('vehx-dates-auctions-list');

                    const datesSelected = new Map(); // sale_number -> label

                    function clearDatesChunks() {
                        datesResultsBox.style.display = 'none';
                        datesChunksWrap.innerHTML = '';
                    }

                    function clearDatesAuctions() {
                        datesSelected.clear();
                        datesAuctionsBox.style.display = 'none';
                        datesAuctionsList.innerHTML = '';
                        searchDatesBtn.style.display = 'none';
                    }

                    function renderDatesAuctionTags() {
                        datesAuctionsList.innerHTML = '';
                        for (const [sale, label] of datesSelected.entries()) {
                            const tag = document.createElement('div');
                            tag.className = 'vehx-tag';
                            tag.innerHTML = `
                                <span title="${escapeHtml(label)}">${escapeHtml(label)}</span>
                                <button type="button" aria-label="Remove">×</button>
                            `;

                            tag.querySelector('button').addEventListener('click', () => {
                                datesSelected.delete(sale);
                                renderDatesAuctionTags();

                                if (datesSelected.size === 0) {
                                    searchDatesBtn.style.display = 'none';
                                    clearDatesChunks();
                                } else {
                                    clearDatesChunks();
                                }
                            });

                            datesAuctionsList.appendChild(tag);
                        }

                        datesAuctionsBox.style.display = datesSelected.size ? 'block' : 'none';
                    }

                    function syncHiddenSelectFromDatesSelected() {
                        hiddenSelect.innerHTML = '';
                        for (const [sale, label] of datesSelected.entries()) {
                            const opt = document.createElement('option');
                            opt.value = sale;
                            opt.selected = true;
                            opt.textContent = label;
                            hiddenSelect.appendChild(opt);
                        }
                    }

                    dateStart.addEventListener('change', () => {
                        datesStatus.textContent = '';
                        clearDatesChunks();
                        clearDatesAuctions();
                    });
                    dateEnd.addEventListener('change', () => {
                        datesStatus.textContent = '';
                        clearDatesChunks();
                        clearDatesAuctions();
                    });

                    // Step 1: FILTER
                    filterDatesBtn.addEventListener('click', async () => {
                        modeInput.value = 'dates';
                        dateStartHidden.value = dateStart.value || '';
                        dateEndHidden.value = dateEnd.value || '';

                        hiddenSelect.innerHTML = '';
                        datesStatus.textContent = '';
                        clearDatesChunks();
                        clearDatesAuctions();

                        showLoader('Filtering auctions…');

                        try {
                            const formData = new FormData();
                            formData.append('action', 'vehicles_export_get_auctions_by_dates');
                            formData.append('nonce', nonce);

                            if (dateStart.value) formData.append('date_start', dateStart.value);
                            if (dateEnd.value) formData.append('date_end', dateEnd.value);

                            const res = await fetch(ajaxUrl, {
                                method: 'POST',
                                credentials: 'same-origin',
                                body: formData
                            });
                            const json = await res.json();

                            if (!json || !json.success) {
                                const msg = (json && json.data && json.data.message) ? json.data.message : 'Filter failed.';
                                throw new Error(msg);
                            }

                            const auctions = json.data.auctions || [];
                            if (!auctions.length) {
                                datesStatus.textContent = 'No auctions found for that date filter.';
                                return;
                            }

                            auctions.forEach(a => {
                                if (!a || !a.auction_id) return;
                                datesSelected.set(String(a.auction_id), a.label || String(a.auction_id));
                            });

                            renderDatesAuctionTags();
                            searchDatesBtn.style.display = datesSelected.size ? 'inline-block' : 'none';
                            datesStatus.textContent = `Found ${datesSelected.size} auctions. You can remove any and then Search.`;

                        } catch (err) {
                            datesStatus.textContent = (err && err.message) ? err.message : 'Filter failed.';
                        } finally {
                            hideLoader();
                        }
                    });

                    // Step 2: SEARCH
                    searchDatesBtn.addEventListener('click', async () => {
                        if (datesSelected.size === 0) {
                            datesStatus.textContent = 'Please keep at least one auction before searching.';
                            return;
                        }

                        modeInput.value = 'dates';
                        dateStartHidden.value = dateStart.value || '';
                        dateEndHidden.value = dateEnd.value || '';

                        syncHiddenSelectFromDatesSelected();

                        clearDatesChunks();
                        datesStatus.textContent = '';

                        showLoader('Searching vehicles…');

                        try {
                            const sales = Array.from(datesSelected.keys());

                            const formData = new FormData();
                            formData.append('action', 'vehicles_export_search');
                            formData.append('nonce', nonce);
                            sales.forEach(s => formData.append('sale_numbers[]', s));

                            const res = await fetch(ajaxUrl, {
                                method: 'POST',
                                credentials: 'same-origin',
                                body: formData
                            });
                            const json = await res.json();

                            if (!json || !json.success) {
                                const msg = (json && json.data && json.data.message) ? json.data.message : 'Search failed.';
                                throw new Error(msg);
                            }

                            const total = parseInt(json.data.total || 0, 10);
                            const chunks = json.data.chunks || [];

                            if (total <= 0 || !chunks.length) {
                                datesStatus.textContent = 'No vehicles found for the selected auctions.';
                                return;
                            }

                            datesStatus.textContent = `Found ${total} vehicles.`;

                            datesChunksWrap.innerHTML = '';
                            chunks.forEach((c, idx) => {
                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'button';
                                btn.textContent = String(idx + 1);

                                btn.addEventListener('click', () => {
                                    rangeInput.value = `${c.start}-${c.end}`;
                                    showLoader(`Generating XLSX (chunk ${idx + 1})…`);
                                    waitForDownloadThenHideLoader();
                                    downloadForm.submit();
                                });

                                datesChunksWrap.appendChild(btn);
                            });

                            datesResultsBox.style.display = 'block';
                        } catch (err) {
                            datesStatus.textContent = (err && err.message) ? err.message : 'Search failed.';
                        } finally {
                            hideLoader();
                        }
                    });

                    // ==========================================================
                    // TAB 3: EXPORT IMAGES (ZIP)
                    // ==========================================================
                    const imagesAuctionFilter = document.getElementById('vehx-images-auction-filter');
                    const imagesAuctionList = document.getElementById('vehx-images-auction-list');
                    const imagesSelectedTagWrap = document.getElementById('vehx-images-selected-tag');
                    const imagesOptions = document.getElementById('vehx-images-options');
                    const imagesStatus = document.getElementById('vehx-images-status');

                    const lotRangeInput = document.getElementById('vehx-images-lot-range-input');
                    const sizeSeg = document.getElementById('vehx-images-size-seg');
                    const modeSeg = document.getElementById('vehx-images-mode-seg');
                    const createZipBtn = document.getElementById('vehx-images-create-zip-btn');

                    let selectedAuction = null; // {sale, label}

                    function renderImagesSelectedTag() {
                        imagesSelectedTagWrap.innerHTML = '';
                        if (!selectedAuction) {
                            imagesOptions.style.display = 'none';
                            return;
                        }

                        const tag = document.createElement('div');
                        tag.className = 'vehx-tag';
                        tag.innerHTML = `<span title="${escapeHtml(selectedAuction.label)}">${escapeHtml(selectedAuction.label)}</span>
                            <button type="button" aria-label="Remove">×</button>`;
                        tag.querySelector('button').addEventListener('click', () => {
                            selectedAuction = null;
                            imagesSaleNumber.value = '';
                            imagesSelectedTagWrap.innerHTML = '';
                            imagesOptions.style.display = 'none';
                            imagesStatus.textContent = '';
                        });
                        imagesSelectedTagWrap.appendChild(tag);

                        imagesOptions.style.display = 'block';
                    }

                    imagesAuctionFilter.addEventListener('input', () => {
                        const q = imagesAuctionFilter.value.trim().toLowerCase();
                        const items = imagesAuctionList.querySelectorAll('.vehx-item');
                        items.forEach(el => {
                            const label = (el.getAttribute('data-label') || '').toLowerCase();
                            el.style.display = (!q || label.includes(q)) ? '' : 'none';
                        });
                    });

                    imagesAuctionList.addEventListener('click', (e) => {
                        const item = e.target.closest('.vehx-item');
                        if (!item) return;

                        const sale = item.getAttribute('data-sale');
                        const label = item.getAttribute('data-label') || sale;
                        if (!sale) return;

                        selectedAuction = {
                            sale: String(sale),
                            label: String(label)
                        };
                        imagesSaleNumber.value = selectedAuction.sale;

                        imagesStatus.textContent = '';
                        lotRangeInput.value = '';
                        imagesLotRange.value = '';

                        // reset toggles defaults
                        imagesSize.value = 'large';
                        imagesMode.value = 'all';
                        sizeSeg.querySelectorAll('button').forEach(b => b.classList.toggle('active', b.dataset.size === 'large'));
                        modeSeg.querySelectorAll('button').forEach(b => b.classList.toggle('active', b.dataset.mode === 'all'));

                        renderImagesSelectedTag();
                    });

                    // Lot range: only numbers, commas, hyphens
                    lotRangeInput.addEventListener('input', () => {
                        const raw = lotRangeInput.value || '';
                        const cleaned = raw.replace(/[^0-9,\-\s]/g, '');
                        if (cleaned !== raw) {
                            const pos = lotRangeInput.selectionStart || cleaned.length;
                            lotRangeInput.value = cleaned;
                            lotRangeInput.setSelectionRange(pos, pos);
                        }
                        imagesLotRange.value = lotRangeInput.value.trim();
                    });

                    // Seg size (single select)
                    sizeSeg.addEventListener('click', (e) => {
                        const btn = e.target.closest('button');
                        if (!btn) return;
                        const size = btn.dataset.size;
                        if (!size) return;

                        imagesSize.value = size;
                        sizeSeg.querySelectorAll('button').forEach(b => b.classList.toggle('active', b === btn));
                    });

                    // Seg mode (single select)
                    modeSeg.addEventListener('click', (e) => {
                        const btn = e.target.closest('button');
                        if (!btn) return;
                        const mode = btn.dataset.mode;
                        if (!mode) return;

                        imagesMode.value = mode;
                        modeSeg.querySelectorAll('button').forEach(b => b.classList.toggle('active', b === btn));
                    });

                    // Create ZIP
                    createZipBtn.addEventListener('click', () => {
                        imagesStatus.textContent = '';

                        if (!selectedAuction || !imagesSaleNumber.value) {
                            imagesStatus.textContent = 'Please select an auction first.';
                            return;
                        }

                        showLoader('Creating ZIP…');
                        waitForDownloadThenHideLoader();
                        imagesForm.submit();
                    });

                })();
            </script>
        </div>
<?php
    }

    // =========================
    // Export XLSX
    // =========================
    private static function handle_export(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die('You do not have permission.');
        }

        @set_time_limit(0);
        while (ob_get_level()) {
            ob_end_clean();
        }

        self::require_composer_autoload();

        $mode = isset($_POST['vehicles_export_mode']) ? sanitize_key((string) $_POST['vehicles_export_mode']) : 'auction';
        $sale_numbers = [];

        if ($mode === 'dates') {
            $sale_numbers = self::get_selected_auction_ids_from_request(); // may be empty

            if (empty($sale_numbers)) {
                $start_raw = isset($_POST['vehicles_export_date_start']) ? trim((string) $_POST['vehicles_export_date_start']) : '';
                $end_raw   = isset($_POST['vehicles_export_date_end']) ? trim((string) $_POST['vehicles_export_date_end']) : '';

                $start = ($start_raw !== '') ? self::normalize_date_input_to_mysql_datetime($start_raw, 'start') : null;
                $end   = ($end_raw   !== '') ? self::normalize_date_input_to_mysql_datetime($end_raw, 'end')   : null;

                if (($start_raw !== '' && !$start) || ($end_raw !== '' && !$end)) {
                    wp_die('Please select a valid date (or leave empty).');
                }
                if ($start && $end && strtotime($end) < strtotime($start)) {
                    wp_die('End date must be after start date.');
                }

                $auctions = self::get_auctions_by_optional_date_range($start, $end);
                if (empty($auctions)) {
                    wp_die('No auctions found for the selected date filter.');
                }

                $sale_numbers = array_values(array_unique(array_filter(array_map(
                    fn($a) => $a['auction_id'] ?? '',
                    $auctions
                ))));
            }
        } else {
            // auction mode: empty allowed => export all
            $sale_numbers = self::get_selected_auction_ids_from_request(); // may be empty
        }

        $filtered_total = self::count_total_posts($sale_numbers);
        if ($filtered_total <= 0) {
            wp_die('No vehicles found for the selected filter.');
        }

        [$startChunk, $endChunk] = self::get_validated_range_from_request($filtered_total);

        $limit  = ($endChunk - $startChunk) + 1;
        $offset = $startChunk - 1;

        $ids = self::get_vehicle_ids_by_offset($offset, $limit, $sale_numbers);
        if (empty($ids)) {
            wp_die('No vehicles found in the selected chunk.');
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Vehicles');

        $columns = self::columns();
        $sheet->fromArray(array_keys($columns), null, 'A1');

        $row_index = 2;
        foreach ($ids as $post_id) {
            $row = self::resolve_row((int) $post_id, $columns);
            $sheet->fromArray($row, null, 'A' . $row_index);
            $row_index++;
        }

        self::stream_xlsx($spreadsheet, $startChunk, $endChunk, $mode);
    }

    private static function handle_urls_export(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die('You do not have permission.');
        }

        @set_time_limit(0);
        @ini_set('memory_limit', '512M');
        wp_suspend_cache_addition(true);

        while (ob_get_level()) {
            ob_end_clean();
        }

        self::require_composer_autoload();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Vehicles');
        $sheet->fromArray(['URL Permalink', 'URL Handh.co.uk'], null, 'A1');

        $row_index  = 2;
        $last_id    = 0;
        $batch_size = self::DEFAULT_CHUNK_SIZE;

        while (true) {
            $ids = self::get_all_vehicle_ids_after_id($last_id, $batch_size);
            if (empty($ids)) {
                break;
            }

            update_meta_cache('post', $ids);

            foreach ($ids as $post_id) {
                $post_id = (int) $post_id;
                $sheet->fromArray([
                    get_permalink($post_id) ?: '',
                    self::normalize_handh_lot_url(self::acf_value_string($post_id, 'lot_link')),
                ], null, 'A' . $row_index);
                $row_index++;
            }

            $last_id = (int) end($ids);

            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        }

        self::stream_urls_xlsx($spreadsheet);
    }

    private static function normalize_handh_lot_url(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        return str_replace(
            ['https://auctions.handh.co.uk', 'http://auctions.handh.co.uk', 'auctions.handh.co.uk'],
            ['https://www.handh.co.uk', 'http://www.handh.co.uk', 'www.handh.co.uk'],
            $url
        );
    }

    private static function build_vehicles_meta_query(array $auction_ids): array
    {
        $has_private = in_array(self::PRIVATE_SALES_TOKEN, $auction_ids, true);

        $only_auction_ids = array_values(array_filter($auction_ids, function ($v) {
            return $v !== self::PRIVATE_SALES_TOKEN && $v !== '';
        }));

        $clauses = [];

        if (!empty($only_auction_ids)) {
            $clauses[] = [
                'key'     => self::VEHICLE_AUCTION_META, // auction_number_latest (Post Object)
                'value'   => array_map('intval', $only_auction_ids),
                'compare' => 'IN',
                'type'    => 'NUMERIC',
            ];
        }

        if ($has_private) {
            $clauses[] = [
                'key'     => self::VEHICLE_TYPE_META,
                'value'   => 'private-sale',
                'compare' => '=',
                'type'    => 'CHAR',
            ];
        }

        if (count($clauses) > 1) {
            return array_merge(['relation' => 'OR'], $clauses);
        }

        return $clauses;
    }

    private static function stream_xlsx(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, int $start, int $end, string $mode): void
    {
        @setcookie('vehx_file_download', '1', time() + 60, COOKIEPATH ?: '/', COOKIE_DOMAIN ?: '');

        nocache_headers();

        $filename = sprintf(
            'vehicles-%s-%d-%d-%s.xlsx',
            $mode,
            $start,
            $end,
            gmdate('Y-m-d-His')
        );

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private static function stream_urls_xlsx(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet): void
    {
        @setcookie('vehx_file_download', '1', time() + 60, COOKIEPATH ?: '/', COOKIE_DOMAIN ?: '');

        nocache_headers();

        $filename = sprintf('vehicles-urls-all-%s.xlsx', gmdate('Y-m-d-His'));

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private static function signal_download_complete(): void
    {
        @setcookie('vehx_file_download', '1', time() + 60, COOKIEPATH ?: '/', COOKIE_DOMAIN ?: '');
    }

    // =========================
    // Export IMAGES ZIP
    // =========================
    private static function handle_images_export(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die('You do not have permission.');
        }

        if (!class_exists('ZipArchive')) {
            wp_die('ZipArchive is not available on this server.');
        }

        @set_time_limit(0);
        while (ob_get_level()) {
            ob_end_clean();
        }

        $auction_id_raw = isset($_POST['vehx_images_sale_number']) ? trim((string) $_POST['vehx_images_sale_number']) : '';
        if ($auction_id_raw === '') wp_die('Please select an auction.');

        $raw_lot_range = isset($_POST['vehx_images_lot_range']) ? trim((string) $_POST['vehx_images_lot_range']) : '';
        if ($raw_lot_range !== '' && !preg_match('/^[0-9,\-\s]+$/', $raw_lot_range)) {
            wp_die('Lot range contains invalid characters.');
        }

        $size = isset($_POST['vehx_images_size']) ? sanitize_key((string) $_POST['vehx_images_size']) : 'large';
        $mode = isset($_POST['vehx_images_mode']) ? sanitize_key((string) $_POST['vehx_images_mode']) : 'all';

        // map UI size -> WP size
        $wp_size = 'large';
        if ($size === 'medium') $wp_size = 'medium';
        if ($size === 'small')  $wp_size = 'thumbnail';

        $only_main = ($mode === 'main');

        $lot_numbers_filter = self::parse_lot_range_to_int_set($raw_lot_range); // [] means no filter

        if ($auction_id_raw === self::PRIVATE_SALES_TOKEN) {
            $vehicles = self::get_vehicle_ids_for_images_export_private_sales();
        } else {
            $auction_id = (int) $auction_id_raw;
            $vehicles = self::get_vehicle_ids_for_images_export_by_auction_id($auction_id);
        }

        if (empty($vehicles)) {
            self::signal_download_complete();
            wp_die('No vehicles found for this selection (and lot range, if provided).', 'No vehicles', ['response' => 200]);
        }

        // 2) If lot filter provided, filter in PHP by numeric part of lot_number_latest
        if (!empty($lot_numbers_filter)) {
            $vehicles = array_values(array_filter($vehicles, function ($vid) use ($lot_numbers_filter) {
                $lot_raw = self::get_acf_or_meta((int)$vid, 'lot_number_latest');
                $num = self::extract_first_int($lot_raw);
                return ($num !== null && isset($lot_numbers_filter[$num]));
            }));

            if (empty($vehicles)) {
                self::signal_download_complete();
                wp_die('No vehicles matched the Lot range filter.');
            }
        }

        // 3) Build zip
        $tmp = self::make_tmp_file('vehx-images-');
        if (!$tmp) {
            self::signal_download_complete();
            wp_die('Could not create a temp file.');
        }

        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
            @unlink($tmp);
            self::signal_download_complete();
            wp_die('Could not create ZIP.');
        }

        $added = 0;
        $lotCounters = []; // lot_key => next index (int)

        foreach ($vehicles as $vid) {
            $vid = (int)$vid;

            $lot_raw = self::get_acf_or_meta($vid, 'lot_number_latest');
            $lot_raw = is_scalar($lot_raw) ? trim((string)$lot_raw) : '';
            $lot_key = $lot_raw !== '' ? $lot_raw : ('vehicle-' . $vid);

            if (!isset($lotCounters[$lot_key])) {
                $lotCounters[$lot_key] = 1;
            }

            // Featured image first
            $featured_id = (int) get_post_thumbnail_id($vid);
            if ($featured_id > 0) {
                $path = self::get_attachment_path_for_size($featured_id, $wp_size);
                if ($path && file_exists($path)) {
                    $index = $lotCounters[$lot_key];
                    $name  = self::build_zip_entry_name($lot_key, $index, $path);

                    while ($zip->locateName($name) !== false) {
                        $lotCounters[$lot_key]++;
                        $index = $lotCounters[$lot_key];
                        $name  = self::build_zip_entry_name($lot_key, $index, $path);
                    }

                    $zip->addFile($path, $name);
                    $added++;
                    $lotCounters[$lot_key]++;
                }
            }

            if ($only_main) continue;

            // Gallery images
            $gallery_ids = self::get_gallery_image_ids($vid);
            foreach ($gallery_ids as $img_id) {
                $img_id = (int)$img_id;
                if ($img_id <= 0) continue;

                $path = self::get_attachment_path_for_size($img_id, $wp_size);
                if (!$path || !file_exists($path)) continue;

                $index = $lotCounters[$lot_key];
                $name  = self::build_zip_entry_name($lot_key, $index, $path);

                while ($zip->locateName($name) !== false) {
                    $lotCounters[$lot_key]++;
                    $index = $lotCounters[$lot_key];
                    $name  = self::build_zip_entry_name($lot_key, $index, $path);
                }

                $zip->addFile($path, $name);
                $added++;
                $lotCounters[$lot_key]++;
            }
        }

        $zip->close();

        if ($added <= 0) {
            @unlink($tmp);
            wp_die('No images found to include in the ZIP.');
        }

        // 4) Stream zip
        @setcookie('vehx_file_download', '1', time() + 60, COOKIEPATH ?: '/', COOKIE_DOMAIN ?: '');

        nocache_headers();

        $zipLabel = ($auction_id_raw === self::PRIVATE_SALES_TOKEN) ? 'private-sales' : preg_replace('/[^0-9a-zA-Z\-_]/', '', $auction_id_raw);

        $filename = sprintf(
            'auction-%s-images-%s.zip',
            $zipLabel,
            gmdate('Y-m-d-His')
        );

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmp));
        header('Cache-Control: max-age=0');

        readfile($tmp);
        @unlink($tmp);
        exit;
    }

    private static function get_vehicle_ids_for_images_export_private_sales(): array
    {
        $q = new WP_Query([
            'post_type'      => self::POST_TYPE,
            'perm'           => 'readable',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'orderby'        => 'ID',
            'order'          => 'ASC',
            'meta_query'     => [
                [
                    'key'     => self::VEHICLE_TYPE_META,
                    'value'   => 'private-sale',
                    'compare' => '=',
                    'type'    => 'CHAR',
                ],
            ],
        ]);

        return is_array($q->posts) ? $q->posts : [];
    }

    private static function get_vehicle_ids_for_images_export_by_auction_id(int $auction_id): array
    {
        if ($auction_id <= 0) return [];

        $q = new WP_Query([
            'post_type'      => self::POST_TYPE,
            'perm'           => 'readable',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'orderby'        => 'ID',
            'order'          => 'ASC',
            'meta_query'     => [
                [
                    'key'     => self::VEHICLE_AUCTION_META,
                    'value'   => $auction_id,
                    'compare' => '=',
                    'type'    => 'NUMERIC',
                ],
            ],
        ]);

        return is_array($q->posts) ? $q->posts : [];
    }

    private static function get_gallery_image_ids(int $vehicle_id): array
    {
        $out = [];

        if (function_exists('get_field')) {
            // If your gallery field name is different, update it here:
            $gallery = get_field('gallery_vehicle', $vehicle_id);

            if (is_array($gallery)) {
                foreach ($gallery as $item) {
                    if (is_numeric($item)) {
                        $out[] = (int)$item;
                        continue;
                    }
                    if (is_array($item)) {
                        if (isset($item['ID']) && is_numeric($item['ID'])) {
                            $out[] = (int)$item['ID'];
                            continue;
                        }
                        if (isset($item['id']) && is_numeric($item['id'])) {
                            $out[] = (int)$item['id'];
                            continue;
                        }
                    }
                }
            }
        }

        $out = array_values(array_filter(array_unique($out)));
        return $out;
    }

    private static function get_attachment_path_for_size(int $attachment_id, string $size): ?string
    {
        $full = get_attached_file($attachment_id);
        if (!$full || !is_string($full)) return null;

        if ($size === 'full') return $full;

        $meta = wp_get_attachment_metadata($attachment_id);
        if (!is_array($meta) || empty($meta['sizes']) || !is_array($meta['sizes'])) return $full;

        if (!isset($meta['sizes'][$size]['file'])) return $full;

        $file = (string)$meta['sizes'][$size]['file'];
        $dir = trailingslashit(dirname($full));
        $path = $dir . $file;

        return $path ?: $full;
    }

    private static function build_zip_entry_name(string $lot_key, int $index, string $path): string
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $ext = $ext ? strtolower((string)$ext) : 'jpg';

        $lot_key = sanitize_file_name($lot_key);
        if ($lot_key === '') $lot_key = 'unknown-lot';

        return sanitize_file_name(sprintf('%s-%d.%s', $lot_key, $index, $ext));
    }

    private static function make_tmp_file(string $prefix): ?string
    {
        if (function_exists('wp_tempnam')) {
            $tmp = wp_tempnam($prefix);
            return $tmp ? (string)$tmp : null;
        }
        $dir = get_temp_dir();
        $tmp = tempnam($dir, $prefix);
        return $tmp ? (string)$tmp : null;
    }

    private static function parse_lot_range_to_int_set(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') return [];

        $set = [];
        $parts = array_map('trim', explode(',', $raw));

        foreach ($parts as $p) {
            if ($p === '') continue;

            if (strpos($p, '-') !== false) {
                [$a, $b] = array_map('trim', explode('-', $p, 2));
                if ($a === '' || $b === '') continue;
                if (!ctype_digit($a) || !ctype_digit($b)) continue;

                $start = (int)$a;
                $end   = (int)$b;
                if ($end < $start) {
                    [$start, $end] = [$end, $start];
                }

                for ($i = $start; $i <= $end; $i++) $set[$i] = true;
                continue;
            }

            if (!ctype_digit($p)) continue;
            $set[(int)$p] = true;
        }

        return $set;
    }

    private static function extract_first_int($value): ?int
    {
        if (!is_scalar($value)) return null;
        $s = trim((string)$value);
        if ($s === '') return null;

        if (preg_match('/\d+/', $s, $m)) return (int)$m[0];
        return null;
    }

    private static function get_acf_or_meta(int $post_id, string $key): string
    {
        if (function_exists('get_field')) {
            $v = get_field($key, $post_id);
            if (is_scalar($v)) return (string)$v;
        }
        return self::meta_string($post_id, $key);
    }

    // ==================================================
    // Queries (XLSX)
    // ==================================================
    private static function count_total_posts(array $auction_ids = []): int
    {
        // ✅ Normal: empty => all published
        if (empty($auction_ids)) {
            $count = wp_count_posts(self::POST_TYPE);
            return isset($count->publish) ? (int) $count->publish : 0;
        }

        // ✅ Filtered (auctions and/or private sales)
        $meta_query = self::build_vehicles_meta_query($auction_ids);

        $q = new WP_Query([
            'post_type'      => self::POST_TYPE,
            'perm'           => 'readable',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => false,
            'meta_query'     => $meta_query,
        ]);

        return (int) $q->found_posts;
    }

    private static function get_vehicle_ids_by_offset(int $offset, int $limit, array $auction_ids = []): array
    {
        $args = [
            'post_type'              => self::POST_TYPE,
            'perm'                   => 'readable',
            'posts_per_page'         => $limit,
            'offset'                 => $offset,
            'fields'                 => 'ids',
            'orderby'                => 'ID',
            'order'                  => 'ASC',
            'no_found_rows'          => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => true,
        ];

        if (!empty($auction_ids)) {
            $args['meta_query'] = self::build_vehicles_meta_query($auction_ids);
        }

        $q = new WP_Query($args);
        return is_array($q->posts) ? $q->posts : [];
    }

    private static function get_all_vehicle_ids_after_id(int $after_id, int $limit): array
    {
        global $wpdb;

        $ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts}
                 WHERE post_type = %s AND post_status = 'publish' AND ID > %d
                 ORDER BY ID ASC
                 LIMIT %d",
                self::POST_TYPE,
                $after_id,
                $limit
            )
        );

        if (!is_array($ids) || empty($ids)) {
            return [];
        }

        return array_map('intval', $ids);
    }

    // ==================================================
    // Range validation + chunk builder
    // ==================================================
    private static function build_chunks(int $total, int $chunk_size): array
    {
        if ($total <= 0) return [];

        $chunks = [];
        $start = 1;
        while ($start <= $total) {
            $end = min($start + $chunk_size - 1, $total);
            $chunks[] = ['start' => $start, 'end' => $end];
            $start = $end + 1;
        }
        return $chunks;
    }

    private static function parse_range(string $raw): array
    {
        if ($raw === '' || strpos($raw, '-') === false) return [0, 0];
        [$a, $b] = array_map('trim', explode('-', $raw, 2));
        return [(int) $a, (int) $b];
    }

    private static function get_validated_range_from_request(int $total_filtered): array
    {
        $range_raw = isset($_POST['vehicles_export_range'])
            ? sanitize_text_field(wp_unslash($_POST['vehicles_export_range']))
            : '';

        [$start, $end] = self::parse_range($range_raw);

        if ($start <= 0 || $end <= 0 || $end < $start) {
            $start = 1;
            $end = self::DEFAULT_CHUNK_SIZE;
        }

        if ($total_filtered > 0) {
            $end = min($end, $total_filtered);
        }

        $rows = ($end - $start) + 1;
        if ($rows > self::MAX_EXPORT_ROWS) {
            wp_die(sprintf('Maximum export size is %d rows. Please select a smaller range.', self::MAX_EXPORT_ROWS));
        }

        return [$start, $end];
    }

    private static function sanitize_auction_ids($raw): array
    {
        if (!is_array($raw)) return [];

        $out = [];
        foreach ($raw as $v) {
            $v = is_scalar($v) ? trim((string) $v) : '';
            if ($v === '') continue;

            if ($v === self::PRIVATE_SALES_TOKEN) {
                $out[$v] = true;
                continue;
            }

            $id = (int) $v;
            if ($id > 0) $out[(string)$id] = true;
        }

        return array_keys($out);
    }

    // ==================================================
    // Columns (XLSX)  ✅ UPDATED AS REQUESTED
    // ==================================================
    private static function columns(): array
    {
        // Column headers MUST match exactly (and in this order)
        return [
            'client_id'              => ['acf', 'client_id'],
            'listing_reference_id'   => ['acf', 'stock_number'],
            'listing_type'           => ['listing_type'],
            'listing_category'       => ['acf', 'listing_category'],

            'vrn'                    => ['acf', 'vrn'],
            'vin'                    => ['acf', 'vin'],

            'year'                   => ['acf', 'year_vehicle'],
            'make_name'              => ['acf', 'artist_maker_brand'],
            'model_name'             => ['acf', 'model_vehicle'],

            'variant_name'           => ['acf', 'variant_name'],
            'generation_name'        => ['acf', 'generation_name'],

            'body_type'              => ['acf', 'body_type'],
            'odometer'               => ['acf', 'odometer'],
            'odometer_unit'          => ['acf', 'odometer_unit'],
            'engine_size_cc'         => ['acf', 'engine_size_cc'],
            'steering_position'      => ['acf', 'steering_position'],
            'transmission_type'      => ['acf', 'transmission_type'],
            'gears'                  => ['acf', 'gears'],
            'colour'                 => ['acf', 'colour'],
            'fuel_type'              => ['acf', 'fuel_type'],

            'price'                  => ['price', 'estimate_low'],
            'price_currency'         => ['acf', 'price_currency'],

            'advert_title'           => ['post_title'],           // advert_title = post title
            'description'            => ['acf', 'description'],   // description = ACF description
            'image_urls'             => ['image_urls'],           // special format
			'lot_number'             => ['acf', 'lot_number_latest'],
        ];
    }

    private static function resolve_row(int $post_id, array $columns): array
    {
        $row = [];
        foreach ($columns as $descriptor) {
            $row[] = self::resolve_value($post_id, $descriptor);
        }
        return $row;
    }

    private static function resolve_value(int $post_id, array $descriptor): string
    {
        $type = $descriptor[0] ?? '';

        switch ($type) {
            case 'fixed':
                return (string)($descriptor[1] ?? '');

            case 'post_title':
                $title = (string) get_the_title($post_id);
                return html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            case 'price':
                $v = function_exists('get_field') ? get_field('estimate_low', $post_id) : null;
                return is_scalar($v) ? (string) $v : '';

            case 'listing_type':
                // ACF radio: auction | private-sale
                $tov = function_exists('get_field') ? (string) get_field('type_of_vehicle', $post_id) : '';
                $tov = trim($tov);

                if ($tov === 'auction') {
                    return 'for-sale-by-auction';
                }

                if ($tov === 'private-sale') {
                    return 'for-sale';
                }

                return '';

            case 'acf':
                $key = (string)($descriptor[1] ?? '');
                if ($key === '') return '';
                return self::acf_value_string($post_id, $key);

            case 'image_urls':
                return self::get_image_urls_export_string($post_id);
        }

        return '';
    }

    /**
     * ACF value to string
     * - Scalars => string
     * - WP_Post (Post Object) => post_title
     * - Numeric (post ID) => title
     * - Arrays:
     *   - if has ['label'] use it
     *   - else join scalar values with comma
     */
    private static function acf_value_string(int $post_id, string $key): string
    {
        if (!function_exists('get_field')) {
            return self::meta_string($post_id, $key);
        }

        $v = get_field($key, $post_id);

        // Sometimes Post Object returns ID
        if ($key == 'artist_maker_brand' || $key == 'model_vehicle') {
            $pid = (int)$v;
            if ($pid > 0) return (string) get_post_field('post_title', $pid);
            return (string)$v;
        }

        if (!empty($v)) {
            return $v;
        } else {
            return '';
        }
    }

    /**
     * image_urls format required:
     * [""https://www.example.com/image1.jpg"",""https://www.example.com/image2.jpg""]
     */
    private static function get_image_urls_export_string(int $vehicle_id): string
    {
        $urls = [];

        // Featured first
        $featured_id = (int) get_post_thumbnail_id($vehicle_id);
        if ($featured_id > 0) {
            $u = wp_get_attachment_url($featured_id);
            if ($u) $urls[] = (string)$u;
        }

        // Then gallery_vehicle
        $ids = self::get_gallery_image_ids($vehicle_id);
        if (!empty($ids)) {
            foreach ($ids as $id) {
                $id = (int)$id;
                if ($id <= 0) continue;
                if ($featured_id > 0 && $id === $featured_id) continue;

                $u = wp_get_attachment_url($id);
                if ($u) $urls[] = (string)$u;
            }
        }

        $urls = array_values(array_filter(array_unique(array_map(static function ($u) {
            $u = is_string($u) ? trim(str_replace(["\r", "\n"], '', $u)) : '';
            return $u;
        }, $urls))));

        return wp_json_encode($urls, JSON_UNESCAPED_SLASHES);
    }

    // ==================================================
    // Helpers (Auctions by optional date range)
    // ==================================================
    private static function get_auctions_by_optional_date_range(?string $start, ?string $end): array
    {
        $args = [
            'post_type'      => self::AUCTION_POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'orderby'        => 'ID',
            'order'          => 'DESC',
        ];

        if ($start || $end) {
            $min = '1970-01-01 00:00:00';
            $max = '2100-12-31 23:59:59';

            $args['meta_query'] = [
                [
                    'key'     => self::AUCTION_DATE_META,
                    'value'   => [$start ?: $min, $end ?: $max],
                    'compare' => 'BETWEEN',
                    'type'    => 'DATETIME',
                ],
            ];
        }

        $q = new WP_Query($args);
        $ids = is_array($q->posts) ? $q->posts : [];
        if (empty($ids)) return [];

        $out = [];
        foreach ($ids as $aid) {
            $sale_number = get_post_meta((int) $aid, self::AUCTION_SALE_META, true);
            $sale_number = is_scalar($sale_number) ? trim((string) $sale_number) : '';
            if ($sale_number === '') continue;

            $auction_date = get_post_meta((int) $aid, self::AUCTION_DATE_META, true);
            $auction_date = is_scalar($auction_date) ? trim((string) $auction_date) : '';

            $title = get_the_title((int) $aid);
            $title = $title ? (string) $title : ('Auction #' . (int) $aid);

            $label = $title;
            if ($auction_date !== '') $label .= ' | ' . $auction_date;
            $label .= ' | Sale #' . $sale_number;

            $out[] = [
                'auction_id' => (int) $aid,
                'label'       => $label,
            ];
        }

        return $out;
    }

    private static function require_composer_autoload(): void
    {
        if (class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            return;
        }

        $paths = array_unique([
            get_template_directory() . '/vendor/autoload.php',
            get_stylesheet_directory() . '/vendor/autoload.php',
        ]);

        foreach ($paths as $autoload) {
            if (!is_readable($autoload)) {
                continue;
            }

            require_once $autoload;

            if (class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
                return;
            }
        }

        wp_die('Composer autoload not found or PhpSpreadsheet is not available.');
    }

    private static function meta_string(int $post_id, string $meta_key): string
    {
        $value = get_post_meta($post_id, $meta_key, true);
        return is_scalar($value) ? (string) $value : '';
    }

    // ==================================================
    // Auctions options + request parsing
    // ==================================================
    private static function get_auction_options(): array
    {
        $posts = get_posts([
            'post_type'      => self::AUCTION_POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'ID',
            'order'          => 'DESC',
            'no_found_rows'  => true,
        ]);

        $out = [];

        foreach ($posts as $p) {
            if (!($p instanceof WP_Post)) continue;

            $auction_id = (int) $p->ID;

            $sale_number = get_post_meta($auction_id, self::AUCTION_SALE_META, true);
            $sale_number = is_scalar($sale_number) ? trim((string) $sale_number) : '';

            $auction_date = get_post_meta($auction_id, self::AUCTION_DATE_META, true);
            $auction_date = is_scalar($auction_date) ? trim((string) $auction_date) : '';

            $title = get_the_title($auction_id);
            $title = $title ? (string) $title : ('Auction #' . $auction_id);

            $label = $title;
            if ($auction_date !== '') $label .= ' | ' . $auction_date;
            if ($sale_number !== '') $label .= ' | Sale #' . $sale_number;

            // ✅ KEY = AUCTION POST ID (no sale_number)
            $out[$auction_id] = $label;
        }

        return $out;
    }

    private static function get_selected_auction_ids_from_request(): array
    {
        return self::sanitize_auction_ids($_POST['vehicles_export_auctions'] ?? []);
    }

    // ==================================================
    // Date handling
    // ==================================================
    private static function normalize_date_input_to_mysql_datetime(string $raw, string $edge): ?string
    {
        if ($raw === '') return null;

        $ts = strtotime($raw);
        if (!$ts) return null;

        if ($edge === 'start') {
            return gmdate('Y-m-d 00:00:00', $ts);
        }
        return gmdate('Y-m-d 23:59:59', $ts);
    }
}

Vehicles_Export_Module::init();
