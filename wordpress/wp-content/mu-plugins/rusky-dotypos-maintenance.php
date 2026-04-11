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

function rdm_apply_dotypos_fix(WP_REST_Request $request) {
    global $wpdb;

    $results = [];
    $action = $request->get_param('action');

    if ($action === 'clear_lock') {
        $deleted = $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%dotypos%lock%' OR option_name LIKE '%_transient_%dotypos%lock%'"
        );
        $results['deleted_lock_options'] = $deleted;

        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%dotypos%' AND option_value = 'LOCKED'"
        );
    }

    if ($action === 'clear_as_actions') {
        $as_table = $wpdb->prefix . 'actionscheduler_actions';
        $deleted = $wpdb->query(
            "DELETE FROM {$as_table} WHERE hook LIKE '%dotypos%' AND status IN ('pending', 'in-progress', 'failed')"
        );
        $results['deleted_as_actions'] = $deleted;
    }

    if ($action === 'read_dotypos_php') {
        $file = WP_PLUGIN_DIR . '/woocommerce-extension-master/dotypos.php';
        if (file_exists($file)) {
            $lines = file($file);
            $results['total_lines'] = count($lines);
            $from = intval($request->get_param('from')) ?: 1559;
            $to = intval($request->get_param('to')) ?: ($from + 20);
            $around = [];
            for ($i = max(0, $from - 1); $i < min(count($lines), $to); $i++) {
                $around[$i + 1] = $lines[$i];
            }
            $results['lines'] = $around;
        } else {
            $results['error'] = 'File not found: ' . $file;
        }
    }

    if ($action === 'grep_dotypos') {
        $file = WP_PLUGIN_DIR . '/woocommerce-extension-master/dotypos.php';
        $pattern = $request->get_param('pattern');
        if (file_exists($file) && $pattern) {
            $lines = file($file);
            $matches = [];
            foreach ($lines as $i => $line) {
                if (stripos($line, $pattern) !== false) {
                    $matches[$i + 1] = $line;
                }
            }
            $results['matches'] = $matches;
            $results['total_lines'] = count($lines);
        }
    }

    if ($action === 'read_plugin') {
        $plugin = $request->get_param('plugin');
        if ($plugin) {
            $file = WP_PLUGIN_DIR . '/' . $plugin;
            if (file_exists($file)) {
                $results['content'] = file_get_contents($file);
            } else {
                $results['error'] = 'Not found: ' . $file;
            }
        }
    }

    if ($action === 'write_plugin') {
        $plugin = $request->get_param('plugin');
        $content = $request->get_param('content');
        if ($plugin && $content) {
            $file = WP_PLUGIN_DIR . '/' . $plugin;
            if (file_exists($file)) {
                file_put_contents($file, $content);
                $results['written'] = true;
            } else {
                $results['error'] = 'Not found: ' . $file;
            }
        }
    }

    if ($action === 'update_option') {
        $name = $request->get_param('name');
        $value = $request->get_param('value');
        if ($name && $value !== null) {
            update_option($name, $value);
            $results['updated'] = $name;
            $results['value'] = get_option($name);
        }
    }

    if ($action === 'get_option') {
        $name = $request->get_param('name');
        if ($name) {
            $results['value'] = get_option($name);
        }
    }

    if ($action === 'patch_dotypos') {
        $file = WP_PLUGIN_DIR . '/woocommerce-extension-master/dotypos.php';
        if (file_exists($file)) {
            $content = file_get_contents($file);
            $patched = preg_replace(
                '/as_enqueue_async_action\(\s*([^,]+),\s*null\s*\)/',
                'as_enqueue_async_action($1, [])',
                $content
            );
            $patched = preg_replace(
                '/as_enqueue_async_action\(\s*([^,\)]+)\s*\)/',
                'as_enqueue_async_action($1, [])',
                $patched
            );
            if ($patched !== $content) {
                file_put_contents($file, $patched);
                $results['patched'] = true;
            } else {
                $results['patched'] = false;
                $results['note'] = 'No changes needed or pattern not found';
            }
        }
    }

    return $results;
}

function rdm_register_rest_routes(): void {
    register_rest_route('gls/v1', '/dotypos-diag', [
        'methods' => 'GET',
        'callback' => 'rdm_read_dotypos_diag',
        'permission_callback' => 'rdm_can_manage_dotypos_tools',
    ]);

    register_rest_route('gls/v1', '/dotypos-fix', [
        'methods' => 'POST',
        'callback' => 'rdm_apply_dotypos_fix',
        'permission_callback' => 'rdm_can_manage_dotypos_tools',
    ]);
}

add_action('rest_api_init', 'rdm_register_rest_routes');

if (!function_exists('gastronom_register_dotypos_maintenance_routes')) {
    function gastronom_register_dotypos_maintenance_routes(): void {
        rdm_register_rest_routes();
    }
}
