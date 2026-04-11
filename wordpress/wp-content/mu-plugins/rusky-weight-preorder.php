<?php
/**
 * Plugin Name: Rusky Weight Preorder
 * Description: Extraction-ready preorder core for variable-weight products.
 *
 * Scaffold status:
 * - local hardening surface only
 * - no live hook registration yet
 * - uses `rwp_*` names to avoid collisions with current `gastronom_*` ownership
 */

if (!defined('ABSPATH')) {
    exit;
}

function rwp_enabled($product_id): bool {
    return get_post_meta((int) $product_id, '_gastronom_weight_preorder', true) === 'yes';
}

function rwp_min_kg($product_id): float {
    return max(0.0, (float) get_post_meta((int) $product_id, '_gastronom_weight_preorder_min_kg', true));
}

function rwp_max_kg($product_id): float {
    $max = max(0.0, (float) get_post_meta((int) $product_id, '_gastronom_weight_preorder_max_kg', true));
    $min = rwp_min_kg($product_id);

    return $max > 0 ? $max : $min;
}

function rwp_avg_kg($product_id): float {
    $min = rwp_min_kg($product_id);
    $max = rwp_max_kg($product_id);
    if ($min > 0 && $max > 0) {
        return ($min + $max) / 2;
    }

    return max($min, $max, 0.0);
}

function rwp_price_per_kg($product_id): float {
    $product = wc_get_product($product_id);
    if (!$product) {
        return 0.0;
    }

    return (float) wc_get_price_to_display($product, ['qty' => 1]);
}

function rwp_reserved_qty($product_id, $exclude_order_id = 0): int {
    $product_id = (int) $product_id;
    $exclude_order_id = (int) $exclude_order_id;

    if ($product_id <= 0 || !function_exists('wc_get_orders')) {
        return 0;
    }

    $orders = wc_get_orders([
        'limit' => -1,
        'status' => ['wc-await-weight', 'wc-pending', 'wc-on-hold'],
        'return' => 'objects',
    ]);

    $reserved = 0;
    foreach ($orders as $order) {
        if (!$order instanceof WC_Order) {
            continue;
        }
        if ($exclude_order_id > 0 && (int) $order->get_id() === $exclude_order_id) {
            continue;
        }

        foreach ($order->get_items('line_item') as $item) {
            if ((int) $item->get_product_id() !== $product_id) {
                continue;
            }
            if ($item->get_meta('_gastronom_weight_confirmed', true) === 'yes') {
                continue;
            }

            $reserved += max(0, (int) $item->get_quantity());
        }
    }

    return $reserved;
}

function rwp_piece_capacity($product_id, ?float $raw_kg = null, $exclude_order_id = 0): int {
    $product_id = (int) $product_id;
    if (!rwp_enabled($product_id)) {
        return 0;
    }

    $max_kg = rwp_max_kg($product_id);
    if ($max_kg <= 0) {
        return 0;
    }

    if ($raw_kg === null) {
        $raw_kg = (float) get_post_meta($product_id, '_gastronom_cash_stock_kg', true);
        if ($raw_kg <= 0) {
            $raw_kg = (float) get_post_meta($product_id, '_stock', true);
        }
    }

    $reserved_qty = rwp_reserved_qty($product_id, $exclude_order_id);
    $safe_kg = max(0.0, (float) $raw_kg - ($reserved_qty * $max_kg));

    return max(0, (int) floor(($safe_kg + 0.000001) / $max_kg));
}

function rwp_apply_piece_stock($product_id, float $raw_kg): void {
    static $running = [];

    $product_id = (int) $product_id;
    if ($product_id <= 0 || !rwp_enabled($product_id)) {
        return;
    }

    if (!empty($running[$product_id])) {
        return;
    }

    $running[$product_id] = true;

    update_post_meta($product_id, '_gastronom_cash_stock_kg', $raw_kg);

    $pieces = rwp_piece_capacity($product_id, $raw_kg);
    update_post_meta($product_id, '_stock', $pieces);
    wc_update_product_stock_status($product_id, $pieces > 0 ? 'instock' : 'outofstock');
    update_post_meta($product_id, '_manage_stock', 'yes');
    update_post_meta($product_id, '_backorders', 'no');
    wc_delete_product_transients($product_id);

    unset($running[$product_id]);
}

function rwp_item_is_confirmed($item): bool {
    if (!$item instanceof WC_Order_Item_Product) {
        return false;
    }

    if ($item->get_meta('_gastronom_weight_preorder', true) !== 'yes') {
        return false;
    }

    return $item->get_meta('_gastronom_weight_confirmed', true) === 'yes';
}

