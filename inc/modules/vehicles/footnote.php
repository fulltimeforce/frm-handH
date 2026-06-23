<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registers the global vehicle footnote editor in ACF.
 */
function hnh_register_vehicle_footnote_options(): void
{
    if (!function_exists('acf_add_options_page') || !function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_options_page([
        'page_title' => 'Vehicle Footnote Settings',
        'menu_title' => 'Vehicle Footnote',
        'menu_slug' => 'vehicle-footnote-settings',
        'capability' => 'manage_options',
        'redirect' => false,
        'parent_slug' => 'edit.php?post_type=vehicles',
    ]);

    acf_add_local_field_group([
        'key' => 'group_hnh_vehicle_footnote_options',
        'title' => 'Vehicle Footnote',
        'fields' => [
            [
                'key' => 'field_hnh_default_vehicle_footnote',
                'label' => 'Default Footnote',
                'name' => 'default_vehicle_footnote',
                'type' => 'wysiwyg',
                'instructions' => 'This content is displayed on every vehicle page.',
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'vehicle-footnote-settings',
                ],
            ],
        ],
    ]);
}
add_action('acf/init', 'hnh_register_vehicle_footnote_options');

/**
 * Returns the shared footnote used by every vehicle page.
 *
 * Until the global option is saved, vehicle 274879 supplies the approved HTML.
 * The current vehicle footnote is only a final fallback.
 */
function hnh_get_global_vehicle_footnote(int $vehicle_id = 0): string
{
    if (!function_exists('get_field')) {
        return '';
    }

    $footnote = get_field('default_vehicle_footnote', 'option');

    if (!is_string($footnote) || trim($footnote) === '') {
        $footnote = get_field('footnote', 274879);
    }

    if ((!is_string($footnote) || trim($footnote) === '') && $vehicle_id > 0) {
        $footnote = get_field('footnote', $vehicle_id);
    }

    return is_string($footnote) ? $footnote : '';
}
