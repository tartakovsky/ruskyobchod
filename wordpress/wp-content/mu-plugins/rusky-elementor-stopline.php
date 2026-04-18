<?php
/**
 * Plugin Name: Rusky Elementor Stop-Line
 * Description: Prevents known-incompatible Elementor Pro activation on live.
 */

if (!defined('ABSPATH')) {
    exit;
}

const RES_STOPLINE_NOTICE_OPTION = 'rusky_elementor_stopline_notice';

function res_stopline_plugin_path(string $plugin): string {
    return trailingslashit(WP_CONTENT_DIR) . 'plugins/' . $plugin;
}

function res_stopline_plugin_version(string $plugin): string {
    $path = res_stopline_plugin_path($plugin);
    if (!file_exists($path) || !function_exists('get_file_data')) {
        return '';
    }

    $data = get_file_data($path, ['Version' => 'Version']);
    return trim((string) ($data['Version'] ?? ''));
}

function res_stopline_is_incompatible_pair(?string $free_version = null, ?string $pro_version = null): bool {
    $free_version = $free_version ?? res_stopline_plugin_version('elementor/elementor.php');
    $pro_version = $pro_version ?? res_stopline_plugin_version('elementor-pro/elementor-pro.php');

    if ($free_version === '' || $pro_version === '') {
        return false;
    }

    $free_major = strtok($free_version, '.');
    $pro_major = strtok($pro_version, '.');

    return $free_major === '4' && $pro_major !== '4';
}

function res_stopline_filtered_active_plugins($plugins): array {
    $plugins = is_array($plugins) ? $plugins : [];

    if (!in_array('elementor-pro/elementor-pro.php', $plugins, true)) {
        return $plugins;
    }

    if (!res_stopline_is_incompatible_pair()) {
        return $plugins;
    }

    return array_values(array_filter(
        $plugins,
        static fn(string $plugin): bool => $plugin !== 'elementor-pro/elementor-pro.php'
    ));
}

function res_stopline_enforce_incompatible_elementor_pair(): void {
    $active_plugins = get_option('active_plugins', []);
    $filtered = res_stopline_filtered_active_plugins($active_plugins);

    if ($filtered === $active_plugins) {
        return;
    }

    update_option('active_plugins', $filtered, false);

    update_option(RES_STOPLINE_NOTICE_OPTION, [
        'free_version' => res_stopline_plugin_version('elementor/elementor.php'),
        'pro_version' => res_stopline_plugin_version('elementor-pro/elementor-pro.php'),
        'recorded_at' => current_time('mysql'),
    ], false);
}

function res_stopline_render_admin_notice(): void {
    $notice = get_option(RES_STOPLINE_NOTICE_OPTION);
    if (!is_array($notice) || empty($notice['free_version']) || empty($notice['pro_version'])) {
        return;
    }

    delete_option(RES_STOPLINE_NOTICE_OPTION);

    $message = sprintf(
        'Elementor Pro %s was deactivated because it is incompatible with Elementor %s. Keep Elementor Pro disabled until a compatible Pro 4.x package is available.',
        $notice['pro_version'],
        $notice['free_version']
    );

    printf(
        '<div class="notice notice-error"><p>%s</p></div>',
        esc_html($message)
    );
}

add_filter('option_active_plugins', 'res_stopline_filtered_active_plugins', 1);
add_action('muplugins_loaded', 'res_stopline_enforce_incompatible_elementor_pair', 1);
add_action('admin_notices', 'res_stopline_render_admin_notice');
