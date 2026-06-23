<?php
/**
 * Bulk Images (ZIP) -> Vehicles Gallery
 * - Submenu under Auctions
 * - Upload ZIP (images only)
 * - Select ONE auction via radio list (NO search)
 * - Parse filenames like:
 *   123-1.jpg, 123_1.jpg, 123.1.jpg, 123.(1).jpg, 123(1).jpg, 45p.jpg, 59p-2.jpg
 * - Finds Vehicle by:
 *   - auction_number_latest == Auction ID (preferred)
 *   - OR auction_number_latest == sale_number (fallback)
 *   - AND lot_number_latest == lot (normalized match)
 * - Replaces (clears + sets) ACF gallery field gallery_vehicle
 *   ONLY for vehicles whose lot exists in the ZIP.
 *
 * Notes:
 * - ZIP manifest is built without extracting (lazy extract per image during batches).
 * - Job state is persisted as job.json under the run folder (large ZIPs exceed transient limits).
 * - Images are processed in small batches (BATCH_IMAGE_COUNT) to avoid PHP/proxy timeouts.
 */

if (!defined('ABSPATH')) exit;

final class HNH_Bulk_Images
{
    private const MENU_SLUG   = 'hnh-bulk-images';
    private const CAPABILITY  = 'edit_posts';

    private const AUCTION_CPT = 'auction';
    private const VEHICLE_CPT = 'vehicles';

    // Vehicle fields (ACF/meta)
    private const META_AUCTION = 'auction_number_latest'; // Can be Post Object ID or sale_number
    private const META_LOT     = 'lot_number_latest';

    // ACF gallery field name in Vehicle
    private const GALLERY_FIELD = 'gallery_vehicle';

    // Working dir under uploads
    private const WORKING_SUBDIR = 'hnh-bulk-images';

    // Transient TTL (final report only)
    private const REPORT_TTL_SECONDS = 180;

    /** Max images processed per admin-ajax batch request */
    private const BATCH_IMAGE_COUNT = 8;

    /** Client timeout for ZIP manifest scan (ms); 0 = no limit for batch requests */
    private const AJAX_TIMEOUT_PREPARE_MS = 1800000;
    private const AJAX_TIMEOUT_BATCH_MS   = 0;

    private const JOB_FILE = 'job.json';
    private const S3_UPLOAD_CONCURRENCY = 10;

    /** @var \Aws\S3\S3Client|null Reused for all uploads within one batch request */
    private static $s3Client = null;

    public static function boot(): void
    {
        add_action('admin_menu', [__CLASS__, 'registerMenu']);
        add_action('wp_ajax_hnh_bulk_images_upload_chunk', [__CLASS__, 'uploadChunk']);
        add_action('wp_ajax_hnh_bulk_images_finalize', [__CLASS__, 'finalizeChunks']);
        add_action('wp_ajax_hnh_bulk_images_prepare', [__CLASS__, 'prepareJob']);
        add_action('wp_ajax_hnh_bulk_images_process_batch', [__CLASS__, 'processBatch']);
    }

    public static function registerMenu(): void
    {
        add_submenu_page(
            'edit.php?post_type=' . self::AUCTION_CPT,
            'Bulk Image Upload',
            'Bulk Image Upload',
            self::CAPABILITY,
            self::MENU_SLUG,
            [__CLASS__, 'renderPage']
        );
    }

