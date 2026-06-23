<?php

function hh_tracking_dashboard()
{
    if (!current_user_can(HH_TRACKING_ADMIN_CAP)) {
        wp_die('You do not have permission to access this page.');
    }

    // Guardar settings
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hh_tracking_settings_nonce'])) {
        if (!wp_verify_nonce($_POST['hh_tracking_settings_nonce'], 'hh_tracking_save_settings')) {
            echo '<div class="notice notice-error"><p>Security check failed.</p></div>';
        } else {

            $sales_manager_user_id = isset($_POST['hh_sales_manager_user_id']) ? (int) $_POST['hh_sales_manager_user_id'] : 0;
            $eval_form_id          = isset($_POST['hh_eval_form_id']) ? (int) $_POST['hh_eval_form_id'] : 0;
            $cond_form_id          = isset($_POST['hh_cond_form_id']) ? (int) $_POST['hh_cond_form_id'] : 0;

            update_option('hh_sales_manager_user_id', $sales_manager_user_id);
            update_option('hh_eval_form_id', $eval_form_id);
            update_option('hh_cond_form_id', $cond_form_id);

            echo '<div class="notice notice-success"><p>Tracking settings saved.</p></div>';
        }
    }

    // Valores actuales
    $current_sales_manager = (int) get_option('hh_sales_manager_user_id', 0);
    $current_eval_form_id  = (int) get_option('hh_eval_form_id', 0);
    $current_cond_form_id  = (int) get_option('hh_cond_form_id', 0);

    // Forms GF
    $gf_forms = [];
    $gf_ok = class_exists('GFAPI');

    if ($gf_ok) {
        // Devuelve forms con: id, title, etc.
        $gf_forms = GFAPI::get_forms();
        if (is_wp_error($gf_forms)) {
            $gf_forms = [];
            $gf_ok = false;
        }
    }
?>
    <div class="wrap">
        <h1>Tracking</h1>
        <p>Configure auto-assignment and form mapping for Tracking.</p>

        <?php if (!$gf_ok): ?>
            <div class="notice notice-warning">
                <p><strong>Gravity Forms</strong> is not available. Please ensure it is installed and activated.</p>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <?php wp_nonce_field('hh_tracking_save_settings', 'hh_tracking_settings_nonce'); ?>

            <table class="form-table" role="presentation">
                <tbody>

                    <?php if (NOT_APPEAR): ?>
                        <tr>
                            <th scope="row"><label for="hh_sales_manager_user_id">Sales Manager</label></th>
                            <td>
                                <select name="hh_sales_manager_user_id" id="hh_sales_manager_user_id" class="regular-text">
                                    <option value="0">— Select user —</option>
                                    <?php foreach (HH_TEAM_USERS as $u): ?>
                                        <option value="<?php echo (int) $u->ID; ?>" <?php selected($current_sales_manager, (int) $u->ID); ?>>
                                            <?php echo esc_html($u->display_name . ' (' . $u->user_login . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">This user will receive all new Evaluation Requests by default.</p>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <tr>
                        <th scope="row"><label for="hh_eval_form_id">Evaluation Requests (Gravity Form)</label></th>
                        <td>
                            <select name="hh_eval_form_id" id="hh_eval_form_id" class="regular-text" <?php echo $gf_ok ? '' : 'disabled'; ?>>
                                <option value="0">— Select form —</option>
                                <?php foreach ($gf_forms as $f): ?>
                                    <option value="<?php echo (int) $f['id']; ?>" <?php selected($current_eval_form_id, (int) $f['id']); ?>>
                                        <?php echo esc_html($f['title'] . ' (ID: ' . $f['id'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">Choose which Gravity Form creates Evaluation Request leads.</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="hh_cond_form_id">Condition Report Request (Gravity Form)</label></th>
                        <td>
                            <select name="hh_cond_form_id" id="hh_cond_form_id" class="regular-text" <?php echo $gf_ok ? '' : 'disabled'; ?>>
                                <option value="0">— Select form —</option>
                                <?php foreach ($gf_forms as $f): ?>
                                    <option value="<?php echo (int) $f['id']; ?>" <?php selected($current_cond_form_id, (int) $f['id']); ?>>
                                        <?php echo esc_html($f['title'] . ' (ID: ' . $f['id'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">Choose which Gravity Form creates Condition Report leads.</p>
                        </td>
                    </tr>

                </tbody>
            </table>

            <?php submit_button('Save Settings'); ?>
        </form>
    </div>
<?php
}
