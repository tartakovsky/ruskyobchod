<?php
/**
 * Plugin Name: Rusky Disable FAR Runtime
 * Description: Emergency runtime filter for high-risk plugins without editing active_plugins in the database.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_filter('option_active_plugins', static function($plugins) {
    if (!is_array($plugins)) {
        return $plugins;
    }

    $blocked = [
        'real-time-find-and-replace/real-time-find-and-replace.php',
    ];

    return array_values(array_filter($plugins, static function($plugin) use ($blocked) {
        return !in_array($plugin, $blocked, true);
    }));
}, 1);
