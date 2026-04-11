<?php
/**
 * Plugin Name: Rusky Stock Policy
 * Description: Extraction-ready stock policy and bootstrap helpers.
 *
 * Scaffold status:
 * - local hardening surface only
 * - no live hook registration yet
 */

if (!defined('ABSPATH')) {
    exit;
}

function rsp_setup_decimal_stock_amount(): void {
    remove_filter('woocommerce_stock_amount', 'intval');
    add_filter('woocommerce_stock_amount', 'floatval');
}

function rsp_disable_stock_notifications(): void {
    if (get_option('woocommerce_notify_low_stock', 'yes') !== 'no') {
        update_option('woocommerce_notify_low_stock', 'no');
    }
    if (get_option('woocommerce_notify_no_stock', 'yes') !== 'no') {
        update_option('woocommerce_notify_no_stock', 'no');
    }
}

function rsp_disable_stock_email_actions($mailer): void {
    if (!$mailer || !is_object($mailer)) {
        return;
    }

    remove_action('woocommerce_low_stock_notification', [$mailer, 'low_stock']);
    remove_action('woocommerce_no_stock_notification', [$mailer, 'no_stock']);
    remove_action('woocommerce_product_on_backorder_notification', [$mailer, 'backorder']);
}

function rsp_detach_dotypos_product_updated_hook(): void {
    global $dotypos;

    if ($dotypos && is_object($dotypos) && method_exists($dotypos, 'handle_product_updated')) {
        remove_action('woocommerce_update_product', [$dotypos, 'handle_product_updated'], 10);
    }
}

function rsp_adjust_rest_endpoints_for_decimal_stock($endpoints) {
    foreach ($endpoints as $route => $handlers) {
        if (strpos($route, 'wc/v3/products') === false) {
            continue;
        }
        foreach ($handlers as $key => $handler) {
            if (!is_array($handler) || !isset($handler['args']['stock_quantity'])) {
                continue;
            }
            $endpoints[$route][$key]['args']['stock_quantity']['type'] = 'number';
        }
    }

    return $endpoints;
}

function rsp_sync_stock_status_after_set_stock($product): void {
    gastronom_reconcile_decimal_stock($product->get_id());
}

function rsp_sync_stock_status_after_rest_insert($product): void {
    if (!$product->managing_stock()) {
        return;
    }

    gastronom_reconcile_decimal_stock($product->get_id());
}

function rsp_reconcile_on_updated_post_meta($meta_id, $object_id, $meta_key): void {
    if ($meta_key === '_stock') {
        gastronom_reconcile_decimal_stock($object_id);
    }
}

function rsp_reconcile_on_added_post_meta($meta_id, $object_id, $meta_key): void {
    if ($meta_key === '_stock') {
        gastronom_reconcile_decimal_stock($object_id);
    }
}

function rsp_run_stock_fix_repair_once(): void {
    $fix_version = '3.3';
    if (get_option('gastronom_stock_fix_version') === $fix_version) {
        return;
    }

    global $wpdb;

    $broken = $wpdb->get_results("
        SELECT p.ID, sm.meta_value as stock_qty
        FROM {$wpdb->posts} p
        JOIN {$wpdb->postmeta} sm ON p.ID = sm.post_id AND sm.meta_key = '_stock'
        JOIN {$wpdb->postmeta} ss ON p.ID = ss.post_id AND ss.meta_key = '_stock_status'
        WHERE p.post_type = 'product'
        AND p.post_status = 'publish'
        AND CAST(sm.meta_value AS DECIMAL(10,3)) > 0
        AND ss.meta_value = 'outofstock'
    ");

    $fixed = 0;
    foreach ($broken as $row) {
        wc_update_product_stock_status($row->ID, 'instock');
        $fixed++;
    }

    $broken_reverse = $wpdb->get_results("
        SELECT p.ID
        FROM {$wpdb->posts} p
        JOIN {$wpdb->postmeta} sm ON p.ID = sm.post_id AND sm.meta_key = '_stock'
        JOIN {$wpdb->postmeta} ss ON p.ID = ss.post_id AND ss.meta_key = '_stock_status'
        WHERE p.post_type = 'product'
        AND p.post_status = 'publish'
        AND p.ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_manage_stock' AND meta_value = 'yes')
        AND (sm.meta_value IS NULL OR CAST(sm.meta_value AS DECIMAL(10,3)) <= 0)
        AND ss.meta_value = 'instock'
    ");

    foreach ($broken_reverse as $row) {
        wc_update_product_stock_status($row->ID, 'outofstock');
        update_post_meta($row->ID, '_backorders', 'no');
        $fixed++;
    }

    $service_and_seasonal = get_posts([
        'post_type' => 'product',
        'post_status' => ['publish', 'draft', 'pending', 'private'],
        'numberposts' => -1,
        'fields' => 'ids',
    ]);

    foreach ($service_and_seasonal as $product_id) {
        gastronom_apply_catalog_policy($product_id);
        gastronom_reconcile_decimal_stock($product_id);
    }

    update_option('gastronom_stock_fix_version', $fix_version);

    if ($fixed > 0) {
        wc_delete_product_transients();
        error_log("Gastronom Stock Fix v{$fix_version}: fixed {$fixed} products stock status + visibility");
    }
}

if (!function_exists('gastronom_setup_decimal_stock_amount')) {
    function gastronom_setup_decimal_stock_amount(): void {
        rsp_setup_decimal_stock_amount();
    }
}

if (!function_exists('gastronom_disable_stock_notifications')) {
    function gastronom_disable_stock_notifications(): void {
        rsp_disable_stock_notifications();
    }
}

if (!function_exists('gastronom_disable_stock_email_actions')) {
    function gastronom_disable_stock_email_actions($mailer): void {
        rsp_disable_stock_email_actions($mailer);
    }
}

if (!function_exists('gastronom_detach_dotypos_product_updated_hook')) {
    function gastronom_detach_dotypos_product_updated_hook(): void {
        rsp_detach_dotypos_product_updated_hook();
    }
}

if (!function_exists('gastronom_adjust_rest_endpoints_for_decimal_stock')) {
    function gastronom_adjust_rest_endpoints_for_decimal_stock($endpoints) {
        return rsp_adjust_rest_endpoints_for_decimal_stock($endpoints);
    }
}

if (!function_exists('gastronom_sync_stock_status_after_set_stock')) {
    function gastronom_sync_stock_status_after_set_stock($product): void {
        rsp_sync_stock_status_after_set_stock($product);
    }
}

if (!function_exists('gastronom_sync_stock_status_after_rest_insert')) {
    function gastronom_sync_stock_status_after_rest_insert($product): void {
        rsp_sync_stock_status_after_rest_insert($product);
    }
}

if (!function_exists('gastronom_reconcile_on_updated_post_meta')) {
    function gastronom_reconcile_on_updated_post_meta($meta_id, $object_id, $meta_key): void {
        rsp_reconcile_on_updated_post_meta($meta_id, $object_id, $meta_key);
    }
}

if (!function_exists('gastronom_reconcile_on_added_post_meta')) {
    function gastronom_reconcile_on_added_post_meta($meta_id, $object_id, $meta_key): void {
        rsp_reconcile_on_added_post_meta($meta_id, $object_id, $meta_key);
    }
}

if (!function_exists('gastronom_run_stock_fix_repair_once')) {
    function gastronom_run_stock_fix_repair_once(): void {
        rsp_run_stock_fix_repair_once();
    }
}
