<?php
/**
 * Plugin Name: Rusky Dotypos Stock Reconcile
 * Description: Quiet bulk stock backstop from Dotypos to WooCommerce for missed movement updates.
 */

if (!defined('ABSPATH')) {
    exit;
}

const RDSR_ACTION_HOOK = 'rusky_dotypos_quiet_reconcile_stock';
const RDSR_ACTION_GROUP = 'rusky_maintenance';
const RDSR_INTERVAL = 900;
const RDSR_LOCK_KEY = 'rusky_dotypos_quiet_reconcile_lock';
const RDSR_LOCK_TTL = 600;

function rdsr_can_run(): bool {
    if (!class_exists('Dotypos') || !class_exists('WooCommerce')) {
        return false;
    }

    $settings = get_option(Dotypos::$keys['settings'], []);
    return !empty($settings['product']['movement']['syncFromDotypos']) && !empty($settings['dotypos']['warehouseId']);
}

function rdsr_schedule_backstop(): void {
    if (!function_exists('as_next_scheduled_action') || !function_exists('as_schedule_recurring_action')) {
        return;
    }

    if (!rdsr_can_run()) {
        return;
    }

    if (as_next_scheduled_action(RDSR_ACTION_HOOK, [], RDSR_ACTION_GROUP)) {
        return;
    }

    as_schedule_recurring_action(time() + RDSR_INTERVAL, RDSR_INTERVAL, RDSR_ACTION_HOOK, [], RDSR_ACTION_GROUP);
}
add_action('init', 'rdsr_schedule_backstop', 20);

function rdsr_apply_stock_to_product(WC_Product $product, float $qty): bool {
    if (function_exists('gastronom_apply_dotypos_stock_to_wc_product') && gastronom_apply_dotypos_stock_to_wc_product($product, $qty)) {
        return true;
    }

    update_post_meta($product->get_id(), '_stock', $qty);
    wc_update_product_stock_status($product->get_id(), $qty > 0 ? 'instock' : 'outofstock');
    wc_delete_product_transients($product->get_id());
    clean_post_cache($product->get_id());
    return true;
}

function rdsr_run_quiet_reconcile(): void {
    if (!rdsr_can_run()) {
        return;
    }

    if (get_transient(RDSR_LOCK_KEY)) {
        return;
    }

    set_transient(RDSR_LOCK_KEY, '1', RDSR_LOCK_TTL);

    try {
        $settings = get_option(Dotypos::$keys['settings'], []);
        $warehouse_id = (string) $settings['dotypos']['warehouseId'];
        $dotypos = Dotypos::instance();
        if (!$dotypos || empty($dotypos->dotyposService)) {
            return;
        }

        $products = wc_get_products([
            'limit' => -1,
            'status' => 'publish',
            'return' => 'objects',
        ]);

        $wc_products = [];
        foreach ($products as $product) {
            if (!$product instanceof WC_Product) {
                continue;
            }

            $dotypos_id = (string) get_post_meta($product->get_id(), Dotypos::$keys['product']['field-id'], true);
            if ($dotypos_id === '') {
                continue;
            }

            $wc_products[$dotypos_id] = $product->get_id();
        }

        $updated = 0;
        $seen = 0;
        foreach ($dotypos->dotyposService->getProductsOnWarehouse($warehouse_id) as $remote_product) {
            $dotypos_id = isset($remote_product['id']) ? (string) $remote_product['id'] : '';
            if ($dotypos_id === '' || !isset($wc_products[$dotypos_id])) {
                continue;
            }

            $seen++;
            $wc_id = $wc_products[$dotypos_id];
            $qty = isset($remote_product['stockQuantityStatus']) ? (float) $remote_product['stockQuantityStatus'] : null;
            if ($qty === null) {
                continue;
            }

            $product = wc_get_product($wc_id);
            if (!$product instanceof WC_Product) {
                continue;
            }

            $expected_source = get_post_meta($wc_id, '_gastronom_weight_preorder', true) === 'yes'
                ? '_gastronom_cash_stock_kg'
                : '_stock';
            $current_qty = (float) get_post_meta($wc_id, $expected_source, true);
            if (abs($current_qty - $qty) < 0.0001) {
                continue;
            }

            rdsr_apply_stock_to_product($product, $qty);
            $updated++;
        }

        if (function_exists('wc_get_logger')) {
            wc_get_logger()->info(
                sprintf('[Rusky reconcile] seen=%d updated=%d', $seen, $updated),
                ['source' => 'dotypos_reconcile']
            );
        }
    } finally {
        delete_transient(RDSR_LOCK_KEY);
    }
}
add_action(RDSR_ACTION_HOOK, 'rdsr_run_quiet_reconcile');
