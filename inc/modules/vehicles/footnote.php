<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Returns the vehicle-specific footnote when explicitly enabled.
 * Otherwise, returns the shared footnote from the General ACF options page.
 */
function hnh_get_vehicle_footnote(int $vehicle_id): string
{
    if (!function_exists('get_field')) {
        return '';
    }

    if (get_field('use_custom_vehicle_footnote', $vehicle_id)) {
        $footnote = get_field('footnote', $vehicle_id);

        return is_string($footnote) ? $footnote : '';
    }

    $footnote = get_field('notes_for_intending_purchases_general', 'option');

    return is_string($footnote) ? $footnote : '';
}
