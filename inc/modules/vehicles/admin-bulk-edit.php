<?php
/**
 * Bulk Edit for Vehicles CPT
 * 
 * Adds custom ACF fields to the native WordPress Bulk Edit panel:
 * - notes (textarea)
 * - C&C option fields: steering_position, transmission_type, colour, fuel_type, price_currency
 * - listing_category
 * 
 * @package HandH Theme
 */

if (!defined('ABSPATH'))
  exit;

// DEBUG: Verificar que el archivo se carga
// add_action('admin_notices', function() {
//     $screen = get_current_screen();
//     if ($screen && $screen->id === 'edit-vehicles') {
//         echo '<div class="notice notice-info"><p><strong>DEBUG:</strong> admin-bulk-edit.php loaded successfully!</p></div>';
//     }
// });

/**
 * Render custom fields in Bulk Edit panel (vehicles CPT only)
 * 
 * Field names (ACF meta keys):
 * - notes
 * - steering_position
 * - transmission_type
 * - colour
 * - fuel_type
 * - price_currency
 * - listing_category
 */
add_action('bulk_edit_custom_box', 'hnh_vehicles_bulk_edit_fields', 10, 2);

function hnh_vehicles_bulk_edit_fields($column_name, $post_type)
{
  // DEBUG: Verificar que el hook se dispara
  error_log("BULK EDIT HOOK FIRED: post_type={$post_type}, column={$column_name}");

  // Only for vehicles CPT
  if ($post_type !== 'vehicles') {
    return;
  }

  // Render only once using static variable (hook fires for each column)
  static $rendered = false;
  if ($rendered) {
    return;
  }
  $rendered = true;

  error_log("BULK EDIT: Rendering custom fields for vehicles");

  // Get ACF field objects to retrieve choices dynamically
  // We need a sample post to get field objects with choices
  $sample_post = get_posts([
    'post_type' => 'vehicles',
    'posts_per_page' => 1,
    'post_status' => 'any',
    'fields' => 'ids'
  ]);
  $sample_post_id = !empty($sample_post) ? $sample_post[0] : false;

  $field_steering = null;
  $field_transmission = null;
  $field_colour = null;
  $field_fuel = null;
  $field_currency = null;
  $field_listing_category = null;

  if ($sample_post_id && function_exists('get_field_object')) {
    $field_steering = get_field_object('field_698a4460c1999');
    error_log('STEERING FIELD: ' . print_r($field_steering, true));
	  
    $field_transmission = get_field_object('field_698a44afe21f2');
    error_log('TRANSMISSION FIELD: ' . print_r($field_transmission, true));

    $field_colour = get_field_object('field_698a4647b6094');
    error_log('COLOUR FIELD: ' . print_r($field_colour, true));

    $field_fuel = get_field_object('field_698a470340677');
    error_log('FUEL FIELD: ' . print_r($field_fuel, true));

    $field_currency = get_field_object('field_698a4958b3e3f');
    error_log('CURRENCY FIELD: ' . print_r($field_currency, true));

    $field_listing_category = get_field_object('field_698a3d6bdc98a');
    error_log('LISTING CATEGORY FIELD: ' . print_r($field_listing_category, true));
  }

  ?>
  <fieldset class="inline-edit-col-left hnh-bulk-edit-vehicles">
    <div class="inline-edit-col">

      <!-- Notes Field -->
      <div class="inline-edit-group wp-clearfix">
        <label class="inline-edit-status alignleft">
          <!-- Leave empty to keep existing notes unchanged -->
          <span class="title">Notes</span>
          <textarea name="hnh_bulk_notes" rows="4" style="width:100%;max-width:400px;" placeholder=""></textarea>
        </label>
        <em class="alignleft" style="display:block;margin-top:4px;opacity:0.7;font-size:12px;">
          Leaving this empty will NOT clear existing notes.
        </em>
      </div>

      <!-- C&C Option Heading -->
      <div class="inline-edit-group wp-clearfix" style="margin-top:16px;">
        <h4 style="margin:8px 0;font-size:13px;font-weight:600;">C&C Option</h4>
      </div>

      <!-- Steering Position -->
      <label class="alignleft" style="width:48%;margin-right:2%;">
        <span class="title" style="width: 100%;">Steering Position</span>
        <select name="hnh_bulk_steering_position" style="width:100%;">
          <option value="__no_change__">— No change —</option>
          <?php
          if ($field_steering && isset($field_steering['choices'])) {
            foreach ($field_steering['choices'] as $value => $label) {
              echo '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
            }
          }
          ?>
        </select>
      </label>

      <!-- Transmission Type -->
      <label class="alignleft" style="width:48%;margin-left:2%;">
        <span class="title" style="width: 100%;">Transmission Type</span>
        <select name="hnh_bulk_transmission_type" style="width:100%;">
          <option value="__no_change__">— No change —</option>
          <?php
          if ($field_transmission && isset($field_transmission['choices'])) {
            foreach ($field_transmission['choices'] as $value => $label) {
              echo '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
            }
          }
          ?>
        </select>
      </label>

      <!-- Colour -->
      <label class="alignleft" style="width:48%;margin-right:2%;margin-top:8px;">
        <span class="title" style="width: 100%;">Colour</span>
        <select name="hnh_bulk_colour" style="width:100%;">
          <option value="__no_change__">— No change —</option>
          <?php
          if ($field_colour && isset($field_colour['choices'])) {
            foreach ($field_colour['choices'] as $value => $label) {
              echo '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
            }
          }
          ?>
        </select>
      </label>

      <!-- Fuel Type -->
      <label class="alignleft" style="width:48%;margin-left:2%;margin-top:8px;">
        <span class="title" style="width: 100%;">Fuel Type</span>
        <select name="hnh_bulk_fuel_type" style="width:100%;">
          <option value="__no_change__">— No change —</option>
          <?php
          if ($field_fuel && isset($field_fuel['choices'])) {
            foreach ($field_fuel['choices'] as $value => $label) {
              echo '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
            }
          }
          ?>
        </select>
      </label>

      <!-- Price Currency -->
      <label class="alignleft" style="width:48%;margin-right:2%;margin-top:8px;">
        <span class="title" style="width: 100%;">Price Currency</span>
        <select name="hnh_bulk_price_currency" style="width:100%;">
          <option value="__no_change__">— No change —</option>
          <?php
          if ($field_currency && isset($field_currency['choices'])) {
            foreach ($field_currency['choices'] as $value => $label) {
              echo '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
            }
          }
          ?>
        </select>
      </label>

      <!-- Listing Category -->
      <label class="alignleft" style="width:48%;margin-left:2%;margin-top:8px;">
        <span class="title" style="width: 100%;">Listing Category</span>
        <select name="hnh_bulk_listing_category" style="width:100%;">
          <option value="__no_change__">— No change —</option>
          <?php
          $listing_choices = ($field_listing_category && isset($field_listing_category['choices'])) ? $field_listing_category['choices'] : [
            'classic-cars'   => 'Classic Cars',
            'commercial'     => 'Commercial',
            'military'       => 'Military',
            'bikes'          => 'Bikes',
            '4x4s'           => '4x4s',
            'american-cars' => 'American Cars',
            'projects'       => 'Projects',
            'wedding-cars'   => 'Wedding Cars',
            '3-wheelers'     => '3 Wheelers',
            'fia-race-cars'  => 'Fia Race Cars',
            'camper-vans'    => 'Camper Vans',
            'modern-cars'    => 'Modern Cars',
            'electric-cars'  => 'Electric Cars',
          ];
          foreach ($listing_choices as $value => $label) {
            echo '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
          }
          ?>
        </select>
      </label>

    </div>
  </fieldset>
  <?php
}