function rwp_normalize_order_state($order): void {
    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }
    if (!$order instanceof WC_Order) {
        return;
    }

    $needs_confirmation = false;
    $has_preorder = false;
    $changed = false;

    foreach ($order->get_items('line_item') as $item) {
        if ($item->get_meta('_gastronom_weight_preorder', true) !== 'yes') {
            continue;
        }

        $has_preorder = true;
        if (rwp_item_is_confirmed($item)) {
            if ($item->get_meta('_gastronom_weight_confirmed', true) !== 'yes') {
                $item->update_meta_data('_gastronom_weight_confirmed', 'yes');
                $item->save();
                $changed = true;
            }
            continue;
        }

        $needs_confirmation = true;
    }

    if (!$has_preorder) {
        return;
    }

    $desired = $needs_confirmation ? 'yes' : 'no';
    if ($order->get_meta('_gastronom_requires_weight_confirmation', true) !== $desired) {
        $order->update_meta_data('_gastronom_requires_weight_confirmation', $desired);
        $changed = true;
    }

    if ($changed) {
        $order->save();
    }
}

function rwp_product_has_preorder_weight($product): bool {
    if (!$product instanceof WC_Product) {
        return false;
    }

    return rwp_enabled($product->get_id());
}

function rwp_order_has_preorder_weight($order): bool {
    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }

    if (!$order instanceof WC_Order) {
        return false;
    }

    foreach ($order->get_items('line_item') as $item) {
        if ($item->get_meta('_gastronom_weight_preorder', true) === 'yes') {
            return true;
        }
    }

    return false;
}

function rwp_order_requires_confirmation($order): bool {
    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }

    if (!$order instanceof WC_Order) {
        return false;
    }

    foreach ($order->get_items('line_item') as $item) {
        if ($item->get_meta('_gastronom_weight_preorder', true) === 'yes' && !rwp_item_is_confirmed($item)) {
            return true;
        }
    }

    return false;
}

function rwp_reserve_site_stock($order): void {
    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }
    if (!$order instanceof WC_Order) {
        return;
    }
    if ($order->get_meta('_gastronom_preorder_site_reserved', true) === 'yes') {
        return;
    }

    foreach ($order->get_items('line_item') as $item) {
        if ($item->get_meta('_gastronom_weight_preorder', true) !== 'yes') {
            continue;
        }

        $product = $item->get_product();
        if (!$product) {
            continue;
        }

        $product_id = $product->get_id();
        $current = (int) get_post_meta($product_id, '_stock', true);
        $next = max(0, $current - (int) $item->get_quantity());
        update_post_meta($product_id, '_stock', $next);
        wc_update_product_stock_status($product_id, $next > 0 ? 'instock' : 'outofstock');
        wc_delete_product_transients($product_id);
    }

    $order->update_meta_data('_gastronom_preorder_site_reserved', 'yes');
    $order->save();
}

function rwp_restore_site_stock($order): void {
    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }
    if (!$order instanceof WC_Order) {
        return;
    }
    if ($order->get_meta('_gastronom_preorder_site_reserved', true) !== 'yes') {
        return;
    }
    if ($order->get_meta('_gastronom_preorder_site_restored', true) === 'yes') {
        return;
    }

    foreach ($order->get_items('line_item') as $item) {
        if ($item->get_meta('_gastronom_weight_preorder', true) !== 'yes') {
            continue;
        }

        $product = $item->get_product();
        if (!$product) {
            continue;
        }

        $product_id = $product->get_id();
        $current = (int) get_post_meta($product_id, '_stock', true);
        $next = max(0, $current + (int) $item->get_quantity());
        update_post_meta($product_id, '_stock', $next);
        wc_update_product_stock_status($product_id, $next > 0 ? 'instock' : 'outofstock');
        wc_delete_product_transients($product_id);
    }

    $order->update_meta_data('_gastronom_preorder_site_restored', 'yes');
    $order->save();
}

function rwp_can_reduce_order_stock($can_reduce, $order) {
    if (rwp_order_has_preorder_weight($order)) {
        return false;
    }

    return $can_reduce;
}

function rwp_can_restore_order_stock($can_restore, $order) {
    if (rwp_order_has_preorder_weight($order)) {
        return false;
    }

    return $can_restore;
}

function rwp_prepare_checkout_processed_order($order_id, $posted_data, $order): void {
    if (!$order instanceof WC_Order || !rwp_order_requires_confirmation($order)) {
        return;
    }

    foreach ($order->get_items('line_item') as $item) {
        if ($item->get_meta('_gastronom_weight_preorder', true) !== 'yes') {
            continue;
        }

        $item->delete_meta_data('_gastronom_actual_weight_kg');
        $item->update_meta_data('_gastronom_weight_confirmed', 'no');
        $item->update_meta_data('_gastronom_weight_cash_synced', 'no');
        $item->delete_meta_data('_gastronom_weight_cash_restored');
        $item->save();
    }

    $order->delete_meta_data('_order_stock_reduced');

    if ($order->get_meta('_gastronom_requires_weight_confirmation', true) === 'no') {
        return;
    }

    $order->update_meta_data('_gastronom_requires_weight_confirmation', 'yes');
    $order->save();

    if ($order->get_status() !== 'await-weight') {
        $order->update_status('await-weight', 'Awaiting actual weight confirmation.', true);
    }

    rwp_reserve_site_stock($order);

    if (function_exists('rpn_send_preorder_created_emails')) {
        rpn_send_preorder_created_emails($order);
    }
}

