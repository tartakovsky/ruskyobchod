<?php
/**
 * Plugin Name: Rusky Dotypos Maintenance
 * Description: Quarantines temporary Dotypos diagnostic and repair REST tools away from the language layer.
 */

if (!defined('ABSPATH')) {
    exit;
}

function rdm_can_manage_dotypos_tools(): bool {
    return current_user_can('manage_options');
}

function rdm_read_dotypos_diag() {
    global $wpdb;

    $opts = $wpdb->get_results(
        "SELECT option_name, LEFT(option_value, 1000) as option_value FROM {$wpdb->options} WHERE option_name LIKE '%dotypos%' ORDER BY option_name"
    );
    $transients = $wpdb->get_results(
        "SELECT option_name, LEFT(option_value, 500) as option_value FROM {$wpdb->options} WHERE option_name LIKE '%_transient_%dotypos%' ORDER BY option_name"
    );

    $as_table = $wpdb->prefix . 'actionscheduler_actions';
    $as_exists = $wpdb->get_var("SHOW TABLES LIKE '{$as_table}'");
    $as_actions = [];
    if ($as_exists) {
        $as_actions = $wpdb->get_results(
            "SELECT action_id, hook, status, args, scheduled_date_gmt, last_attempt_gmt FROM {$as_table} WHERE hook LIKE '%dotypos%' ORDER BY action_id DESC LIMIT 20"
        );
    }

    return [
        'options' => $opts,
        'transients' => $transients,
        'action_scheduler' => $as_actions,
    ];
}

function rdm_register_rest_routes(): void {
    register_rest_route('gls/v1', '/dotypos-diag', [
        'methods' => 'GET',
        'callback' => 'rdm_read_dotypos_diag',
        'permission_callback' => 'rdm_can_manage_dotypos_tools',
    ]);

}

add_action('rest_api_init', 'rdm_register_rest_routes');

if (!function_exists('gastronom_register_dotypos_maintenance_routes')) {
    function gastronom_register_dotypos_maintenance_routes(): void {
        rdm_register_rest_routes();
    }
}

function rdm_has_logged_in_cookie(): bool {
    foreach (array_keys($_COOKIE) as $name) {
        if (strpos($name, 'wordpress_logged_in_') === 0) {
            return true;
        }
    }

    return false;
}

function rdm_is_dotypos_frontend_boundary_request(): bool {
    if (is_admin()) {
        return false;
    }

    if (function_exists('wp_doing_ajax') && wp_doing_ajax()) {
        return false;
    }

    if (defined('REST_REQUEST') && REST_REQUEST) {
        return false;
    }

    if (defined('DOING_CRON') && DOING_CRON) {
        return false;
    }

    $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if (!in_array($method, ['GET', 'HEAD'], true)) {
        return false;
    }

    $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
    if ($uri === '') {
        return false;
    }

    // REST requests are not reliably identified by REST_REQUEST at this phase.
    // Keep Dotypos loaded for all wp-json / rest_route requests so its routes exist.
    if (
        strpos($uri, '/wp-json') === 0 ||
        strpos($uri, '/wp-json/') === 0 ||
        strpos($uri, 'rest_route=') !== false
    ) {
        return false;
    }

    if (strpos($uri, '/dotypos-webhook-') !== false) {
        return false;
    }

    return true;
}

add_filter('option_active_plugins', function($plugins) {
    if (!is_array($plugins)) {
        return $plugins;
    }

    if (!rdm_is_dotypos_frontend_boundary_request() || !rdm_has_logged_in_cookie()) {
        return $plugins;
    }

    return array_values(array_filter($plugins, static function($plugin) {
        return $plugin !== 'woocommerce-extension-master/dotypos.php';
    }));
}, 1);
