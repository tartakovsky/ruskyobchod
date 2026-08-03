<?php
/**
 * Plugin Name: Rusky Dotypos Fiscalization
 * Description: Fiscalizes paid WooCommerce web sales on the Slovak Dotypos register.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

const RDF_STATE_META = '_rusky_dotypos_fiscal_state';
const RDF_ORDER_ID_META = '_rusky_dotypos_fiscal_order_id';
const RDF_ERROR_META = '_rusky_dotypos_fiscal_error';
const RDF_RETRY_HOOK = 'rusky_dotypos_retry_fiscalization';
const RDF_RETRY_ATTEMPTS_META = '_rusky_dotypos_fiscal_retry_attempts';
const RDF_RETRY_SNAPSHOT_META = '_rusky_dotypos_fiscal_retry_snapshot';
const RDF_RETRY_OFFSET_META = '_rusky_dotypos_fiscal_retry_offset';

function rdf_logger(): WC_Logger_Interface {
    return wc_get_logger();
}

function rdf_log(string $level, string $message, array $context = []): void {
    $context['source'] = 'rusky_dotypos_fiscalization';
    rdf_logger()->log($level, $message, $context);
}

function rdf_order($order): ?WC_Order {
    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }
    return $order instanceof WC_Order ? $order : null;
}

function rdf_supported_payment_method(WC_Order $order): bool {
    $method = (string) $order->get_payment_method();
    return in_array($method, ['cod', 'woocommerce_payments'], true)
        || strpos($method, 'stripe') !== false;
}

function rdf_payment_method_id(WC_Order $order): int {
    // Dotypos standard payment method IDs: Online and Bank transfer.
    return $order->get_payment_method() === 'cod' ? 900000009 : 900000019;
}

function rdf_order_owns_dotypos_stock_movement($order): bool {
    $order = rdf_order($order);
    return $order && $order->get_meta(RDF_STATE_META, true) === 'fiscalized';
}

function rdf_has_unconfirmed_weight_item(WC_Order $order): bool {
    foreach ($order->get_items('line_item') as $item) {
        if ($item->get_meta('_gastronom_weight_preorder', true) !== 'yes') {
            continue;
        }
        if (!function_exists('rwp_item_is_confirmed') || !rwp_item_is_confirmed($item)) {
            return true;
        }
    }
    return false;
}

function rdf_should_fiscalize(WC_Order $order): bool {
    if (!rdf_supported_payment_method($order) || rdf_has_unconfirmed_weight_item($order)) {
        return false;
    }

    if ($order->get_payment_method() === 'cod') {
        return in_array($order->get_status(), ['processing', 'on-hold', 'completed'], true);
    }

    return $order->is_paid();
}

function rdf_access_context() {
    if (!class_exists('Dotypos')) {
        return new WP_Error('rdf_missing_dotypos', 'Dotypos plugin is unavailable.');
    }

    $settings = get_option(Dotypos::$keys['settings'], []);
    $refresh_token = (string) ($settings['dotypos']['apiKey'] ?? '');
    $cloud_id = (string) ($settings['dotypos']['cloudId'] ?? '');
    $warehouse_id = (string) ($settings['dotypos']['warehouseId'] ?? '');
    if ($refresh_token === '' || $cloud_id === '' || $warehouse_id === '') {
        return new WP_Error('rdf_missing_settings', 'Dotypos API or warehouse settings are incomplete.');
    }

    $signin = wp_remote_post('https://api.dotykacka.cz/v2/signin/token', [
        'headers' => [
            'Authorization' => 'User ' . $refresh_token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ],
        'body' => wp_json_encode(['_cloudId' => $cloud_id]),
        'timeout' => 20,
    ]);
    if (is_wp_error($signin)) {
        return $signin;
    }
    if (wp_remote_retrieve_response_code($signin) !== 201) {
        return new WP_Error('rdf_signin_failed', 'Dotypos sign-in returned HTTP ' . wp_remote_retrieve_response_code($signin) . '.');
    }

    $body = json_decode(wp_remote_retrieve_body($signin), true);
    $access_token = (string) ($body['accessToken'] ?? '');
    if ($access_token === '') {
        return new WP_Error('rdf_missing_access_token', 'Dotypos did not return an access token.');
    }

    return [
        'access_token' => $access_token,
        'cloud_id' => $cloud_id,
        'warehouse_id' => $warehouse_id,
    ];
}

function rdf_api_get(array $context, string $path, bool $empty_on_404 = false) {
    $url = 'https://api.dotykacka.cz/v2/clouds/' . rawurlencode($context['cloud_id']) . $path;
    $response = wp_remote_get($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $context['access_token'],
            'Accept' => 'application/json',
        ],
        'timeout' => 20,
    ]);
    if (is_wp_error($response)) {
        return $response;
    }
    $status_code = wp_remote_retrieve_response_code($response);
    if ($empty_on_404 && $status_code === 404) {
        return ['data' => []];
    }
    if ($status_code !== 200) {
        return new WP_Error('rdf_api_get_failed', 'Dotypos GET returned HTTP ' . $status_code . '.');
    }
    return json_decode(wp_remote_retrieve_body($response), true);
}

function rdf_branch_id(array $context) {
    $payload = rdf_api_get($context, '/warehouse-branches?limit=100');
    if (is_wp_error($payload)) {
        return $payload;
    }
    $matches = [];
    foreach (($payload['data'] ?? []) as $relation) {
        if ((string) ($relation['_warehouseId'] ?? '') === $context['warehouse_id']
            && !empty($relation['visible'])) {
            $matches[] = (int) $relation['_branchId'];
        }
    }
    $matches = array_values(array_unique(array_filter($matches)));
    if (count($matches) !== 1) {
        return new WP_Error('rdf_branch_ambiguous', 'Expected one visible Dotypos branch for the configured warehouse; found ' . count($matches) . '.');
    }
    return $matches[0];
}

function rdf_existing_order(array $context, WC_Order $order) {
    $external_id = 'WC-' . $order->get_id();
    $filter = rawurlencode('externalId|eq|' . $external_id);
    $payload = rdf_api_get($context, '/orders?limit=10&filter=' . $filter, true);
    if (is_wp_error($payload)) {
        return $payload;
    }
    foreach (($payload['data'] ?? []) as $remote_order) {
        if ((string) ($remote_order['externalId'] ?? '') === $external_id
            && !empty($remote_order['paid']) && empty($remote_order['canceledDate'])) {
            return $remote_order;
        }
    }
    return null;
}

function rdf_item_quantity(WC_Order $order, WC_Order_Item_Product $item): float {
    if ($item->get_meta('_gastronom_weight_preorder', true) === 'yes') {
        $actual = (float) $item->get_meta('_gastronom_actual_weight_kg', true);
        if ($actual > 0) {
            return $actual;
        }
    }
    return (float) $item->get_quantity();
}

function rdf_action_items(WC_Order $order) {
    $items = [];
    $gross_total = 0.0;
    foreach ($order->get_items('line_item') as $item) {
        if (!$item instanceof WC_Order_Item_Product) {
            continue;
        }
        $product = $item->get_product();
        $dotypos_id = $product ? (string) $product->get_meta(Dotypos::$keys['product']['field-id']) : '';
        $qty = rdf_item_quantity($order, $item);
        if ($dotypos_id === '' || $qty <= 0) {
            return new WP_Error('rdf_unpaired_item', 'Order item ' . $item->get_id() . ' has no Dotypos product mapping or valid quantity.');
        }

        $gross = (float) $item->get_total() + (float) $item->get_total_tax();
        $gross_total += $gross;
        $items[] = [
            'id' => (int) $dotypos_id,
            'qty' => $qty,
            'manual-price' => round($gross / $qty, wc_get_price_decimals()),
            'note' => 'Woo item ' . $item->get_id(),
        ];
    }

    if (!$items) {
        return new WP_Error('rdf_empty_order', 'Order has no fiscalizable line items.');
    }

    // POS Actions only accept product items. Spread shipping, COD fee and order
    // rounding proportionally over the sold products so the fiscal receipt total
    // exactly matches the amount collected by WooCommerce.
    $difference = (float) $order->get_total() - $gross_total;
    if (abs($difference) > 0.000001) {
        $base = max(abs($gross_total), 0.01);
        $allocated = 0.0;
        $last = count($items) - 1;
        foreach ($items as $index => &$action_item) {
            $line_gross = $action_item['manual-price'] * $action_item['qty'];
            $share = $index === $last ? $difference - $allocated : round($difference * ($line_gross / $base), 2);
            $allocated += $share;
            $action_item['manual-price'] = round(($line_gross + $share) / $action_item['qty'], 4);
        }
        unset($action_item);
    }

    // Dotypos rounds every POS line independently. Proportional allocation
    // can therefore drift by a few cents on larger orders. Put the final
    // cent-level residual on a quantity-one line so the issued receipt total
    // is exactly equal to the WooCommerce amount.
    $pos_total = 0.0;
    foreach ($items as $action_item) {
        $pos_total += round($action_item['manual-price'] * $action_item['qty'], 2);
    }
    $rounding_residual = round((float) $order->get_total() - $pos_total, 2);
    if (abs($rounding_residual) >= 0.01) {
        $target_index = null;
        foreach ($items as $index => $action_item) {
            if (abs((float) $action_item['qty'] - 1.0) <= 0.000001) {
                $target_index = $index;
                break;
            }
        }
        if ($target_index === null) {
            $target_index = count($items) - 1;
        }
        $target_qty = max((float) $items[$target_index]['qty'], 0.000001);
        $items[$target_index]['manual-price'] = round(
            (float) $items[$target_index]['manual-price'] + ($rounding_residual / $target_qty),
            4
        );
    }

    return $items;
}

function rdf_mark_fiscalized(WC_Order $order, array $remote_order): void {
    $remote_id = (string) ($remote_order['id'] ?? $remote_order['order']['id'] ?? '');
    $order->update_meta_data(RDF_STATE_META, 'fiscalized');
    $order->update_meta_data(RDF_ORDER_ID_META, $remote_id);
    $order->delete_meta_data(RDF_ERROR_META);
    $order->save();
    $order->add_order_note('Dotypos: фискальный чек создан' . ($remote_id !== '' ? ' (order ' . $remote_id . ')' : '') . '.');
}

function rdf_error_is_retryable(string $message): bool {
    return strpos($message, 'HTTP 404') !== false
        || strpos($message, 'HTTP 5') !== false
        || stripos($message, 'timed out') !== false
        || stripos($message, 'timeout') !== false
        || stripos($message, 'could not resolve') !== false
        || stripos($message, 'connection') !== false;
}

function rdf_retry_timestamp(): int {
    $now = new DateTimeImmutable('now', wp_timezone());
    $hour = (int) $now->format('G');
    if ($hour < 8) {
        return $now->setTime(8, 15)->getTimestamp();
    }
    if ($hour >= 20) {
        return $now->modify('+1 day')->setTime(8, 15)->getTimestamp();
    }
    return $now->modify('+15 minutes')->getTimestamp();
}

function rdf_schedule_retry(WC_Order $order, ?int $timestamp = null): void {
    $args = [$order->get_id()];
    $timestamp = $timestamp ?? rdf_retry_timestamp();

    if (function_exists('as_next_scheduled_action') && function_exists('as_schedule_single_action')) {
        // While this hook is running, Action Scheduler can report the current
        // action as the "next" matching action. A transient failure would then
        // finish the current action without ever creating its successor.
        if (doing_action(RDF_RETRY_HOOK)) {
            as_schedule_single_action($timestamp, RDF_RETRY_HOOK, $args, 'rusky-dotypos');
            return;
        }
        if (!as_next_scheduled_action(RDF_RETRY_HOOK, $args, 'rusky-dotypos')) {
            as_schedule_single_action($timestamp, RDF_RETRY_HOOK, $args, 'rusky-dotypos');
        }
        return;
    }

    if (!wp_next_scheduled(RDF_RETRY_HOOK, $args)) {
        wp_schedule_single_event($timestamp, RDF_RETRY_HOOK, $args);
    }
}

function rdf_pos_is_online(array $context): bool {
    $branch_id = rdf_branch_id($context);
    if (is_wp_error($branch_id)) {
        return false;
    }

    $url = 'https://api.dotykacka.cz/v2/clouds/' . rawurlencode($context['cloud_id'])
        . '/branches/' . $branch_id . '/pos-actions';
    $response = wp_remote_post($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $context['access_token'],
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ],
        'body' => wp_json_encode([
            'action' => 'order/hello',
            'validity' => (time() + 120) * 1000,
            'idempotency-key' => 'rdf-hello-' . gmdate('YmdHi'),
        ]),
        'timeout' => 30,
    ]);
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return false;
    }
    $body = json_decode(wp_remote_retrieve_body($response), true);
    return (int) ($body['code'] ?? -1) === 0;
}

function rdf_retry_stock_service() {
    if (!class_exists('Dotypos')) {
        return new WP_Error('rdf_retry_missing_dotypos', 'Dotypos plugin is unavailable.');
    }
    $settings = get_option(Dotypos::$keys['settings'], []);
    $warehouse_id = (string) ($settings['dotypos']['warehouseId'] ?? '');
    $dotypos = Dotypos::instance();
    if ($warehouse_id === '' || !$dotypos || empty($dotypos->dotyposService)) {
        return new WP_Error('rdf_retry_missing_stock_service', 'Dotypos stock service is unavailable.');
    }
    return [
        'warehouse_id' => $warehouse_id,
        'service' => $dotypos->dotyposService,
    ];
}

function rdf_retry_capture_snapshot(WC_Order $order) {
    $stock_context = rdf_retry_stock_service();
    if (is_wp_error($stock_context)) {
        return $stock_context;
    }

    $snapshot = [];
    foreach ($order->get_items('line_item') as $item_id => $item) {
        if (!$item instanceof WC_Order_Item_Product) {
            continue;
        }
        $product = $item->get_product();
        $dotypos_id = $product ? (string) $product->get_meta(Dotypos::$keys['product']['field-id']) : '';
        $qty = rdf_item_quantity($order, $item);
        if ($dotypos_id === '' || $qty <= 0) {
            return new WP_Error('rdf_retry_invalid_item', 'Invalid Dotypos mapping or quantity for item ' . $item_id . '.');
        }
        $row = $stock_context['service']->getProductOnWarehouse($stock_context['warehouse_id'], $dotypos_id);
        if (!is_array($row) || !isset($row['stockQuantityStatus'])) {
            return new WP_Error('rdf_retry_stock_read_failed', 'Cannot read Dotypos stock for item ' . $item_id . '.');
        }
        $snapshot[] = [
            'item_id' => (int) $item_id,
            'dotypos_id' => $dotypos_id,
            'qty' => (float) $qty,
            'before' => (float) $row['stockQuantityStatus'],
        ];
    }
    return $snapshot ?: new WP_Error('rdf_retry_empty_snapshot', 'No stock lines available for retry.');
}

function rdf_retry_apply_stock_offset(WC_Order $order, bool $allow_write = true): bool {
    if ($order->get_meta(RDF_RETRY_OFFSET_META, true) === 'yes') {
        return true;
    }
    $snapshot = $order->get_meta(RDF_RETRY_SNAPSHOT_META, true);
    if (!is_array($snapshot) || !$snapshot) {
        return false;
    }
    $stock_context = rdf_retry_stock_service();
    if (is_wp_error($stock_context)) {
        return false;
    }

    $all_restored = true;
    $all_deducted = true;
    foreach ($snapshot as $line) {
        $row = $stock_context['service']->getProductOnWarehouse(
            $stock_context['warehouse_id'],
            (string) $line['dotypos_id']
        );
        if (!is_array($row) || !isset($row['stockQuantityStatus'])) {
            return false;
        }
        $current = (float) $row['stockQuantityStatus'];
        $before = (float) $line['before'];
        $deducted = $before - (float) $line['qty'];
        $all_restored = $all_restored && abs($current - $before) <= 0.0001;
        $all_deducted = $all_deducted && abs($current - $deducted) <= 0.0001;
    }

    if ($all_restored) {
        $order->update_meta_data(RDF_RETRY_OFFSET_META, 'yes');
        $order->delete_meta_data(RDF_RETRY_SNAPSHOT_META);
        $order->save();
        return true;
    }
    if (!$all_deducted) {
        rdf_log('error', 'Order ' . $order->get_id() . ': retry stock state is mixed; automatic offset stopped.');
        return false;
    }
    if (!$allow_write || !function_exists('rdsb_dotypos_post')) {
        return false;
    }

    $items = [];
    foreach ($snapshot as $line) {
        $items[] = [
            '_productId' => (int) $line['dotypos_id'],
            'quantity' => (float) $line['qty'],
        ];
    }
    $ok = rdsb_dotypos_post(
        '/warehouses/' . rawurlencode($stock_context['warehouse_id']) . '/stockups',
        [
            'updatePurchasePrice' => false,
            'invoiceNumber' => 'WC-' . $order->get_id() . '-FISCAL-RETRY-OFFSET',
            'note' => 'Offset after delayed WooCommerce fiscalization #' . $order->get_id(),
            'items' => $items,
        ]
    );
    if (!$ok) {
        return false;
    }

    // Do one read-back pass. If the stock API is eventually consistent, the
    // scheduled retry will observe the restored values without writing again.
    return rdf_retry_apply_stock_offset($order, false);
}

function rdf_retry_fiscalization($order_id): void {
    $order = wc_get_order($order_id);
    if (!$order instanceof WC_Order) {
        return;
    }
    $attempts = (int) $order->get_meta(RDF_RETRY_ATTEMPTS_META, true) + 1;
    $order->update_meta_data(RDF_RETRY_ATTEMPTS_META, $attempts);
    $order->save();
    if ($attempts > 24) {
        $order->add_order_note('Dotypos: автоматическая повторная фискализация остановлена после 24 попыток.');
        return;
    }

    if ($order->get_meta(RDF_STATE_META, true) === 'fiscalized') {
        if ($order->get_meta(RDF_RETRY_SNAPSHOT_META, true) && !rdf_retry_apply_stock_offset($order)) {
            rdf_schedule_retry($order, time() + 15 * MINUTE_IN_SECONDS);
        }
        return;
    }
    if ($order->get_meta(RDF_STATE_META, true) !== 'stock-fallback') {
        return;
    }

    $context = rdf_access_context();
    if (is_wp_error($context) || !rdf_pos_is_online($context)) {
        rdf_schedule_retry($order);
        return;
    }
    $existing = rdf_existing_order($context, $order);
    if (is_wp_error($existing)) {
        rdf_schedule_retry($order, time() + 15 * MINUTE_IN_SECONDS);
        return;
    }
    if (is_array($existing)) {
        rdf_mark_fiscalized($order, $existing);
        if ($order->get_meta(RDF_RETRY_SNAPSHOT_META, true) && !rdf_retry_apply_stock_offset($order)) {
            rdf_schedule_retry($order, time() + 15 * MINUTE_IN_SECONDS);
        }
        return;
    }

    $snapshot = rdf_retry_capture_snapshot($order);
    if (is_wp_error($snapshot)) {
        rdf_log('error', 'Order ' . $order->get_id() . ': ' . $snapshot->get_error_message());
        return;
    }
    $order->update_meta_data(RDF_RETRY_SNAPSHOT_META, $snapshot);
    $order->delete_meta_data(RDF_STATE_META);
    $order->delete_meta_data(RDF_ERROR_META);
    $order->save();

    if (!rdf_fiscalize_order($order)) {
        return;
    }
    if (rdf_retry_apply_stock_offset($order)) {
        $order->add_order_note('Dotypos: отложенная фискализация выполнена, повторное POS-списание склада компенсировано.');
        return;
    }
    rdf_schedule_retry($order, time() + 15 * MINUTE_IN_SECONDS);
}
add_action(RDF_RETRY_HOOK, 'rdf_retry_fiscalization', 10, 1);

function rdf_fail(WC_Order $order, string $message): void {
    $order->update_meta_data(RDF_STATE_META, 'stock-fallback');
    $order->update_meta_data(RDF_ERROR_META, $message);
    $order->save();
    $order->add_order_note('Dotypos: фискализация не выполнена; сохранено обычное складское списание. ' . $message);
    rdf_log('error', 'Order ' . $order->get_id() . ': ' . $message);
    if (rdf_error_is_retryable($message)) {
        rdf_schedule_retry($order);
    }
}

function rdf_fiscalize_order($order): bool {
    $order = rdf_order($order);
    if (!$order || !rdf_should_fiscalize($order)) {
        return false;
    }
    $state = (string) $order->get_meta(RDF_STATE_META, true);
    if ($state === 'fiscalized' || $state === 'sending' || $state === 'stock-fallback') {
        return $state === 'fiscalized';
    }

    $order->update_meta_data(RDF_STATE_META, 'sending');
    $order->save();

    $context = rdf_access_context();
    if (is_wp_error($context)) {
        rdf_fail($order, $context->get_error_message());
        return false;
    }

    $existing = rdf_existing_order($context, $order);
    if (is_wp_error($existing)) {
        rdf_fail($order, $existing->get_error_message());
        return false;
    }
    if (is_array($existing)) {
        rdf_mark_fiscalized($order, $existing);
        return true;
    }

    $branch_id = rdf_branch_id($context);
    $items = rdf_action_items($order);
    if (is_wp_error($branch_id) || is_wp_error($items)) {
        $error = is_wp_error($branch_id) ? $branch_id : $items;
        rdf_fail($order, $error->get_error_message());
        return false;
    }

    $payload = [
        'action' => 'order/create-issue-pay',
        // Current Dotypos POS devices compare validity with deviceTimestamp,
        // which is expressed in Unix milliseconds.
        'validity' => (time() + 120) * 1000,
        'idempotency-key' => 'wc-' . $order->get_id(),
        'external-id' => 'WC-' . $order->get_id(),
        'note' => 'WooCommerce #' . $order->get_order_number() . ' / ' . $order->get_payment_method_title(),
        'items' => $items,
        'payment-method-id' => rdf_payment_method_id($order),
        'print-type' => 'local',
    ];

    $url = 'https://api.dotykacka.cz/v2/clouds/' . rawurlencode($context['cloud_id'])
        . '/branches/' . $branch_id . '/pos-actions';
    $response = wp_remote_post($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $context['access_token'],
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ],
        'body' => wp_json_encode($payload),
        'timeout' => 30,
    ]);
    if (is_wp_error($response)) {
        rdf_fail($order, $response->get_error_message());
        return false;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    $http_code = wp_remote_retrieve_response_code($response);
    $result_code = (int) ($body['code'] ?? -1);
    if ($http_code !== 200 || $result_code !== 0 || empty($body['order']['paid'])) {
        rdf_fail($order, 'POS Action failed (HTTP ' . $http_code . ', result ' . $result_code . ').');
        return false;
    }

    rdf_mark_fiscalized($order, $body);
    $remote_total = (float) ($body['order']['price-total'] ?? $order->get_total());
    if (abs($remote_total - (float) $order->get_total()) > 0.01) {
        $message = sprintf(
            'Dotypos fiscal total %.2f does not match WooCommerce total %.2f.',
            $remote_total,
            (float) $order->get_total()
        );
        $order->update_meta_data(RDF_ERROR_META, $message);
        $order->save();
        $order->add_order_note('Dotypos: ВНИМАНИЕ — ' . $message);
        rdf_log('error', 'Order ' . $order->get_id() . ': ' . $message);
    }
    return true;
}

function rdf_fiscalize_when_stock_reduces($order): void {
    rdf_fiscalize_order($order);
}
add_action('woocommerce_reduce_order_stock', 'rdf_fiscalize_when_stock_reduces', 5, 1);

function rdf_fiscalize_after_payment($order_id): void {
    rdf_fiscalize_order($order_id);
}
add_action('woocommerce_payment_complete', 'rdf_fiscalize_after_payment', 20, 1);
add_action('woocommerce_order_status_processing', 'rdf_fiscalize_after_payment', 20, 1);
add_action('woocommerce_order_status_completed', 'rdf_fiscalize_after_payment', 20, 1);

/**
 * Keep exactly one stock owner. The live bridge currently records web orders
 * through /warehouses/:id/sales; older repo/runtime variants use the vendor
 * /stockups callback. A successful POS Action already owns that deduction.
 */
function rdf_stock_fallback($order): void {
    if (rdf_order_owns_dotypos_stock_movement($order)) {
        return;
    }

    if (function_exists('rdsb_sync_order_sale_to_dotypos')) {
        rdsb_sync_order_sale_to_dotypos($order);
        return;
    }

    if (class_exists('Dotypos')) {
        $dotypos = Dotypos::instance();
        if ($dotypos && is_callable([$dotypos, 'handle_reduce_order_stock'])) {
            $dotypos->handle_reduce_order_stock($order);
        }
    }
}

function rdf_install_single_stock_owner(): void {
    remove_action('woocommerce_reduce_order_stock', 'rdsb_sync_order_sale_to_dotypos', 10);

    if (class_exists('Dotypos')) {
        $dotypos = Dotypos::instance();
        if ($dotypos) {
            remove_action('woocommerce_reduce_order_stock', [$dotypos, 'handle_reduce_order_stock'], 10);
        }
    }

    add_action('woocommerce_reduce_order_stock', 'rdf_stock_fallback', 10, 1);
}
add_action('plugins_loaded', 'rdf_install_single_stock_owner', 110);
