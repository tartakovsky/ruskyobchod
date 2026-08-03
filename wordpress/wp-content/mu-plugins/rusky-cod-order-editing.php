<?php
/**
 * Plugin Name: Rusky COD Order Editing
 * Description: Allows safe pre-fiscalization editing of processing COD orders.
 */

if (!defined('ABSPATH')) {
    exit;
}

function rcoe_is_deferred_cod_order($order): bool {
    return $order instanceof WC_Order
        && $order->get_payment_method() === 'cod'
        && $order->has_status('processing')
        && defined('RDF_COD_DEFERRED_STATE')
        && $order->get_meta(RDF_STATE_META, true) === RDF_COD_DEFERRED_STATE;
}

function rcoe_allow_deferred_cod_editing(bool $editable, $order): bool {
    if ($editable || !current_user_can('edit_shop_orders')) {
        return $editable;
    }
    return rcoe_is_deferred_cod_order($order);
}
add_filter('wc_order_is_editable', 'rcoe_allow_deferred_cod_editing', 10, 2);

function rcoe_dotypos_stock_change(WC_Order $order, WC_Order_Item_Product $item, float $delta): bool {
    if (abs($delta) < 0.000001 || !function_exists('rdsb_dotypos_post') || !class_exists('Dotypos')) {
        return true;
    }
    $product = $item->get_product();
    $settings = get_option(Dotypos::$keys['settings'], []);
    $warehouse_id = (string) ($settings['dotypos']['warehouseId'] ?? '');
    $dotypos_id = $product ? (string) $product->get_meta(Dotypos::$keys['product']['field-id']) : '';
    if ($warehouse_id === '' || $dotypos_id === '') {
        return false;
    }
    $payload_item = ['_productId' => (int) $dotypos_id, 'quantity' => abs($delta)];
    if ($delta > 0) {
        $path = '/warehouses/' . rawurlencode($warehouse_id) . '/sales';
        $payload = [
            'currency' => $order->get_currency() ?: 'EUR',
            'items' => [$payload_item + ['note' => 'Uprava WooCommerce objednavky #' . $order->get_id()]],
        ];
    } else {
        $path = '/warehouses/' . rawurlencode($warehouse_id) . '/stockups';
        $payload = [
            'updatePurchasePrice' => false,
            'invoiceNumber' => 'WC-ORDER-' . $order->get_id() . '-EDIT-' . $item->get_id() . '-' . time(),
            'note' => 'Uprava WooCommerce objednavky #' . $order->get_id(),
            'items' => [$payload_item],
        ];
    }
    return rdsb_dotypos_post($path, $payload);
}

$GLOBALS['rcoe_pending_stock_changes'] = [];

function rcoe_item_uses_standard_stock(WC_Order $order, WC_Order_Item_Product $item): bool {
    if (!function_exists('gastronom_resolve_dotypos_order_sync_quantity')) {
        return true;
    }
    return gastronom_resolve_dotypos_order_sync_quantity($order, $item, false) !== false;
}

function rcoe_capture_quantity_change($item): void {
    if (!$item instanceof WC_Order_Item_Product) {
        return;
    }
    $order = $item->get_order();
    if (!rcoe_is_deferred_cod_order($order) || !rcoe_item_uses_standard_stock($order, $item)) {
        return;
    }
    $old_reduced = (float) $item->get_meta('_reduced_stock', true);
    $delta = (float) $item->get_quantity() - $old_reduced;
    if (abs($delta) >= 0.000001) {
        $GLOBALS['rcoe_pending_stock_changes'][$item->get_id()] = $delta;
    }
}
add_action('woocommerce_before_save_order_item', 'rcoe_capture_quantity_change', 20, 1);

function rcoe_sync_saved_quantity_changes($order_id): void {
    $order = wc_get_order($order_id);
    if (!rcoe_is_deferred_cod_order($order)) {
        $GLOBALS['rcoe_pending_stock_changes'] = [];
        return;
    }
    foreach ($GLOBALS['rcoe_pending_stock_changes'] as $item_id => $delta) {
        $item = $order->get_item($item_id);
        if ($item instanceof WC_Order_Item_Product && !rcoe_dotypos_stock_change($order, $item, (float) $delta)) {
            $order->add_order_note('Dotypos: POZOR — zmena skladu po úprave položky zlyhala.');
        }
    }
    $GLOBALS['rcoe_pending_stock_changes'] = [];
}
add_action('woocommerce_saved_order_items', 'rcoe_sync_saved_quantity_changes', 20, 1);

function rcoe_sync_added_items(array $added_items, $order): void {
    if (!rcoe_is_deferred_cod_order($order)) {
        return;
    }
    foreach ($added_items as $item) {
        if (!$item instanceof WC_Order_Item_Product || !rcoe_item_uses_standard_stock($order, $item)) {
            continue;
        }
        $changed = wc_maybe_adjust_line_item_product_stock($item);
        if (is_wp_error($changed) || !rcoe_dotypos_stock_change($order, $item, (float) $item->get_quantity())) {
            $order->add_order_note('Dotypos: POZOR — sklad novej položky po úprave objednávky nebol synchronizovaný.');
        }
    }
}
add_action('woocommerce_ajax_order_items_added', 'rcoe_sync_added_items', 20, 2);

function rcoe_sync_removed_item($item_id, $item, $changed_stock, $order): void {
    if (!rcoe_is_deferred_cod_order($order) || !$item instanceof WC_Order_Item_Product || !rcoe_item_uses_standard_stock($order, $item)) {
        return;
    }
    $restored = (float) $item->get_quantity();
    if ($restored > 0 && !rcoe_dotypos_stock_change($order, $item, -$restored)) {
        $order->add_order_note('Dotypos: POZOR — vrátenie skladu odstránenej položky zlyhalo.');
    }
}
add_action('woocommerce_ajax_order_items_removed', 'rcoe_sync_removed_item', 20, 4);