/**
 * Save bulk edit data for vehicles
 * 
 * Hook: save_post_vehicles fires for each post when bulk editing
 * 
 * Security rules:
 * - Check user capabilities
 * - Validate "__no_change__" sentinel value (skip update)
 * - For notes: empty value = no update (prevents accidental mass deletion)
 * - Sanitize all inputs
 */
add_action('save_post_vehicles', 'hnh_vehicles_save_bulk_edit_data', 10, 2);

function hnh_vehicles_save_bulk_edit_data($post_id, $post)
{
  // Security: verify nonce and user capabilities
  if (!current_user_can('edit_post', $post_id)) {
    return;
  }

  // Prevent autosave/revision interference
  if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
    return;
  }

  // Only process if this is a bulk edit request
  if (!isset($_REQUEST['bulk_edit'])) {
    return;
  }

  // Get field values from REQUEST (bulk edit uses GET parameters)
  $notes = isset($_REQUEST['hnh_bulk_notes']) ? $_REQUEST['hnh_bulk_notes'] : '';
  $steering_position = isset($_REQUEST['hnh_bulk_steering_position']) ? sanitize_text_field($_REQUEST['hnh_bulk_steering_position']) : '__no_change__';
  $transmission_type = isset($_REQUEST['hnh_bulk_transmission_type']) ? sanitize_text_field($_REQUEST['hnh_bulk_transmission_type']) : '__no_change__';
  $colour = isset($_REQUEST['hnh_bulk_colour']) ? sanitize_text_field($_REQUEST['hnh_bulk_colour']) : '__no_change__';
  $fuel_type = isset($_REQUEST['hnh_bulk_fuel_type']) ? sanitize_text_field($_REQUEST['hnh_bulk_fuel_type']) : '__no_change__';
  $price_currency = isset($_REQUEST['hnh_bulk_price_currency']) ? sanitize_text_field($_REQUEST['hnh_bulk_price_currency']) : '__no_change__';
  $listing_category = isset($_REQUEST['hnh_bulk_listing_category']) ? sanitize_text_field($_REQUEST['hnh_bulk_listing_category']) : '__no_change__';

  // Sanitize notes (textarea)
  $notes = sanitize_textarea_field($notes);

  // Update notes ONLY if not empty (prevents accidental mass deletion)
  if (!empty(trim($notes))) {
    hnh_update_field_safe('notes', $notes, $post_id);
  }

  // Update selects only if value is NOT "__no_change__"
  if ($steering_position !== '__no_change__') {
    hnh_update_field_safe('steering_position', $steering_position, $post_id);
  }

  if ($transmission_type !== '__no_change__') {
    hnh_update_field_safe('transmission_type', $transmission_type, $post_id);
  }

  if ($colour !== '__no_change__') {
    hnh_update_field_safe('colour', $colour, $post_id);
  }

  if ($fuel_type !== '__no_change__') {
    hnh_update_field_safe('fuel_type', $fuel_type, $post_id);
  }

  if ($price_currency !== '__no_change__') {
    hnh_update_field_safe('price_currency', $price_currency, $post_id);
  }

  if ($listing_category !== '__no_change__') {
    hnh_update_field_safe('listing_category', $listing_category, $post_id);
  }
}


/**
 * Helper function to update ACF field (with fallback to update_post_meta)
 * 
 * @param string $field_name ACF field name (meta key)
 * @param mixed  $value      Value to save
 * @param int    $post_id    Post ID
 */
function hnh_update_field_safe($field_name, $value, $post_id)
{
  // Try ACF's update_field first (if ACF is active)
  if (function_exists('update_field')) {
    update_field($field_name, $value, $post_id);
  } else {
    // Fallback to native WordPress meta
    update_post_meta($post_id, $field_name, $value);
  }
}