    public static function renderPage(): void
    {
        if (!current_user_can(self::CAPABILITY)) wp_die('Forbidden', 403);

        $report_key = self::reportKey();
        $report = get_transient($report_key);
        if ($report) delete_transient($report_key);

        $auctions = get_posts([
            'post_type'      => self::AUCTION_CPT,
            'post_status'    => ['publish', 'draft', 'pending', 'private'],
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'fields'         => 'ids',
        ]);

        $uploads = wp_upload_dir();

        // CSS + Loader UI
        echo '<style>
            .hnh-bulk-wrap{max-width:1100px}
            .hnh-bulk-card{background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:18px;margin-top:14px}
            .hnh-row{display:flex;gap:18px;flex-wrap:wrap}
            .hnh-col{flex:1;min-width:360px}
            .hnh-field label{display:block;font-weight:600;margin:0 0 6px}
            .hnh-help{color:#646970;margin-top:6px;font-size:12px}
            .hnh-list{border:1px solid #dcdcde;border-radius:8px;max-height:360px;overflow:auto;background:#fff;padding:8px}
            .hnh-item{display:flex;gap:10px;align-items:flex-start;padding:8px;border-radius:8px}
            .hnh-item:hover{background:#f6f7f7}
            .hnh-item input{margin-top:3px}
            .hnh-item-title{font-weight:600}
            .hnh-item-meta{opacity:.75;font-size:12px;margin-top:2px}
            .hnh-muted{opacity:.65}
            .hnh-report table{border-collapse:collapse;width:100%}
            .hnh-report th,.hnh-report td{border:1px solid #dcdcde;padding:8px;vertical-align:top}
            .hnh-badge{display:inline-block;padding:2px 8px;border-radius:999px;background:#f0f0f1;font-size:12px}
            .hnh-badge.ok{background:#e7f7ed}
            .hnh-badge.warn{background:#fff4e5}
            .hnh-badge.err{background:#ffe5e5}

            /* Loader overlay */
            .hnh-loader-overlay{
                position:fixed;inset:0;background:rgba(0,0,0,.25);
                display:none;align-items:center;justify-content:center;z-index:999999;
            }
            .hnh-loader-card{
                width:min(520px, calc(100% - 40px));
                background:#fff;border:1px solid #dcdcde;border-radius:12px;
                padding:16px 18px;box-shadow:0 8px 30px rgba(0,0,0,.12);
            }
            .hnh-loader-title{font-weight:700;margin:0 0 10px;font-size:14px}
            .hnh-loader-sub{margin:0 0 12px;color:#646970;font-size:12px}
            .hnh-progress{
                width:100%;height:10px;border-radius:999px;background:#f0f0f1;overflow:hidden;
                border:1px solid #dcdcde;
            }
            .hnh-progress > div{
                height:100%;
                width:0%;
                background:#2271b1;
                border-radius:999px;
                transition: width 0.2s ease;
            }
            @keyframes hnh-indeterminate{
                0%{transform:translateX(-120%)}
                100%{transform:translateX(520%)}
            }
            .hnh-loader-note{margin:10px 0 0;color:#646970;font-size:12px}
            .hnh-progress-meta{
                display:flex;justify-content:space-between;align-items:flex-start;
                gap:12px;margin-top:8px;
            }
            .hnh-progress-status{
                margin:0;font-size:13px;color:#1d2327;font-weight:500;
                min-height:1.25em;flex:1;
            }
            .hnh-progress-counter{
                font-size:12px;color:#646970;white-space:nowrap;
                text-align:right;font-variant-numeric:tabular-nums;
            }
            .hnh-progress-footer{margin-top:4px;font-size:12px;color:#646970}
            .hnh-progress-error{
                display:none;margin-top:12px;padding:10px 12px;border-radius:8px;
                background:#fcf0f1;border:1px solid #d63638;color:#3c434a;font-size:12px;
            }
            .hnh-progress-error.is-visible{display:block}
            .hnh-progress-error strong{display:block;margin-bottom:4px;color:#d63638}
            .hnh-progress-error pre{
                margin:8px 0 0;padding:8px;background:#fff;border:1px solid #dcdcde;
                border-radius:4px;white-space:pre-wrap;word-break:break-word;font-size:11px;max-height:120px;overflow:auto;
            }
            .hnh-progress-error .button{margin-top:8px}
        </style>';

        $uploads = wp_upload_dir();
        $ajax_nonce = wp_create_nonce('hnh_bulk_images_upload');

        ?>
        <div id="hnh-loader-overlay" class="hnh-loader-overlay" aria-hidden="true">
            <div class="hnh-loader-card">
                <p class="hnh-loader-title">Processing ZIP...</p>
                <p class="hnh-loader-sub">Uploading images and replacing vehicle galleries. Please keep this tab open.</p>
                <div style="width:100%;background:#f0f0f0;border-radius:8px;overflow:hidden;">
                    <div id="hnh_progress_bar" style="height:16px;width:0%;background:#2271b1;transition:width 0.2s ease;"></div>
                </div>
                <div class="hnh-progress-meta">
                    <p id="hnh_progress_status" class="hnh-progress-status">Waiting to start…</p>
                    <span id="hnh_progress_counter" class="hnh-progress-counter" aria-live="polite"></span>
                </div>
                <p id="hnh_progress_text" class="hnh-progress-footer">0%</p>
                <div id="hnh_progress_error" class="hnh-progress-error" role="alert">
                    <strong id="hnh_progress_error_title"></strong>
                    <span id="hnh_progress_error_message"></span>
                    <pre id="hnh_progress_error_detail" style="display:none;"></pre>
                    <button type="button" id="hnh_progress_error_close" class="button">Close</button>
                </div>
                <p class="hnh-loader-note">Progress reflects upload and server processing (batches).</p>
            </div>
        </div>

        <div class="wrap hnh-bulk-wrap">
            <h1>Bulk Image Upload (ZIP) → Vehicles Gallery</h1>
            <div class="hnh-bulk-card">
                <div class="hnh-row">
                    <div class="hnh-col">
                        <div class="hnh-field">
                            <label for="hnh_zip">ZIP with images</label>
                            <input type="file" id="hnh_zip" accept=".zip" required />
                            <input type="hidden" id="chunk_run_dir">
                            <div class="hnh-help">
                                Only image files inside the ZIP are processed (jpg/jpeg/png/webp/gif).
                            </div>
                        </div>
                        <p class="hnh-help" style="margin-top:12px;">
                            Accepted filenames: <code>123-1.jpg</code>, <code>123_1.jpg</code>, <code>123.1.jpg</code>,
                            <code>123.(1).jpg</code>, <code>45p.jpg</code>, <code>59p-2.jpg</code>.
                        </p>
                        <button id="hnh_start_upload" class="button button-primary" style="margin-top:12px;">Process ZIP and replace galleries</button>
                    </div>

                    <div class="hnh-col">
                        <div class="hnh-field">
                            <label>Select 1 Auction</label>
                            <div class="hnh-help" style="margin-bottom:10px;">
                                (Searchable) Choose the auction these images belong to.
                            </div>

                            <!-- Search field -->
                            <input type="text" class="hnh-search" placeholder="Search auction..."
                                style="margin-bottom:10px;padding:5px;width:100%;" />

                            <div class="hnh-list">
                                <div class="hnh-no-results" style="display:none;color:#999;margin-bottom:5px;">No auctions
                                    found.</div>

                                <?php if (!$auctions): ?>
                                <div class="hnh-muted">No auctions found.</div>
                                <?php else: ?>
                                <?php foreach ($auctions as $aid): ?>
                                <?php
                                                        $title = get_the_title($aid);
                                                        $auction_date = function_exists('get_field') ? get_field('auction_date', $aid) : '';
                                                        $sale_number  = function_exists('get_field') ? get_field('sale_number', $aid) : '';

                                                        $date_str = '';
                                                        if (!empty($auction_date)) {
                                                            $ts = strtotime((string)$auction_date);
                                                            $date_str = $ts ? date('Y-m-d H:i:s', $ts) : (string)$auction_date;
                                                        }

                                                        $meta_parts = [];
                                                        if ($date_str) $meta_parts[] = $date_str;
                                                        if ($sale_number !== '' && $sale_number !== null) $meta_parts[] = 'Sale #' . $sale_number;
                                                        // $meta_parts[] = 'ID ' . $aid;

                                                        $meta = implode(' | ', $meta_parts);
                                                    ?>
                                <label class="hnh-item">
                                    <input type="radio" name="auction_id" value="<?php echo (int)$aid; ?>" required />
                                    <div>
                                        <div class="hnh-item-title"><?php echo esc_html($title); ?></div>
                                        <div class="hnh-item-meta"><?php echo esc_html($meta); ?></div>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>

                    <script>
                    (function($) {
                        $(document).ready(function() {
                            var $list = $('.hnh-list');
                            var $items = $list.find('.hnh-item');
                            var $noResults = $list.find('.hnh-no-results');

                            $('.hnh-search').on('input', function() {
                                var query = $(this).val().toLowerCase();
                                var visibleCount = 0;

                                $items.each(function() {
                                    var title = $(this).find('.hnh-item-title').text()
                                .toLowerCase();
                                    var meta = $(this).find('.hnh-item-meta').text().toLowerCase();
                                    if (title.indexOf(query) !== -1 || meta.indexOf(query) !== -1) {
                                        $(this).show();
                                        visibleCount++;
                                    } else {
                                        $(this).hide();
                                    }
                                });

                                if (visibleCount === 0) {
                                    $noResults.show();
                                } else {
                                    $noResults.hide();
                                }
                            });
                        });
                    })(jQuery);
                    </script>
                </div>
                </form>
            </div>
            <script>
            (function() {
                const AJAX_TIMEOUT_PREPARE_MS = <?php echo (int) self::AJAX_TIMEOUT_PREPARE_MS; ?>;
                const AJAX_TIMEOUT_BATCH_MS = <?php echo (int) self::AJAX_TIMEOUT_BATCH_MS; ?>;
                const NONCE = <?php echo wp_json_encode($ajax_nonce); ?>;

                const btn = document.getElementById('hnh_start_upload');
                const fileInput = document.getElementById('hnh_zip');
                const runDirInput = document.getElementById('chunk_run_dir');
                const progressContainer = document.getElementById('hnh-loader-overlay');
                const progressBar = document.getElementById('hnh_progress_bar');
                const progressText = document.getElementById('hnh_progress_text');
                const progressStatus = document.getElementById('hnh_progress_status');
                const progressCounter = document.getElementById('hnh_progress_counter');
                const errorBox = document.getElementById('hnh_progress_error');

                let filesTotal = 0;
                let filesUploaded = 0;
                let lotsTotal = 0;

                function setCounter() {
                    if (!progressCounter) return;
                    if (filesTotal > 0) {
                        progressCounter.textContent = filesUploaded + ' / ' + filesTotal + ' images';
                    } else {
                        progressCounter.textContent = '';
                    }
                }

                function applyBatchProgress(batch) {
                    if (typeof batch.uploaded === 'number') {
                        filesUploaded = batch.uploaded;
                    }
                    if (typeof batch.files_found === 'number' && batch.files_found > 0) {
                        filesTotal = batch.files_found;
                    }
                    if (typeof batch.total_lots === 'number') {
                        lotsTotal = batch.total_lots;
                    }
                    setCounter();
                }
                const errorTitle = document.getElementById('hnh_progress_error_title');
                const errorMessage = document.getElementById('hnh_progress_error_message');
                const errorDetail = document.getElementById('hnh_progress_error_detail');
                const errorClose = document.getElementById('hnh_progress_error_close');

                function setUi(pct, statusText) {
                    progressBar.style.width = pct + '%';
                    progressText.textContent = pct + '%';
                    if (statusText) progressStatus.textContent = statusText;
                    setCounter();
                }

                function hideError() {
                    errorBox.classList.remove('is-visible');
                    errorDetail.style.display = 'none';
                    errorDetail.textContent = '';
                }

                function showError(title, message, detail) {
                    errorTitle.textContent = title;
                    errorMessage.textContent = message;
                    if (detail) {
                        errorDetail.textContent = typeof detail === 'string' ? detail : JSON.stringify(detail, null, 2);
                        errorDetail.style.display = 'block';
                    } else {
                        errorDetail.style.display = 'none';
                        errorDetail.textContent = '';
                    }
                    errorBox.classList.add('is-visible');
                }

                function wpErrorMessage(data) {
                    if (!data) return 'Unknown server error';
                    if (typeof data === 'string') return data;
                    if (data.message) return data.message;
                    if (data.code) return data.code;
                    return JSON.stringify(data);
                }

                async function ajaxJson(formData, timeoutMs) {
                    const controller = new AbortController();
                    let timer = null;
                    if (timeoutMs > 0) {
                        timer = setTimeout(function() {
                            controller.abort();
                        }, timeoutMs);
                    }

                    let resp;
                    try {
                        resp = await fetch(ajaxurl, {
                            method: 'POST',
                            body: formData,
                            signal: controller.signal
                        });
                    } catch (err) {
                        if (err && err.name === 'AbortError') {
                            throw {
                                kind: 'timeout',
                                message: 'The server took too long to respond. Keep this tab open; if it persists, ask hosting to raise proxy/fastcgi timeouts.'
                            };
                        }
                        throw {
                            kind: 'network',
                            message: err && err.message ? err.message : 'Network error'
                        };
                    } finally {
                        if (timer) clearTimeout(timer);
                    }

                    const raw = await resp.text();
                    let data;
                    try {
                        data = JSON.parse(raw);
                    } catch (e) {
                        throw {
                            kind: 'invalid_json',
                            httpStatus: resp.status,
                            message: 'The server did not return valid JSON (possible PHP error or proxy timeout).',
                            detail: raw.slice(0, 800)
                        };
                    }

                    if (!resp.ok) {
                        throw {
                            kind: 'http',
                            httpStatus: resp.status,
                            message: 'HTTP ' + resp.status,
                            detail: data
                        };
                    }

                    if (!data.success) {
                        throw {
                            kind: 'wp_error',
                            message: wpErrorMessage(data.data),
                            detail: data.data
                        };
                    }

                    return data;
                }

                function appendNonce(fd) {
                    fd.append('_wpnonce', NONCE);
                }

                errorClose.addEventListener('click', function() {
                    progressContainer.style.display = 'none';
                    hideError();
                    btn.disabled = false;
                });

                btn.addEventListener('click', async function() {
                    const file = fileInput.files[0];
                    const auctionRadio = document.querySelector('input[name="auction_id"]:checked');
                    const auctionId = auctionRadio ? auctionRadio.value : null;

                    if (!file) {
                        alert('Select a ZIP');
                        return;
                    }
                    if (!auctionId) {
                        alert('Select an auction');
                        return;
                    }

                    hideError();
                    btn.disabled = true;
                    filesTotal = 0;
                    filesUploaded = 0;
                    lotsTotal = 0;
                    progressContainer.style.display = 'flex';
                    setUi(0, 'Preparing upload…');

                    let runDir = 'run-' + Date.now() + '-' + Math.random().toString(36).substr(2, 6);
                    runDirInput.value = runDir;

                    const chunkSize = 200 * 1024 * 1024;
                    let offset = 0;
                    let index = 0;
                    const totalChunks = Math.max(1, Math.ceil(file.size / chunkSize));

                    try {
                        while (offset < file.size) {
                            const chunk = file.slice(offset, offset + chunkSize);
                            const fd = new FormData();
                            fd.append('action', 'hnh_bulk_images_upload_chunk');
                            fd.append('chunk', chunk);
                            fd.append('chunk_index', index);
                            fd.append('chunk_size', chunkSize);
                            fd.append('chunk_run_dir', runDir);
                            appendNonce(fd);

                            setUi(
                                Math.floor((offset / file.size) * 45),
                                'Uploading ZIP to server… part ' + (index + 1) + ' of ' + totalChunks
                            );

                            await new Promise(function(resolve, reject) {
                                const xhr = new XMLHttpRequest();
                                xhr.open('POST', ajaxurl);
                                xhr.onload = function() {
                                    try {
                                        const data = JSON.parse(xhr.responseText);
                                        if (!data.success) {
                                            reject({
                                                kind: 'wp_error',
                                                message: wpErrorMessage(data.data) || ('Error uploading chunk #' + index)
                                            });
                                            return;
                                        }
                                        resolve(data);
                                    } catch (e) {
                                        reject({
                                            kind: 'invalid_json',
                                            message: 'Invalid server response on chunk #' + index,
                                            detail: xhr.responseText.slice(0, 800)
                                        });
                                    }
                                };
                                xhr.onerror = function() {
                                    reject({
                                        kind: 'network',
                                        message: 'Network error on chunk #' + index
                                    });
                                };
                                xhr.upload.onprogress = function(e) {
                                    if (e.lengthComputable) {
                                        const totalUploaded = offset + e.loaded;
                                        const pct = Math.floor((totalUploaded / file.size) * 45);
                                        setUi(pct, 'Uploading ZIP… ' + Math.round((totalUploaded / file.size) * 100) + '% of file');
                                    }
                                };
                                xhr.send(fd);
                            });

                            offset += chunkSize;
                            index++;
                        }

                        const fdFinal = new FormData();
                        fdFinal.append('action', 'hnh_bulk_images_finalize');
                        fdFinal.append('chunk_run_dir', runDir);
                        appendNonce(fdFinal);

                        setUi(48, 'Assembling ZIP on server…');
                        await ajaxJson(fdFinal, 120000);

                        const fdPrepare = new FormData();
                        fdPrepare.append('action', 'hnh_bulk_images_prepare');
                        fdPrepare.append('chunk_run_dir', runDir);
                        fdPrepare.append('auction_id', auctionId);
                        appendNonce(fdPrepare);

                        setUi(52, 'Scanning ZIP and matching vehicles…');
                        const prepareData = await ajaxJson(fdPrepare, AJAX_TIMEOUT_PREPARE_MS);
                        const prep = prepareData.data || {};

                        filesTotal = prep.files_found || 0;
                        filesUploaded = 0;
                        lotsTotal = prep.total_lots || 0;
                        setCounter();

                        setUi(
                            55,
                            prep.status_message || ('Ready: ' + filesTotal + ' images in ' + lotsTotal + ' lots')
                        );

                        let done = false;
                        while (!done) {
                            const fdBatch = new FormData();
                            fdBatch.append('action', 'hnh_bulk_images_process_batch');
                            fdBatch.append('chunk_run_dir', runDir);
                            appendNonce(fdBatch);

                            const batchData = await ajaxJson(fdBatch, AJAX_TIMEOUT_BATCH_MS);
                            const batch = batchData.data || {};

                            applyBatchProgress(batch);

                            done = !!batch.done;
                            const lotsDone = batch.lots_processed || 0;
                            const lotsTotalNow = batch.total_lots || lotsTotal;
                            const pct = filesTotal > 0
                                ? 55 + Math.floor((filesUploaded / filesTotal) * 40)
                                : (lotsTotalNow > 0
                                    ? 55 + Math.floor((lotsDone / lotsTotalNow) * 40)
                                    : 95);

                            let statusLine = batch.status_message || ('Processing lots ' + lotsDone + ' / ' + lotsTotalNow);
                            if (lotsTotalNow > 0 && statusLine.indexOf('lots ') === -1) {
                                statusLine += ' · lots ' + lotsDone + '/' + lotsTotalNow;
                            }

                            setUi(Math.min(pct, done ? 100 : 95), statusLine);
                        }

                        setUi(100, 'Done — ' + filesUploaded + '/' + filesTotal + ' images. Reloading report…');
                        window.location.reload();
                    } catch (err) {
                        const title = err.kind === 'timeout' ? 'Timeout'
                            : err.kind === 'invalid_json' ? 'Invalid server response'
                            : err.kind === 'http' ? ('HTTP ' + (err.httpStatus || ''))
                            : 'Upload failed';

                        showError(title, err.message || 'An error occurred', err.detail || null);
                        setUi(
                            parseInt(progressBar.style.width, 10) || 0,
                            'Stopped due to an error. See details below or reload to check for a partial report.'
                        );
                    } finally {
                        btn.disabled = false;
                    }
                });
            })();
            </script>

            <?php if ($report): ?>
            <div class="hnh-bulk-card hnh-report">
                <h2>Report</h2>

                <?php if (!empty($report['debug'])): ?>
                <p class="hnh-help">
                    <strong>Debug</strong><!-- <br>
                    uploads_basedir: <code><?php echo esc_html($report['debug']['uploads_basedir'] ?? ''); ?></code><br>
                    run_dir: <code><?php echo esc_html($report['debug']['run_dir'] ?? ''); ?></code>--><br>
                    <!-- extracted_ok:
                    <code><?php echo esc_html(($report['debug']['extracted_ok'] ?? '') ? 'true' : 'false'); ?></code><br> -->
                    lots_in_zip: <code><?php echo esc_html(implode(', ', $report['debug']['lots_in_zip'] ?? [])); ?></code><br>
                    vehicles_matched: <code><?php echo esc_html((string)($report['debug']['vehicles_matched_count'] ?? count($report['debug']['vehicles_found'] ?? []))); ?></code><br>
                    auction_search:
                    <code><?php echo esc_html(implode(', ', array_map('strval', $report['debug']['auction_search_values'] ?? []))); ?></code><br>
                    vehicle_lots:
                    <code><?php echo esc_html(implode(', ', array_map('strval', $report['debug']['vehicles_found'] ?? []))); ?></code>
                </p>
                <?php endif; ?>

                <p>
                    <span class="hnh-badge ok">Files found: <?php echo (int)($report['files_found'] ?? 0); ?></span>
                    <span class="hnh-badge ok">Uploaded: <?php echo (int)($report['uploaded'] ?? 0); ?></span>
                    <span class="hnh-badge ok">Vehicles replaced: <?php echo (int)($report['vehicles_replaced'] ?? 0); ?></span>
                    <span class="hnh-badge warn">Skipped: <?php echo (int)($report['skipped'] ?? 0); ?></span>
                    <span class="hnh-badge err">Missing vehicle: <?php echo (int)($report['missing_vehicle'] ?? 0); ?></span>
                </p>

                <?php if (!empty($report['messages'])): ?>
                <table>
                    <thead>
                        <tr>
                            <th style="width:160px;">Status</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report['messages'] as $m): ?>
                        <tr>
                            <td>
                                <?php
                                    $cls = 'warn';
                                    if (($m['type'] ?? '') === 'ok')  $cls = 'ok';
                                    if (($m['type'] ?? '') === 'err') $cls = 'err';
                                ?>
                                <span class="hnh-badge <?php echo esc_attr($cls); ?>">
                                    <?php echo esc_html(strtoupper($m['type'] ?? '')); ?>
                                </span>
                            </td>
                            <td><?php echo wp_kses_post($m['text'] ?? ''); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }

    public static function uploadChunk(): void
    {
        check_admin_referer('hnh_bulk_images_upload');

        if (!isset($_FILES['chunk']['tmp_name'])) {
            self::jsonError('no_chunk', 'No chunk received');
        }

        $run_dir     = sanitize_text_field($_POST['chunk_run_dir'] ?? '');
        $chunk_index = isset($_POST['chunk_index']) ? (int) $_POST['chunk_index'] : 0;
        $chunk_size  = isset($_POST['chunk_size']) ? (int) $_POST['chunk_size'] : 0;

        if (!$run_dir) {
            self::jsonError('invalid_run_dir', 'Invalid run dir');
        }

        $uploads = wp_upload_dir();
        $base_dir = trailingslashit($uploads['basedir']) . self::WORKING_SUBDIR;
        wp_mkdir_p($base_dir);

        $run_dir_path = trailingslashit($base_dir) . sanitize_file_name($run_dir);
        wp_mkdir_p($run_dir_path);

        $zip_path = trailingslashit($run_dir_path) . 'upload.zip';
        $offset = $chunk_index * $chunk_size;

        $in = fopen($_FILES['chunk']['tmp_name'], 'rb');
        $out = fopen($zip_path, 'c+b');

        if (!$in || !$out) {
            self::jsonError('stream_error', 'Cannot open file streams');
        }

        fseek($out, $offset);

        while ($buff = fread($in, 1048576)) {
            fwrite($out, $buff);
        }

        fclose($in);
        fclose($out);
        clearstatcache();

        wp_send_json_success([
            'written_size' => file_exists($zip_path) ? filesize($zip_path) : 0,
            'chunk_index'  => $chunk_index,
        ]);
    }

    public static function finalizeChunks(): void
    {
        check_admin_referer('hnh_bulk_images_upload');

        $ctx = self::resolveRunContext(sanitize_text_field($_POST['chunk_run_dir'] ?? ''));
        if (!$ctx) {
            self::jsonError('invalid_run_dir', 'Run dir does not exist');
        }

        clearstatcache();

        wp_send_json_success([
            'zip_path' => $ctx['zip_path'],
            'size'     => filesize($ctx['zip_path']),
        ]);
    }

    public static function prepareJob(): void
    {
        self::raiseLimits();
        check_admin_referer('hnh_bulk_images_upload');

        $run_dir = sanitize_text_field($_POST['chunk_run_dir'] ?? '');
        $auction_id = isset($_POST['auction_id']) ? (int) $_POST['auction_id'] : 0;

        if (!$run_dir) {
            self::jsonError('invalid_run_dir', 'Invalid run_dir');
        }
        if (!$auction_id) {
            self::jsonError('invalid_auction', 'Invalid auction');
        }

        $ctx = self::resolveRunContext($run_dir);
        if (!$ctx) {
            self::jsonError('zip_not_found', 'ZIP not found');
        }

        $report = self::emptyReport();
        $uploads = wp_upload_dir();
        $report['debug']['uploads_basedir'] = $uploads['basedir'];
        $report['debug']['run_dir'] = $ctx['run_dir_path'];

        $extracted = self::buildZipManifest($ctx['zip_path'], $report);
        if (!$extracted['ok']) {
            self::jsonError('zip_open_failed', $extracted['message'] ?? 'Cannot open ZIP');
        }

        $auction_debug = [];
        $vehicles_map = self::getVehiclesMapForAuction($auction_id, $auction_debug);
        $report['debug'] = array_merge($report['debug'], $auction_debug);

        $lot_keys = array_keys($extracted['vehicle_images']);
        sort($lot_keys, SORT_NATURAL);

        $report['debug']['lots_in_zip'] = array_map(
            static fn($k) => $extracted['vehicle_images'][$k]['label'] ?? $k,
            $lot_keys
        );

        if (empty($vehicles_map)) {
            $report['messages'][] = [
                'type' => 'warn',
                'text' => 'No vehicles matched this auction. Check <code>' . esc_html(self::META_AUCTION) . '</code> on a vehicle (auction ID or sale number).',
            ];
        }

        $sale_number = function_exists('get_field') ? get_field('sale_number', $auction_id) : '';

        $job = [
            'run_dir'              => $run_dir,
            'run_dir_path'         => $ctx['run_dir_path'],
            'zip_path'             => $ctx['zip_path'],
            'auction_id'           => $auction_id,
            'sale_number'          => $sale_number,
            'vehicle_images'       => $extracted['vehicle_images'],
            'lot_keys'             => $lot_keys,
            'lot_index'            => 0,
            'image_index'          => 0,
            'pending_attachments'  => [],
            'vehicles_map'         => $vehicles_map,
            'report'               => $report,
        ];

        if (!self::saveJob($run_dir, $job)) {
            self::jsonError('job_save_failed', 'Could not save processing job to disk.');
        }

        $matched = count($vehicles_map);
        $status = sprintf(
            'ZIP ready: %d image(s) in %d lot(s). %d vehicle(s) linked to this auction.',
            (int) $report['files_found'],
            count($lot_keys),
            $matched
        );

        wp_send_json_success([
            'total_lots'       => count($lot_keys),
            'files_found'      => (int) $report['files_found'],
            'vehicles_matched' => $matched,
            'status_message'   => $status,
        ]);
    }

    public static function processBatch(): void
    {
        self::raiseLimits();
        check_admin_referer('hnh_bulk_images_upload');

        try {
            self::processBatchInner();
        } finally {
            self::resetS3Client();
        }
    }

    private static function processBatchInner(): void
    {
        $run_dir = sanitize_text_field($_POST['chunk_run_dir'] ?? '');
        if (!$run_dir) {
            self::jsonError('invalid_run_dir', 'Invalid run_dir');
        }

        $job = self::loadJob($run_dir);
        if (!is_array($job)) {
            self::jsonError('job_not_found', 'Processing job not found. Please upload the ZIP again.');
        }

        $zip_path = (string) ($job['zip_path'] ?? '');
        if ($zip_path === '' || !file_exists($zip_path)) {
            self::jsonError('zip_not_found', 'ZIP file missing. Please upload again.');
        }

        $zip = new ZipArchive();
        if ($zip->open($zip_path) !== true) {
            self::jsonError('zip_open_failed', 'Cannot open ZIP for processing.');
        }

        $lot_keys      = $job['lot_keys'] ?? [];
        $total_lots    = count($lot_keys);
        $lot_index     = (int) ($job['lot_index'] ?? 0);
        $image_index   = (int) ($job['image_index'] ?? 0);
        $pending_ids   = array_map('intval', (array) ($job['pending_attachments'] ?? []));
        $vehicles_map  = $job['vehicles_map'] ?? [];
        $report        = &$job['report'];
        $files_total   = (int) ($report['files_found'] ?? 0);
        $images_in_batch = 0;
        $last_status   = '';

        while ($images_in_batch < self::BATCH_IMAGE_COUNT && $lot_index < $total_lots) {
            $norm_lot = $lot_keys[$lot_index];
            $entry    = $job['vehicle_images'][$norm_lot] ?? null;

            if (!$entry || empty($entry['images']) || !is_array($entry['images'])) {
                $lot_index++;
                $image_index = 0;
                $pending_ids = [];
                continue;
            }

            $label  = (string) ($entry['label'] ?? $norm_lot);
            $images = $entry['images'];
            ksort($images, SORT_NUMERIC);
            $orders = array_keys($images);

            if (!isset($vehicles_map[$norm_lot])) {
                if ($image_index === 0) {
                    $report['missing_vehicle']++;
                    $report['messages'][] = [
                        'type' => 'warn',
                        'text' => 'Vehicle not found for lot <code>' . esc_html($label) . '</code>',
                    ];
                    $report['last_status'] = sprintf(
                        'Lot %s: no vehicle — skipping %d image(s) (%d/%d total)',
                        $label,
                        count($images),
                        (int) ($report['uploaded'] ?? 0),
                        $files_total
                    );
                    $last_status = (string) $report['last_status'];
                }

                $lot_index++;
                $image_index = 0;
                $pending_ids = [];
                continue;
            }

            $vehicle_id = (int) $vehicles_map[$norm_lot];

            while ($images_in_batch < self::BATCH_IMAGE_COUNT && $image_index < count($orders)) {
                $order     = $orders[$image_index];
                $zip_entry = (string) ($images[$order] ?? '');

                $report['last_status'] = sprintf(
                    'Lot %s: uploading image %d/%d to S3 (%d/%d total)',
                    $label,
                    $image_index + 1,
                    count($orders),
                    (int) ($report['uploaded'] ?? 0),
                    $files_total
                );
                $last_status = (string) $report['last_status'];

                $tmp = self::extractZipEntryToTemp($zip, $zip_entry);
                if (!$tmp) {
                    $report['skipped']++;
                    $report['messages'][] = [
                        'type' => 'warn',
                        'text' => 'Could not read from ZIP: <code>' . esc_html(basename($zip_entry)) . '</code> (lot ' . esc_html($label) . ')',
                    ];
                    $image_index++;
                    $images_in_batch++;
                    continue;
                }

                $attach_id = self::sideloadImageAsAttachment($tmp, $vehicle_id);
                if (file_exists($tmp)) {
                    unlink($tmp);
                }

                if ($attach_id) {
                    $pending_ids[] = $attach_id;
                    $report['uploaded']++;
                    $report['last_status'] = sprintf(
                        'Lot %s: saved image %d/%d (%d/%d total)',
                        $label,
                        $image_index + 1,
                        count($orders),
                        (int) $report['uploaded'],
                        $files_total
                    );
                } else {
                    $report['skipped']++;
                    $report['messages'][] = [
                        'type' => 'warn',
                        'text' => 'Could not upload: <code>' . esc_html(basename($zip_entry)) . '</code> (lot ' . esc_html($label) . ', ' . ($image_index + 1) . '/' . count($orders) . ')',
                    ];
                }

                $last_status = (string) $report['last_status'];
                $image_index++;
                $images_in_batch++;
            }

            if ($image_index >= count($orders)) {
                if ($pending_ids) {
                    $report['last_status'] = sprintf(
                        'Lot %s: updating gallery (%d image(s))…',
                        $label,
                        count($pending_ids)
                    );
                    $last_status = (string) $report['last_status'];

                    if (function_exists('update_field')) {
                        $field_key = self::getAcfFieldKey(self::GALLERY_FIELD, $vehicle_id);
                        if ($field_key) {
                            update_field($field_key, $pending_ids, $vehicle_id);
                        } else {
                            update_field(self::GALLERY_FIELD, $pending_ids, $vehicle_id);
                        }
                    }

                    $report['vehicles_replaced']++;
                    $report['messages'][] = [
                        'type' => 'ok',
                        'text' => 'Vehicle <strong>#' . $vehicle_id . '</strong> (lot <code>' . esc_html($label) . '</code>) gallery replaced with ' . count($pending_ids) . ' image(s).',
                    ];
                    $report['last_status'] = sprintf(
                        'Lot %s: gallery updated (%d/%d images total)',
                        $label,
                        (int) $report['uploaded'],
                        $files_total
                    );
                    $last_status = (string) $report['last_status'];
                }

                $lot_index++;
                $image_index = 0;
                $pending_ids = [];
            }
        }

        $zip->close();

        $job['lot_index']           = $lot_index;
        $job['image_index']         = $image_index;
        $job['pending_attachments'] = $pending_ids;

        $done   = $lot_index >= $total_lots;
        $report = $job['report'];

        if ($done) {
            $report['debug']['run_dir'] = $job['run_dir_path'] ?? '';

            set_transient(self::reportKey(), $report, self::REPORT_TTL_SECONDS);
            self::deleteJob($run_dir);

            if (!empty($job['run_dir_path']) && is_dir($job['run_dir_path'])) {
                self::deleteRunDir($job['run_dir_path']);
            }

            $last_status = sprintf(
                'Finished: %d/%d images uploaded · %d vehicle(s) updated · %d missing lot(s)',
                (int) ($report['uploaded'] ?? 0),
                (int) ($report['files_found'] ?? 0),
                (int) ($report['vehicles_replaced'] ?? 0),
                (int) ($report['missing_vehicle'] ?? 0)
            );
        } else {
            if (!self::saveJob($run_dir, $job)) {
                self::jsonError('job_save_failed', 'Could not save job progress. Check disk space.');
            }

            if (!$last_status) {
                $last_status = sprintf(
                    'Processed lots %d/%d · %d/%d images',
                    $lot_index,
                    $total_lots,
                    (int) ($report['uploaded'] ?? 0),
                    $files_total
                );
            }
        }

        wp_send_json_success([
            'done'              => $done,
            'lots_processed'    => $lot_index,
            'total_lots'        => $total_lots,
            'files_found'       => (int) ($report['files_found'] ?? 0),
            'uploaded'          => (int) ($report['uploaded'] ?? 0),
            'skipped'           => (int) ($report['skipped'] ?? 0),
            'vehicles_replaced' => (int) ($report['vehicles_replaced'] ?? 0),
            'status_message'    => $last_status,
        ]);
    }

    private static function ensureAwsSdkLoaded(): bool
    {
        if (class_exists(\Aws\S3\S3Client::class)) {
            return true;
        }

        $autoloaders = array_unique([
            WP_PLUGIN_DIR . '/s3-upload/vendor/autoload.php',
            get_template_directory() . '/vendor/autoload.php',
            get_stylesheet_directory() . '/vendor/autoload.php',
        ]);

        $composerLoaded = class_exists(\Composer\Autoload\ClassLoader::class);

        foreach ($autoloaders as $autoload) {
            if (!is_readable($autoload)) {
                continue;
            }

            if ($composerLoaded) {
                if (class_exists(\Aws\S3\S3Client::class)) {
                    return true;
                }
                continue;
            }

            require_once $autoload;
            $composerLoaded = class_exists(\Composer\Autoload\ClassLoader::class);

            if (class_exists(\Aws\S3\S3Client::class)) {
                return true;
            }
        }

        return false;
    }

    private static function getS3Client(): ?\Aws\S3\S3Client
    {
        if (self::$s3Client !== null) {
            return self::$s3Client;
        }

        if (!self::ensureAwsSdkLoaded()) {
            return null;
        }

        if (!defined('AWS_BUCKET') || !defined('AWS_REGION') || !defined('AWS_KEY') || !defined('AWS_SECRET')) {
            return null;
        }

        self::$s3Client = new \Aws\S3\S3Client([
            'version'     => 'latest',
            'region'      => AWS_REGION,
            'credentials' => [
                'key'    => AWS_KEY,
                'secret' => AWS_SECRET,
            ],
        ]);

        return self::$s3Client;
    }

    private static function resetS3Client(): void
    {
        self::$s3Client = null;
    }

    /**
     * @param array<int, array{path:string,key:string,content_type:string}> $items
     */
    private static function uploadToS3Pool(string $bucket, array $items): void
    {
        if (empty($items)) {
            return;
        }

        $s3 = self::getS3Client();
        if (!$s3) {
            throw new \RuntimeException('S3 client is not available');
        }

        $commands = [];
        foreach ($items as $item) {
            $commands[] = $s3->getCommand('PutObject', [
                'Bucket'      => $bucket,
                'Key'         => $item['key'],
                'SourceFile'  => $item['path'],
                'ContentType' => $item['content_type'],
            ]);
        }

        $pool = new \Aws\CommandPool($s3, $commands, [
            'concurrency' => self::S3_UPLOAD_CONCURRENCY,
        ]);

        $pool->promise()->wait();
    }

    /**
     * Scan ZIP and build manifest (zip entry paths only — no extraction).
     *
     * @return array{ok:bool,message?:string,vehicle_images:array<string,array{label:string,images:array<int,string>}>}
     */
    private static function buildZipManifest(string $zip_path, array &$report): array
    {
        $vehicle_images = [];
        $zip = new ZipArchive();

        if ($zip->open($zip_path) !== true) {
            return ['ok' => false, 'message' => 'Cannot open ZIP', 'vehicle_images' => []];
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name === false || substr($name, -1) === '/') {
                continue;
            }

            $filename = basename($name);
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                $report['skipped']++;
                continue;
            }

            $report['files_found']++;

            $parsed = self::parseFilenameToLotAndOrder($filename);
            if (!$parsed) {
                $report['skipped']++;
                $report['messages'][] = [
                    'type' => 'warn',
                    'text' => 'Skipped (invalid filename): <code>' . esc_html($filename) . '</code>',
                ];
                continue;
            }

            [$lot, $order] = $parsed;
            $norm_lot = self::norm($lot);

            if (!isset($vehicle_images[$norm_lot])) {
                $vehicle_images[$norm_lot] = [
                    'label'  => $lot,
                    'images' => [],
                ];
            }

            $vehicle_images[$norm_lot]['images'][$order] = $name;
        }

        $zip->close();

        return ['ok' => true, 'vehicle_images' => $vehicle_images];
    }

    private static function extractZipEntryToTemp(ZipArchive $zip, string $entry_name): ?string
    {
        $stream = $zip->getStream($entry_name);
        if (!$stream) {
            return null;
        }

        $filename  = basename($entry_name);
        $safe_name = sanitize_file_name($filename);
        $tmp       = get_temp_dir() . wp_unique_filename(get_temp_dir(), $safe_name);
        $out       = fopen($tmp, 'wb');

        if (!$out) {
            fclose($stream);
            return null;
        }

        while (!feof($stream)) {
            fwrite($out, fread($stream, 8192));
        }

        fclose($out);
        fclose($stream);

        return is_file($tmp) ? $tmp : null;
    }

    /**
     * @return array<string,int> norm_lot => vehicle post ID
     */
    private static function getVehiclesMapForAuction(int $auction_id, array &$debug): array
    {
        $sale_number = function_exists('get_field') ? get_field('sale_number', $auction_id) : '';

        $search_values = array_values(array_unique(array_filter([
            (string) $auction_id,
            (string) (int) $auction_id,
            ($sale_number !== '' && $sale_number !== null) ? (string) $sale_number : null,
        ], static fn($v) => $v !== null && $v !== '')));

        $debug['auction_id'] = $auction_id;
        $debug['sale_number'] = $sale_number;
        $debug['auction_search_values'] = $search_values;

        if (empty($search_values)) {
            $debug['vehicles_matched_count'] = 0;
            $debug['vehicles_found'] = [];
            return [];
        }

        $vehicles = get_posts([
            'post_type'      => self::VEHICLE_CPT,
            'posts_per_page' => -1,
            'post_status'    => 'any',
            'fields'         => 'ids',
            'meta_query'     => [
                [
                    'key'     => self::META_AUCTION,
                    'value'   => $search_values,
                    'compare' => 'IN',
                ],
            ],
        ]);

        $map = [];
        $labels = [];

        foreach ($vehicles as $vehicle_id) {
            $vehicle_id = (int) $vehicle_id;
            $lot = get_post_meta($vehicle_id, self::META_LOT, true);
            if ($lot === '' || $lot === null) {
                continue;
            }
            $norm = self::norm((string) $lot);
            $map[$norm] = $vehicle_id;
            $labels[] = (string) $lot;
        }

        $debug['vehicles_matched_count'] = count($map);
        $debug['vehicles_found'] = $labels;

        return $map;
    }

    private static function emptyReport(): array
    {
        return [
            'files_found'       => 0,
            'uploaded'          => 0,
            'vehicles_replaced' => 0,
            'skipped'           => 0,
            'missing_vehicle'   => 0,
            'messages'          => [],
            'debug'             => [],
        ];
    }

    private static function raiseLimits(): void
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');
    }

    private static function jsonError(string $code, string $message, $detail = null): void
    {
        $payload = [
            'code'    => $code,
            'message' => $message,
        ];
        if ($detail !== null) {
            $payload['detail'] = $detail;
        }
        wp_send_json_error($payload);
    }

    private static function jobFilePath(string $run_dir): ?string
    {
        $ctx = self::resolveRunContext($run_dir);
        if (!$ctx) {
            return null;
        }

        return trailingslashit($ctx['run_dir_path']) . self::JOB_FILE;
    }

    private static function saveJob(string $run_dir, array $job): bool
    {
        $path = self::jobFilePath($run_dir);
        if (!$path) {
            return false;
        }

        $json = wp_json_encode($job);
        if ($json === false) {
            return false;
        }

        return file_put_contents($path, $json, LOCK_EX) !== false;
    }

    private static function loadJob(string $run_dir): ?array
    {
        $path = self::jobFilePath($run_dir);
        if (!$path || !is_readable($path)) {
            return null;
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            return null;
        }

        $data = json_decode($raw, true);

        return is_array($data) ? $data : null;
    }

    private static function deleteJob(string $run_dir): void
    {
        $path = self::jobFilePath($run_dir);
        if ($path && file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * @return array{run_dir_path:string,zip_path:string}|null
     */
    private static function resolveRunContext(string $run_dir): ?array
    {
        if ($run_dir === '') {
            return null;
        }

        $uploads = wp_upload_dir();
        $run_dir_path = trailingslashit($uploads['basedir']) . self::WORKING_SUBDIR . '/' . sanitize_file_name($run_dir);

        if (!is_dir($run_dir_path)) {
            return null;
        }

        $zip_path = trailingslashit($run_dir_path) . 'upload.zip';
        if (!file_exists($zip_path)) {
            return null;
        }

        return [
            'run_dir_path' => $run_dir_path,
            'zip_path'     => $zip_path,
        ];
    }

    /**
     * Accept:
     * 123-1.jpg, 123_1.jpg, 123.1.jpg, 123.(1).jpg, 123(1).jpg, 45p.jpg, 59p-2.jpg
     * => returns [lot, order] (order default 1)
     */
    private static function parseFilenameToLotAndOrder(string $filename): ?array
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $name = trim((string)$name);
        $name = preg_replace('/\s+/', '', $name);

        // lot: alphanum (supports 45p)
        // order: optional number after separator or in parentheses
        if (preg_match('/^([A-Za-z0-9]+)(?:[\\-_.]?(?:\\(?([0-9]+)\\)?))?$/', $name, $m)) {
            $lot = (string)$m[1];
            $ord = (isset($m[2]) && $m[2] !== '') ? (int)$m[2] : 1;
            return [$lot, max(1, $ord)];
        }

        return null;
    }

    private static function generateImageHash(string $file_path): string
    {
        return substr(md5($file_path . microtime(true) . wp_rand()), 0, 8);
    }

    private static function sideloadImageAsAttachment(string $path, int $parent_post_id)
    {
        global $wpdb;

        $bucket = defined('AWS_BUCKET') ? AWS_BUCKET : null;
        if (!$bucket || !self::getS3Client()) {
            return null;
        }

        require_once ABSPATH . 'wp-admin/includes/image.php';

        $filename   = sanitize_file_name(basename($path));
        $filetype   = wp_check_filetype($filename, null);
        $upload_dir = wp_upload_dir();

        $current_time = current_time('mysql');
        $current_gmt  = current_time('mysql', 1);

        $hash = substr(md5($path . $parent_post_id), 0, 8);

        $base_filename_raw = pathinfo($filename, PATHINFO_FILENAME);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $unique_filename = wp_unique_filename(
            wp_upload_dir()['path'],
            $base_filename_raw . '-' . $hash . '.' . $ext
        );

        $base_filename = $unique_filename;

        $relative_dir = date('Y/m');

        $base_key = "uploads/{$relative_dir}/{$base_filename}";

        $wpdb->insert($wpdb->posts, [
            'post_author'    => get_current_user_id(),
            'post_title'     => $base_filename,
            'post_status'    => 'inherit',
            'post_parent'    => $parent_post_id,
            'post_type'      => 'attachment',
            'post_date'      => $current_time,
            'post_date_gmt'  => $current_gmt,
            'post_modified'  => $current_time,
            'post_modified_gmt' => $current_gmt,
            'guid'           => $upload_dir['url'] . '/' . $base_key,
            'post_mime_type' => $filetype['type']
        ]);

        $attach_id = $wpdb->insert_id;
        if (!$attach_id) {
            return null;
        }

        $metadata = wp_generate_attachment_metadata($attach_id, $path);

        $s3 = self::getS3Client();
        $upload_items = [
            [
                'path'          => $path,
                'key'           => $base_key,
                'content_type'  => $filetype['type'] ?: 'application/octet-stream',
            ],
        ];

        $size_files_to_unlink = [];

        if (!empty($metadata['sizes'])) {
            foreach ($metadata['sizes'] as $size_name => $size) {
                $size_file = dirname($path) . '/' . $size['file'];

                if (!file_exists($size_file)) {
                    continue;
                }

                $size_filename = $size['width'] . 'x' . $size['height'] . '-' . $hash . '.jpg';
                $size_key = "uploads/{$relative_dir}/{$size_filename}";

                $upload_items[] = [
                    'path'         => $size_file,
                    'key'          => $size_key,
                    'content_type' => $size['mime-type'] ?? 'image/jpeg',
                ];

                $metadata['sizes'][$size_name]['file'] = $size_filename;
                $size_files_to_unlink[] = $size_file;
            }
        }

        try {
            self::uploadToS3Pool($bucket, $upload_items);
        } catch (\Throwable $e) {
            error_log('[HNH Bulk Images] S3 upload failed for attachment ' . $attach_id . ': ' . $e->getMessage());
            wp_delete_attachment($attach_id, true);
            return null;
        }

        if (!empty($metadata['sizes']) && $s3) {
            foreach ($metadata['sizes'] as $size_name => $size) {
                if (empty($metadata['sizes'][$size_name]['file'])) {
                    continue;
                }
                $size_key = "uploads/{$relative_dir}/" . $metadata['sizes'][$size_name]['file'];
                $metadata['sizes'][$size_name]['url'] = $s3->getObjectUrl($bucket, $size_key);
            }
        }

        foreach ($size_files_to_unlink as $size_file) {
            if (file_exists($size_file)) {
                unlink($size_file);
            }
        }

        wp_update_attachment_metadata($attach_id, $metadata);

        $url = $s3->getObjectUrl($bucket, $base_key);

        update_post_meta($attach_id, '_s3_url', $url);
        update_post_meta($attach_id, '_s3_hash', $hash);
        update_post_meta($attach_id, '_s3_original_key', $base_key);

        if (file_exists($path)) {
            unlink($path);
        }

        return $attach_id;
    }

    private static function listFilesRecursive(string $dir): array
    {
        $out = [];
        if (!is_dir($dir)) return $out;

        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($it as $f) {
            $out[] = $f->getPathname();
        }

        return $out;
    }

    private static function norm(string $v): string
    {
        $v = trim($v);
        $v = preg_replace('/\s+/', '', $v);
        return strtolower((string)$v);
    }

    private static function reportKey(): string
    {
        return 'hnh_bulk_images_report_' . get_current_user_id();
    }

    private static function redirectBackWithReport(array $report): void
    {
        set_transient(self::reportKey(), $report, self::REPORT_TTL_SECONDS);

        $ref = wp_get_referer();

        if (!$ref) {
            $ref = admin_url('edit.php?post_type=' . self::AUCTION_CPT . '&page=' . self::MENU_SLUG);
        }

        wp_safe_redirect($ref);

        exit;
    }

    private static function getAcfFieldKey(string $field_name, int $post_id): string
    {
        if (!function_exists('acf_get_field_groups') || !function_exists('acf_get_fields')) return '';

        $groups = acf_get_field_groups(['post_id' => $post_id]);
        if (!$groups) return '';

        foreach ($groups as $group) {
            $fields = acf_get_fields($group);
            if (!$fields) continue;

            $key = self::findFieldKeyRecursive($fields, $field_name);
            if ($key) return $key;
        }

        return '';
    }

    private static function findFieldKeyRecursive(array $fields, string $field_name): string
    {
        foreach ($fields as $f) {
            if (!is_array($f)) continue;

            if (($f['name'] ?? '') === $field_name && !empty($f['key'])) {
                return (string)$f['key'];
            }

            if (!empty($f['sub_fields']) && is_array($f['sub_fields'])) {
                $k = self::findFieldKeyRecursive($f['sub_fields'], $field_name);
                if ($k) return $k;
            }

            // Flexible content layouts can contain sub_fields in layouts
            if (!empty($f['layouts']) && is_array($f['layouts'])) {
                foreach ($f['layouts'] as $layout) {
                    if (!empty($layout['sub_fields']) && is_array($layout['sub_fields'])) {
                        $k = self::findFieldKeyRecursive($layout['sub_fields'], $field_name);
                        if ($k) return $k;
                    }
                }
            }
        }

        return '';
    }

    private static function deleteRunDir(string $run_dir): void
    {
        if (!is_dir($run_dir)) {
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($run_dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($run_dir);
    }
    
}

add_action('delete_attachment', function($post_id) {
    $bucket = defined('AWS_BUCKET') ? AWS_BUCKET : null;
    if (!$bucket) return;

    $s3 = new \Aws\S3\S3Client([
        'version' => 'latest',
        'region'  => AWS_REGION,
        'credentials' => [
            'key'    => AWS_KEY,
            'secret' => AWS_SECRET,
        ],
    ]);

    $base_key = get_post_meta($post_id, '_s3_original_key', true);
    if (!$base_key) return;

    try {

        $dir = dirname($base_key);
        $objects = $s3->listObjectsV2([
            'Bucket' => $bucket,
            'Prefix' => $dir
        ]);

        if (!empty($objects['Contents'])) {
            $delete = [];
            foreach ($objects['Contents'] as $obj) {
                $delete[] = ['Key' => $obj['Key']];
            }
            $s3->deleteObjects([
                'Bucket' => $bucket,
                'Delete' => ['Objects' => $delete]
            ]);
        }

    } catch (\Exception $e) {
        error_log($e->getMessage());
    }

    delete_post_meta($post_id, '_s3_hash');
    delete_post_meta($post_id, '_s3_original_key');
});

add_filter('wp_get_attachment_image_src', function ($image, $attachment_id, $size, $icon) {

    if (!is_admin()) return $image;

    if (!empty($image) && !empty($image[0])) {
        return $image;
    }

    $url = get_post_meta($attachment_id, '_s3_url', true);

    if ($url) {

        return [
            $url,
            0,
            0,
            false 
        ];
    }

    return $image;

}, 10, 4);

HNH_Bulk_Images::boot();