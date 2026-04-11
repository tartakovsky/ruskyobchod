<?php
/**
 * Plugin Name: Rusky Runtime Shim
 * Description: Single runtime owner for plugin overrides and guarded FAR rewrites.
 */

if (!defined('ABSPATH')) {
    exit;
}

function rusky_has_wp_logged_in_cookie(): bool {
    foreach ($_COOKIE as $name => $value) {
        if (strpos((string) $name, 'wordpress_logged_in_') === 0 && $value !== '') {
            return true;
        }
    }

    return false;
}

function rusky_is_admin_uri(): bool {
    $uri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '';

    return strpos($uri, '/wp-admin/') !== false;
}

function rusky_far_is_sensitive_request(): bool {
    if (is_admin() || wp_doing_ajax()) {
        return true;
    }

    if ((defined('REST_REQUEST') && REST_REQUEST) || (function_exists('wp_is_json_request') && wp_is_json_request())) {
        return true;
    }

    if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
        return true;
    }

    if (function_exists('is_preview') && is_preview()) {
        return true;
    }

    if (isset($_GET['elementor-preview'])) {
        return true;
    }

    $action = isset($_GET['action']) ? sanitize_key(wp_unslash($_GET['action'])) : '';

    return $action === 'elementor';
}

function rusky_runtime_blocked_plugins(): array {
    $blocked = [
        'real-time-find-and-replace/real-time-find-and-replace.php',
    ];

    if (rusky_should_trim_language_plugin()) {
        $blocked[] = 'gastronom-lang-switcher/gastronom-lang-switcher.php';
    }

    return $blocked;
}

function rusky_should_trim_language_plugin(): bool {
    return rusky_far_is_sensitive_request();
}

function rusky_should_trim_active_plugins(): bool {
    return true;
}

function rusky_far_should_run(): bool {
    if (rusky_far_is_sensitive_request()) {
        return false;
    }

    return !is_user_logged_in();
}

function rusky_far_apply_rules(string $buffer): string {
    $settings = get_option('far_plugin_settings');
    if (!is_array($settings)) {
        return $buffer;
    }

    $find = $settings['farfind'] ?? null;
    $replace = $settings['farreplace'] ?? null;
    $regex = $settings['farregex'] ?? [];

    if (!is_array($find) || !is_array($replace)) {
        return $buffer;
    }

    foreach ($find as $key => $needle) {
        $replacement = $replace[$key] ?? '';

        if (!is_string($needle) || $needle === '') {
            continue;
        }

        if (isset($regex[$key])) {
            $updated = @preg_replace($needle, $replacement, $buffer);
            if (is_string($updated)) {
                $buffer = $updated;
            }
            continue;
        }

        if (strpos($buffer, $needle) === false) {
            continue;
        }

        $buffer = str_replace($needle, $replacement, $buffer);
    }

    return $buffer;
}

add_filter('option_active_plugins', function($plugins) {
    if (!rusky_should_trim_active_plugins() || !is_array($plugins)) {
        return $plugins;
    }

    $blocked = rusky_runtime_blocked_plugins();

    return array_values(array_filter($plugins, static function($plugin) use ($blocked) {
        return !in_array($plugin, $blocked, true);
    }));
}, 1);

add_action('plugins_loaded', function() {
    if (function_exists('remove_action')) {
        remove_action('template_redirect', 'far_template_redirect');
    }
}, 100);

add_action('template_redirect', function() {
    if (!rusky_far_should_run()) {
        return;
    }

    ob_start(static function($buffer) {
        if (!is_string($buffer) || $buffer === '') {
            return $buffer;
        }

        return rusky_far_apply_rules($buffer);
    });
}, 0);
