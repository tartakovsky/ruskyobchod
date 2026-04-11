<?php
/**
 * Plugin Name: Rusky Shop Alias
 * Description: Redirects the legacy /obchod/ route to the actual WooCommerce shop page.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('template_redirect', function() {
    if (is_admin() || wp_doing_ajax()) {
        return;
    }

    $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
    if ($request_uri === '') {
        return;
    }

    $path = wp_parse_url($request_uri, PHP_URL_PATH);
    if ($path !== '/obchod/' && $path !== '/obchod') {
        return;
    }

    $query = wp_parse_url($request_uri, PHP_URL_QUERY);
    $target = home_url('/shop/');
    if (!empty($query)) {
        $target .= '?' . $query;
    }

    wp_safe_redirect($target, 301, 'RuskyShopAlias');
    exit;
}, 0);
