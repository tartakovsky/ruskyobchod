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

        $warehouse_id = (string) ($settings['dotypos']['warehouseId'] ?? '');
        if ($warehouse_id === '') {
            continue;
        }

        $ok = rdsb_dotypos_post('/warehouses/' . rawurlencode($warehouse_id) . '/sales', [
            'currency' => $order->get_currency() ?: 'EUR',
            'items' => [
                [
                    '_productId' => (int) $dotypos_id,
                    'quantity' => $actual_weight,
                    'note' => 'Predaj cez WooCommerce potvrdeny predobjednavka #' . $order->get_id(),
                ],
            ],
        ]);
        if (!$ok) {
            continue;
        }

        $dotypos = Dotypos::instance();
        $latest_raw = null;
        if ($dotypos && !empty($dotypos->dotyposService)) {
            $warehouse_row = $dotypos->dotyposService->getProductOnWarehouse($warehouse_id, $dotypos_id);
            if (is_array($warehouse_row) && isset($warehouse_row['stockQuantityStatus'])) {
                $latest_raw = (float) $warehouse_row['stockQuantityStatus'];
            }
        }

        if ($latest_raw !== null && function_exists('rwp_apply_piece_stock')) {
            rwp_apply_piece_stock($product->get_id(), $latest_raw);
        }

        $item->update_meta_data('_gastronom_weight_cash_synced', 'yes');
        $item->save();
        $order->add_order_note('Dotypos: potvrdený predobjednávkový tovar odpísaný ako predaj cez WooCommerce.');
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

function rdsb_order_stock_sync_enabled(): bool {
    if (!class_exists('Dotypos')) {
        return false;
    }

    $settings = get_option(Dotypos::$keys['settings']);
    return !empty($settings['product']['movement']['syncToDotypos'])
        && !empty($settings['dotypos']['apiKey'])
        && !empty($settings['dotypos']['cloudId'])
        && !empty($settings['dotypos']['warehouseId']);
}

function rdsb_dotypos_settings(): array {
    if (!class_exists('Dotypos')) {
        return [];
    }

    $settings = get_option(Dotypos::$keys['settings']);
    return is_array($settings) ? $settings : [];
}

function rdsb_dotypos_access_token(array $settings, bool $force_refresh = false): string {
    $cloud_id = (string) ($settings['dotypos']['cloudId'] ?? '');
    $api_key = (string) ($settings['dotypos']['apiKey'] ?? '');
    if ($cloud_id === '' || $api_key === '') {
        return '';
    }

    $cache_key = 'rusky_dotypos_access_token_' . md5($cloud_id);
    if (!$force_refresh) {
        $cached = get_transient($cache_key);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }
    }

    $response = wp_remote_post('https://api.dotykacka.cz/v2/signin/token', [
        'headers' => [
            'Authorization' => 'User ' . $api_key,
            'Content-Type' => 'application/json',
        ],
        'body' => wp_json_encode(['_cloudId' => (int) $cloud_id]),
        'timeout' => 30,
    ]);
    if (is_wp_error($response)) {
        wc_get_logger()->error('Dotypos sale auth failed: ' . $response->get_error_message(), ['source' => 'dotypos_integration']);
        return '';
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    $data = json_decode((string) wp_remote_retrieve_body($response), true);
    $token = is_array($data) ? (string) ($data['accessToken'] ?? '') : '';
    if ($code < 200 || $code >= 300 || $token === '') {
        wc_get_logger()->error('Dotypos sale auth returned HTTP ' . $code, ['source' => 'dotypos_integration']);
        return '';
    }

    set_transient($cache_key, $token, 55 * MINUTE_IN_SECONDS);
    return $token;
}

function rdsb_dotypos_post(string $path, array $payload, bool $retry = true): bool {
    $settings = rdsb_dotypos_settings();
    $cloud_id = (string) ($settings['dotypos']['cloudId'] ?? '');
    if ($cloud_id === '') {
        return false;
    }

    $token = rdsb_dotypos_access_token($settings);
    if ($token === '') {
        return false;
    }

    $url = 'https://api.dotykacka.cz/v2/clouds/' . rawurlencode($cloud_id) . $path;
    $response = wp_remote_post($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ],
        'body' => wp_json_encode($payload),
        'timeout' => 30,
    ]);
    if (is_wp_error($response)) {
        wc_get_logger()->error('Dotypos sale post failed: ' . $response->get_error_message(), ['source' => 'dotypos_integration']);
        return false;
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    if (($code === 401 || $code === 403) && $retry) {
        rdsb_dotypos_access_token($settings, true);
        return rdsb_dotypos_post($path, $payload, false);
    }

    if ($code < 200 || $code >= 300) {
        wc_get_logger()->error('Dotypos sale post returned HTTP ' . $code . ': ' . wp_remote_retrieve_body($response), ['source' => 'dotypos_integration']);
        return false;
    }

    return true;
}

