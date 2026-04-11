<?php
/**
 * Plugin Name: Rusky Dotypos Stock Bridge
 * Description: Extraction-ready Dotypos bridge for delayed preorder stock movement.
 *
 * Scaffold status:
 * - local hardening surface only
 * - no live hook registration yet
 * - uses `rdsb_*` names to avoid collisions with current `gastronom_*` ownership
 */

if (!defined('ABSPATH')) {
    exit;
}

function rdsb_apply_dotypos_stock_to_preorder_product($product, float $raw_qty): bool {
    if (!$product instanceof WC_Product) {
        return false;
    }

    $product_id = (int) $product->get_id();
    if (!function_exists('rwp_enabled') || !rwp_enabled($product_id)) {
        return false;
    }

    if (function_exists('rwp_apply_piece_stock')) {
        rwp_apply_piece_stock($product_id, $raw_qty);
        return true;
    }

    return false;
}

function rdsb_resolve_order_sync_quantity($order, $item, bool $restore = false) {
    if (!$item instanceof WC_Order_Item_Product) {
        return null;
    }

    $product = $item->get_product();
    if (!$product) {
        return null;
    }

    if (!function_exists('rwp_enabled') || !rwp_enabled($product->get_id())) {
        return null;
    }

    // Preorder-weight items must not change Dotypos at normal order placement time.
    return false;
}

function rdsb_sync_confirmed_preorder_items($order): void {
    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }
    if (!$order instanceof WC_Order) {
        return;
    }

    $settings = get_option(Dotypos::$keys['settings']);
    if (empty($settings['product']['movement']['syncToDotypos'])) {
        return;
    }

    $dotypos = Dotypos::instance();
    if (!$dotypos || empty($dotypos->dotyposService)) {
        return;
    }

    foreach ($order->get_items('line_item') as $item) {
        if ($item->get_meta('_gastronom_weight_preorder', true) !== 'yes') {
            continue;
        }
        if (!function_exists('rwp_item_is_confirmed') || !rwp_item_is_confirmed($item)) {
            continue;
        }
        if ($item->get_meta('_gastronom_weight_cash_synced', true) === 'yes') {
            continue;
        }

        $product = $item->get_product();
        if (!$product) {
            continue;
        }

        $dotypos_id = $product->get_meta(Dotypos::$keys['product']['field-id']);
        $actual_weight = (float) $item->get_meta('_gastronom_actual_weight_kg', true);
        if (empty($dotypos_id) || $actual_weight <= 0) {
            continue;
        }

        $invoice_number = 'WC-PREORDER-' . $order->get_id() . '-CONFIRM';
        $dotypos->dotyposService->updateProductStock($settings['dotypos']['warehouseId'], $dotypos_id, -$actual_weight, $invoice_number);
        $latest_raw = (float) $dotypos->dotyposService->getProductOnWarehouse($settings['dotypos']['warehouseId'], $dotypos_id)['stockQuantityStatus'];

        if (function_exists('rwp_apply_piece_stock')) {
            rwp_apply_piece_stock($product->get_id(), $latest_raw);
        }

        $item->update_meta_data('_gastronom_weight_cash_synced', 'yes');
        $item->save();
    }
}

function rdsb_restore_confirmed_preorder_items($order): void {
    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }
    if (!$order instanceof WC_Order) {
        return;
    }

    $settings = get_option(Dotypos::$keys['settings']);
    if (empty($settings['product']['movement']['syncToDotypos'])) {
        return;
    }

    $dotypos = Dotypos::instance();
    if (!$dotypos || empty($dotypos->dotyposService)) {
        return;
    }

    foreach ($order->get_items('line_item') as $item) {
        if ($item->get_meta('_gastronom_weight_preorder', true) !== 'yes') {
            continue;
        }
        if ($item->get_meta('_gastronom_weight_cash_synced', true) !== 'yes') {
            continue;
        }
        if ($item->get_meta('_gastronom_weight_cash_restored', true) === 'yes') {
            continue;
        }

        $product = $item->get_product();
        if (!$product) {
            continue;
        }

        $dotypos_id = $product->get_meta(Dotypos::$keys['product']['field-id']);
        $actual_weight = (float) $item->get_meta('_gastronom_actual_weight_kg', true);
        if (empty($dotypos_id) || $actual_weight <= 0) {
            continue;
        }

        $invoice_number = 'WC-PREORDER-' . $order->get_id() . '-RESTORE';
        $dotypos->dotyposService->updateProductStock($settings['dotypos']['warehouseId'], $dotypos_id, $actual_weight, $invoice_number);
        $latest_raw = (float) $dotypos->dotyposService->getProductOnWarehouse($settings['dotypos']['warehouseId'], $dotypos_id)['stockQuantityStatus'];

        if (function_exists('rwp_apply_piece_stock')) {
            rwp_apply_piece_stock($product->get_id(), $latest_raw);
        }

        $item->update_meta_data('_gastronom_weight_cash_restored', 'yes');
        $item->save();
    }
}

if (!function_exists('gastronom_apply_dotypos_stock_to_wc_product')) {
    function gastronom_apply_dotypos_stock_to_wc_product($product, float $raw_qty): bool {
        return rdsb_apply_dotypos_stock_to_preorder_product($product, $raw_qty);
    }
}

if (!function_exists('gastronom_resolve_dotypos_order_sync_quantity')) {
    function gastronom_resolve_dotypos_order_sync_quantity($order, $item, bool $restore = false) {
        return rdsb_resolve_order_sync_quantity($order, $item, $restore);
    }
}

if (!function_exists('gastronom_sync_confirmed_preorder_items_to_dotypos')) {
    function gastronom_sync_confirmed_preorder_items_to_dotypos($order): void {
        rdsb_sync_confirmed_preorder_items($order);
    }
}

if (!function_exists('gastronom_restore_confirmed_preorder_items_to_dotypos')) {
    function gastronom_restore_confirmed_preorder_items_to_dotypos($order): void {
        rdsb_restore_confirmed_preorder_items($order);
    }
}