function rwp_register_await_weight_status(): void {
    register_post_status('wc-await-weight', [
        'label'                     => 'На уточнении веса',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'label_count'               => _n_noop('На уточнении веса <span class="count">(%s)</span>', 'На уточнении веса <span class="count">(%s)</span>'),
    ]);
}

function rwp_order_statuses($statuses) {
    $new = [];
    foreach ($statuses as $key => $label) {
        $new[$key] = $label;
        if ($key === 'wc-pending') {
            $new['wc-await-weight'] = 'На уточнении веса';
        }
    }

    return $new;
}

if (!function_exists('gastronom_weight_preorder_enabled')) {
    function gastronom_weight_preorder_enabled($product_id): bool {
        return rwp_enabled($product_id);
    }
}

if (!function_exists('gastronom_weight_preorder_min_kg')) {
    function gastronom_weight_preorder_min_kg($product_id): float {
        return rwp_min_kg($product_id);
    }
}

if (!function_exists('gastronom_weight_preorder_max_kg')) {
    function gastronom_weight_preorder_max_kg($product_id): float {
        return rwp_max_kg($product_id);
    }
}

if (!function_exists('gastronom_weight_preorder_avg_kg')) {
    function gastronom_weight_preorder_avg_kg($product_id): float {
        return rwp_avg_kg($product_id);
    }
}

if (!function_exists('gastronom_weight_preorder_price_per_kg')) {
    function gastronom_weight_preorder_price_per_kg($product_id): float {
        return rwp_price_per_kg($product_id);
    }
}

if (!function_exists('gastronom_weight_preorder_reserved_qty')) {
    function gastronom_weight_preorder_reserved_qty($product_id, $exclude_order_id = 0): int {
        return rwp_reserved_qty($product_id, $exclude_order_id);
    }
}

if (!function_exists('gastronom_weight_preorder_piece_capacity')) {
    function gastronom_weight_preorder_piece_capacity($product_id, ?float $raw_kg = null, $exclude_order_id = 0): int {
        return rwp_piece_capacity($product_id, $raw_kg, $exclude_order_id);
    }
}

if (!function_exists('gastronom_apply_preorder_piece_stock')) {
    function gastronom_apply_preorder_piece_stock($product_id, float $raw_kg): void {
        rwp_apply_piece_stock($product_id, $raw_kg);
    }
}

if (!function_exists('gastronom_preorder_item_is_confirmed')) {
    function gastronom_preorder_item_is_confirmed($item): bool {
        return rwp_item_is_confirmed($item);
    }
}

if (!function_exists('gastronom_normalize_preorder_order_state')) {
    function gastronom_normalize_preorder_order_state($order): void {
        rwp_normalize_order_state($order);
    }
}

if (!function_exists('gastronom_product_has_preorder_weight')) {
    function gastronom_product_has_preorder_weight($product): bool {
        return rwp_product_has_preorder_weight($product);
    }
}

if (!function_exists('gastronom_order_has_preorder_weight')) {
    function gastronom_order_has_preorder_weight($order): bool {
        return rwp_order_has_preorder_weight($order);
    }
}

if (!function_exists('gastronom_order_requires_weight_confirmation')) {
    function gastronom_order_requires_weight_confirmation($order): bool {
        return rwp_order_requires_confirmation($order);
    }
}

if (!function_exists('gastronom_reserve_preorder_site_stock')) {
    function gastronom_reserve_preorder_site_stock($order): void {
        rwp_reserve_site_stock($order);
    }
}

if (!function_exists('gastronom_restore_preorder_site_stock')) {
    function gastronom_restore_preorder_site_stock($order): void {
        rwp_restore_site_stock($order);
    }
}

if (!function_exists('gastronom_can_reduce_order_stock')) {
    function gastronom_can_reduce_order_stock($can_reduce, $order) {
        return rwp_can_reduce_order_stock($can_reduce, $order);
    }
}

if (!function_exists('gastronom_can_restore_order_stock')) {
    function gastronom_can_restore_order_stock($can_restore, $order) {
        return rwp_can_restore_order_stock($can_restore, $order);
    }
}

if (!function_exists('gastronom_prepare_checkout_processed_preorder')) {
    function gastronom_prepare_checkout_processed_preorder($order_id, $posted_data, $order): void {
        rwp_prepare_checkout_processed_order($order_id, $posted_data, $order);
    }
}

if (!function_exists('gastronom_register_await_weight_status')) {
    function gastronom_register_await_weight_status(): void {
        rwp_register_await_weight_status();
    }
}

if (!function_exists('gastronom_inject_await_weight_status')) {
    function gastronom_inject_await_weight_status($statuses) {
        return rwp_order_statuses($statuses);
    }
}