function rdsb_collect_order_stock_changes($order, bool $restore = false): array {
    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }
    if (!$order instanceof WC_Order || !class_exists('Dotypos')) {
        return [];
    }

    $changes = [];
    $id_key = Dotypos::$keys['product']['field-id'];
    foreach ($order->get_items('line_item') as $item) {
        if (!$item instanceof WC_Order_Item_Product) {
            continue;
        }

        $product = $item->get_product();
        if (!$product) {
            continue;
        }

        $dotypos_id = $product->get_meta($id_key);
        if (empty($dotypos_id)) {
            continue;
        }

        $resolved_qty = null;
        if (function_exists('gastronom_resolve_dotypos_order_sync_quantity')) {
            $resolved_qty = gastronom_resolve_dotypos_order_sync_quantity($order, $item, $restore);
            if ($resolved_qty === false) {
                continue;
            }
        }

        $qty = $resolved_qty === null ? (float) $item->get_quantity() : (float) $resolved_qty;
        if ($qty <= 0) {
            continue;
        }

        $key = (string) $dotypos_id;
        if (!isset($changes[$key])) {
            $changes[$key] = 0.0;
        }
        $changes[$key] += $qty;
    }

    return $changes;
}

function rdsb_sync_order_sale_to_dotypos($order): void {
    if (!rdsb_order_stock_sync_enabled()) {
        return;
    }
    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }
    if (!$order instanceof WC_Order) {
        return;
    }

    $meta_key = '_dotypos_stock_synced';
    if ($order->get_meta($meta_key, true)) {
        return;
    }

    $changes = rdsb_collect_order_stock_changes($order, false);
    if (!$changes) {
        return;
    }

    $settings = rdsb_dotypos_settings();
    $warehouse_id = (string) ($settings['dotypos']['warehouseId'] ?? '');
    if ($warehouse_id === '') {
        return;
    }

    $note = 'Predaj cez WooCommerce objednavka #' . $order->get_id();
    $items = [];
    foreach ($changes as $dotypos_id => $qty) {
        if ($qty > 0) {
            $items[] = [
                '_productId' => (int) $dotypos_id,
                'quantity' => (float) $qty,
                'note' => $note,
            ];
        }
    }
    if (!$items) {
        return;
    }

    $ok = rdsb_dotypos_post('/warehouses/' . rawurlencode($warehouse_id) . '/sales', [
        'currency' => $order->get_currency() ?: 'EUR',
        'items' => $items,
    ]);
    if (!$ok) {
        return;
    }

    $order->update_meta_data($meta_key, 1);
    $order->add_order_note('Dotypos: sklad odpísaný ako predaj cez WooCommerce.');
    $order->save();
}

function rdsb_restore_order_stock_to_dotypos($order): void {
    if (!rdsb_order_stock_sync_enabled()) {
        return;
    }
    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }
    if (!$order instanceof WC_Order) {
        return;
    }

    $meta_key = '_dotypos_stock_synced';
    if (!$order->get_meta($meta_key, true)) {
        return;
    }

    $changes = rdsb_collect_order_stock_changes($order, true);
    if (!$changes) {
        return;
    }

    $settings = rdsb_dotypos_settings();
    $warehouse_id = (string) ($settings['dotypos']['warehouseId'] ?? '');
    if ($warehouse_id === '') {
        return;
    }

    $items = [];
    foreach ($changes as $dotypos_id => $qty) {
        if ($qty > 0) {
            $items[] = [
                '_productId' => (int) $dotypos_id,
                'quantity' => (float) $qty,
            ];
        }
    }
    if (!$items) {
        return;
    }

    $ok = rdsb_dotypos_post('/warehouses/' . rawurlencode($warehouse_id) . '/stockups', [
        'updatePurchasePrice' => false,
        'invoiceNumber' => 'WC-ORDER-' . $order->get_id() . '-RESTORE',
        'note' => 'Vratka WooCommerce objednavka #' . $order->get_id(),
        'items' => $items,
    ]);
    if (!$ok) {
        return;
    }

    $order->delete_meta_data($meta_key);
    $order->add_order_note('Dotypos: sklad vrátený po zrušení/refundácii WooCommerce objednávky.');
    $order->save();
}

function rdsb_replace_vendor_order_stock_sync(): void {
    if (!class_exists('Dotypos')) {
        return;
    }

    $dotypos = Dotypos::instance();
    if (!$dotypos) {
        return;
    }

    remove_action('woocommerce_reduce_order_stock', [$dotypos, 'handle_reduce_order_stock'], 10);
    remove_action('woocommerce_restore_order_stock', [$dotypos, 'handle_restore_order_stock'], 10);

    add_action('woocommerce_reduce_order_stock', 'rdsb_sync_order_sale_to_dotypos', 10, 1);
    add_action('woocommerce_restore_order_stock', 'rdsb_restore_order_stock_to_dotypos', 10, 1);
}
add_action('plugins_loaded', 'rdsb_replace_vendor_order_stock_sync', 100);

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
