<?php
/**
 * Plugin Name: Rusky Production Lockdown
 * Description: Disables direct dashboard updates and editors on live.
 */

if (!defined('ABSPATH')) {
    exit;
}

function rpl_repo_first_message(): string {
    return 'Live code changes from wp-admin are disabled. Use the repo-first workflow and deploy verified files only.';
}

add_filter('automatic_updater_disabled', '__return_true', 100);
add_filter('auto_update_core', '__return_false', 100);
add_filter('auto_update_plugin', '__return_false', 100);
add_filter('auto_update_theme', '__return_false', 100);
add_filter('auto_update_translation', '__return_false', 100);
add_filter('woocommerce_enable_auto_update_db', '__return_false', 100);

function rpl_block_file_mods($allowed, $context) {
    $blocked = [
        'update_core',
        'update_plugin',
        'update_theme',
        'install_plugin',
        'install_theme',
        'upload_plugin',
        'upload_theme',
        'delete_plugin',
        'delete_theme',
        'edit_plugin',
        'edit_theme',
        'translation',
    ];

    if (in_array((string) $context, $blocked, true)) {
        return false;
    }

    return $allowed;
}
add_filter('file_mod_allowed', 'rpl_block_file_mods', 100, 2);

function rpl_remove_editor_and_update_menus(): void {
    remove_submenu_page('themes.php', 'theme-editor.php');
    remove_submenu_page('plugins.php', 'plugin-editor.php');
    remove_submenu_page('index.php', 'update-core.php');
}
add_action('admin_menu', 'rpl_remove_editor_and_update_menus', 999);

function rpl_block_editor_screens(): void {
    wp_die(
        esc_html(rpl_repo_first_message()),
        esc_html__('Action blocked', 'ruskyobchod'),
        ['response' => 403]
    );
}
add_action('load-plugin-editor.php', 'rpl_block_editor_screens');
add_action('load-theme-editor.php', 'rpl_block_editor_screens');

function rpl_render_notice(): void {
    global $pagenow;

    if (!is_admin() || !current_user_can('update_plugins')) {
        return;
    }

    $screens = [
        'plugins.php',
        'plugin-install.php',
        'themes.php',
        'theme-install.php',
        'update-core.php',
        'plugin-editor.php',
        'theme-editor.php',
    ];

    if (!in_array((string) $pagenow, $screens, true)) {
        return;
    }

    printf(
        '<div class="notice notice-warning"><p>%s</p></div>',
        esc_html(rpl_repo_first_message())
    );
}
add_action('admin_notices', 'rpl_render_notice');
