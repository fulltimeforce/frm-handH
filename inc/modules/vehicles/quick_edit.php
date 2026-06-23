<?php



/**
 * Admin Vehicles search: also search by ACF meta lot_number_latest
 * (Keeps default WP search intact and adds OR condition)
 */
add_filter('posts_join', function ($join, $query) {

    if (!is_admin() || !$query->is_main_query()) return $join;
    if ($query->get('post_type') !== 'vehicles') return $join;

    $search = $query->get('s');
    if (empty($search)) return $join;

    global $wpdb;

    // Join only once
    if (strpos($join, 'pm_lot') === false) {
        $join .= " LEFT JOIN {$wpdb->postmeta} pm_lot
                   ON ({$wpdb->posts}.ID = pm_lot.post_id AND pm_lot.meta_key = 'lot_number_latest') ";
    }

    return $join;
}, 10, 2);

add_filter('posts_search', function ($search_sql, $query) {

    if (!is_admin() || !$query->is_main_query()) return $search_sql;
    if ($query->get('post_type') !== 'vehicles') return $search_sql;

    $search = $query->get('s');
    if (empty($search)) return $search_sql;

    global $wpdb;

    // Escapar LIKE seguro
    $like = '%' . $wpdb->esc_like($search) . '%';

    // Agregamos OR por meta_value, sin destruir el search original
    // $search_sql normalmente viene como: AND ((wp_posts.post_title LIKE ...) OR ...)
    $meta_part = $wpdb->prepare(" OR (pm_lot.meta_value LIKE %s) ", $like);

    // Insertar el OR dentro del bloque principal de búsqueda
    if (!empty($search_sql)) {
        $search_sql = preg_replace('/\)\s*\)\s*$/', $meta_part . '))', $search_sql, 1);
    }

    return $search_sql;
}, 10, 2);

add_filter('posts_distinct', function ($distinct, $query) {

    if (!is_admin() || !$query->is_main_query()) return $distinct;
    if ($query->get('post_type') !== 'vehicles') return $distinct;

    $search = $query->get('s');
    if (empty($search)) return $distinct;

    // Evita duplicados por el JOIN
    return 'DISTINCT';
}, 10, 2);






// ---------------------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------

/**
 * QUICK EDIT: ACF "notes" textarea for Vehicles
 */

/** 1) Render textarea in Quick Edit (attach to 'notes' column) */
add_action('quick_edit_custom_box', function ($column_name, $post_type) {

    if ($post_type !== 'vehicles') return;

    // Importante: usar tu columna existente
    if ($column_name !== 'notes') return;
    ?>
    <fieldset class="inline-edit-col-right">
        <div class="inline-edit-col">
            <label class="alignleft" style="width:100%;">
                <span class="title"><?php echo esc_html('Notes'); ?></span>
                <textarea name="vehicle_notes" class="vehicle-notes" rows="5" style="width:100%;"></textarea>
            </label>
        </div>
    </fieldset>
    <?php
}, 10, 2);


/** 2) JS: cargar el valor actual cuando abres Quick Edit */
add_action('admin_footer-edit.php', function () {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->id !== 'edit-vehicles') return;
    ?>
    <script>
        (function($){
            const $wp_inline_edit = inlineEditPost.edit;

            inlineEditPost.edit = function(id) {
                $wp_inline_edit.apply(this, arguments);

                const postId = (typeof id === 'object') ? parseInt(this.getId(id), 10) : parseInt(id, 10);
                if (!postId) return;

                const $row = $('#post-' + postId);
                // Tomamos el texto que ya se ve en la columna Notes (trim)
                const notesText = ($row.find('td.column-notes').text() || '').trim();

                $('#edit-' + postId).find('textarea.vehicle-notes').val(notesText);
            };
        })(jQuery);
    </script>
    <?php
});


/** 3) Guardar Notes al actualizar desde Quick Edit */
add_action('save_post_vehicles', function ($post_id) {

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (!isset($_POST['vehicle_notes'])) return;

    $notes = wp_kses_post(wp_unslash($_POST['vehicle_notes']));

    // Guardar en ACF por field name
    if (function_exists('update_field')) {
        update_field('notes', $notes, $post_id);
    } else {
        update_post_meta($post_id, 'notes', $notes);
    }

}, 10, 1);
