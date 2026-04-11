<?php
/**
 * Plugin Name: Rusky Stock Normalization
 * Description: Extraction-ready generic Woo stock normalization helpers.
 *
 * Scaffold status:
 * - local hardening surface only
 * - no live hook registration yet
 * - uses `rsn_*` names to avoid collisions with current `gastronom_*` ownership
 */

if (!defined('ABSPATH')) {
    exit;
}

function rsn_normalize_product_name($name): string {
    $name = html_entity_decode(wp_strip_all_tags((string) $name), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (function_exists('mb_strtolower')) {
        $name = mb_strtolower($name, 'UTF-8');
    } else {
        $name = strtolower($name);
    }

    return trim((string) preg_replace('/\s+/', ' ', $name));
}

function rsn_catalog_policy($name): string {
    $normalized = rsn_normalize_product_name($name);

    foreach ([
        'káva espresso',
        'кофе эспрессо',
        'taška veľká',
        'пакет большой',
        'taška malá',
        'пакет малый',
    ] as $needle) {
        if (strpos($normalized, $needle) !== false) {
            return 'service';
        }
    }

    foreach ([
        'пакет подарочный',
        'пакет нг',
        'пакет крафт нг',
        'novoroč',
        'vianoč',
        'darček',
    ] as $needle) {
        if (strpos($normalized, $needle) !== false) {
            return 'seasonal';
        }
    }

    return 'normal';
}

function rsn_reconcile_decimal_stock($product_id): void {
    $product_id = (int) $product_id;
    if ($product_id <= 0 || get_post_type($product_id) !== 'product') {
        return;
    }

    $raw_stock = get_post_meta($product_id, '_stock', true);
    $qty = ($raw_stock === '' || $raw_stock === false) ? 0.0 : (float) $raw_stock;
    $current_status = get_post_meta($product_id, '_stock_status', true);

    if ($qty <= 0 && $current_status !== 'outofstock') {
        wc_update_product_stock_status($product_id, 'outofstock');
        update_post_meta($product_id, '_backorders', 'no');
    } elseif ($qty > 0 && $current_status !== 'instock') {
        wc_update_product_stock_status($product_id, 'instock');
    }
}

function rsn_apply_catalog_policy($product_id): void {
    $product_id = (int) $product_id;
    if ($product_id <= 0 || get_post_type($product_id) !== 'product') {
        return;
    }

    $name = get_the_title($product_id);
    $policy = rsn_catalog_policy($name);

    if ($policy === 'service') {
        remove_action('save_post_product', 'gastronom_enforce_product_rules', 20);
        wp_update_post([
            'ID' => $product_id,
            'post_status' => 'draft',
        ]);
        add_action('save_post_product', 'gastronom_enforce_product_rules', 20, 3);
        update_post_meta($product_id, '_catalog_visibility', 'hidden');
        return;
    }

    if ($policy === 'seasonal') {
        $seasonal_category = get_term_by('slug', 'tovary-po-sluchayu-sezonny-tovar', 'product_cat');
        if ($seasonal_category && !is_wp_error($seasonal_category)) {
            wp_set_post_terms($product_id, [(int) $seasonal_category->term_id], 'product_cat', false);
        }
        update_post_meta($product_id, '_catalog_visibility', 'visible');
    }
}

function rsn_enforce_product_rules($post_id, $post = null, $update = true): void {
    if (wp_is_post_revision($post_id) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
        return;
    }

    rsn_apply_catalog_policy($post_id);
    rsn_reconcile_decimal_stock($post_id);
}

if (!function_exists('gastronom_normalize_product_name')) {
    function gastronom_normalize_product_name($name): string {
        return rsn_normalize_product_name($name);
    }
}

if (!function_exists('gastronom_catalog_policy')) {
    function gastronom_catalog_policy($name): string {
        return rsn_catalog_policy($name);
    }
}

if (!function_exists('gastronom_reconcile_decimal_stock')) {
    function gastronom_reconcile_decimal_stock($product_id): void {
        rsn_reconcile_decimal_stock($product_id);
    }
}

if (!function_exists('gastronom_apply_catalog_policy')) {
    function gastronom_apply_catalog_policy($product_id): void {
        rsn_apply_catalog_policy($product_id);
    }
}

if (!function_exists('gastronom_enforce_product_rules')) {
    function gastronom_enforce_product_rules($post_id, $post = null, $update = true): void {
        rsn_enforce_product_rules($post_id, $post, $update);
    }
}
