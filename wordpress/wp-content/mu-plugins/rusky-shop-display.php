<?php
/**
 * Plugin Name: Rusky Shop Display
 * Description: Isolates shop layout and cart-count display tweaks from the language layer.
 */

if (!defined('ABSPATH')) {
    exit;
}

function rsd_shop_columns() {
    return 4;
}
add_filter('loop_shop_columns', 'rsd_shop_columns', 999);

function rsd_products_per_page() {
    return 12;
}
add_filter('loop_shop_per_page', 'rsd_products_per_page', 999);

add_filter('woocommerce_cart_contents_count', function($count) {
    if (function_exists('WC') && WC()->cart) {
        return count(WC()->cart->get_cart());
    }

    return $count;
});

function rsd_shop_lang(): string {
    if (function_exists('gls_server_lang')) {
        return gls_server_lang() === 'ru' ? 'ru' : 'sk';
    }

    $query_lang = isset($_GET['lang']) ? sanitize_key(wp_unslash($_GET['lang'])) : '';
    if ($query_lang === 'ru' || $query_lang === 'sk') {
        return $query_lang;
    }

    $cookie_lang = isset($_COOKIE['gastronom_lang']) ? sanitize_key(wp_unslash($_COOKIE['gastronom_lang'])) : '';
    return $cookie_lang === 'ru' ? 'ru' : 'sk';
}

function rsd_shop_archive_label(?string $lang = null): string {
    $lang = $lang ?: rsd_shop_lang();
    return $lang === 'ru' ? 'Магазин' : 'Obchod';
}

add_filter('woocommerce_show_page_title', function($show): bool {
    if (function_exists('is_shop') && is_shop()) {
        return false;
    }

    return (bool) $show;
}, 999);

add_filter('woocommerce_get_breadcrumb', function($crumbs) {
    if (!is_array($crumbs) || !function_exists('is_shop') || !is_shop()) {
        return $crumbs;
    }

    $last_index = array_key_last($crumbs);
    if ($last_index === null || !isset($crumbs[$last_index][0])) {
        return $crumbs;
    }

    $crumbs[$last_index][0] = rsd_shop_archive_label();
    return $crumbs;
}, 999);

add_filter('document_title_parts', function($parts) {
    if (!is_array($parts) || !function_exists('is_shop') || !is_shop()) {
        return $parts;
    }

    $parts['title'] = rsd_shop_archive_label();
    return $parts;
}, 999);
