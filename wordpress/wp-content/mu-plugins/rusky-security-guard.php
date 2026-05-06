<?php
/**
 * Plugin Name: Rusky Security Guard
 * Description: Lightweight production guardrails for the storefront.
 */

if (!defined('ABSPATH')) {
    exit;
}

const RSG_FORBIDDEN_ACTIVE_PLUGINS = [
    'file-manager-advanced/file_manager_advanced.php',
    'wp-file-manager/file_folder_manager.php',
    'view-source/view-source.php',
];

function rsg_remove_forbidden_active_plugins(): void {
    $active = get_option('active_plugins', []);
    if (!is_array($active)) {
        return;
    }

    $filtered = array_values(array_diff($active, RSG_FORBIDDEN_ACTIVE_PLUGINS));
    if ($filtered === $active) {
        return;
    }

    update_option('active_plugins', $filtered, false);

    if (function_exists('error_log')) {
        error_log('Rusky Security Guard deactivated forbidden file-manager plugin(s).');
    }
}
add_action('muplugins_loaded', 'rsg_remove_forbidden_active_plugins', 1);

function rsg_mark_file_editor_disabled(): void {
    if (!defined('DISALLOW_FILE_EDIT') || DISALLOW_FILE_EDIT !== true) {
        if (function_exists('error_log')) {
            error_log('Rusky Security Guard warning: DISALLOW_FILE_EDIT is not enabled.');
        }
    }
}
add_action('init', 'rsg_mark_file_editor_disabled', 1);
