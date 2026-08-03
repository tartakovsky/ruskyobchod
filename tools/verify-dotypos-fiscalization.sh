#!/bin/sh
set -eu

REMOTE_HOST="${REMOTE_HOST:-u595644545@46.202.156.109}"
REMOTE_PORT="${REMOTE_PORT:-65002}"
REMOTE_ROOT="${REMOTE_ROOT:-/home/u595644545/domains/ruskyobchod.sk/public_html}"
AUDIT_FROM="${AUDIT_FROM:-2026-07-01}"

tmp_local="$(mktemp)"
tmp_remote="/tmp/rusky-dotypos-fiscalization-verify-$$.php"
trap 'rm -f "$tmp_local"' EXIT

cat >"$tmp_local" <<'PHP'
<?php
$_SERVER['HTTP_HOST'] = 'ruskyobchod.sk';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_NAME'] = 'ruskyobchod.sk';
$_SERVER['SERVER_PORT'] = '443';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_URI'] = '/?verify_dotypos_fiscalization=1';
require $argv[1] . '/wp-load.php';

if (
    !function_exists('rdf_supported_payment_method')
    || !function_exists('rdf_payment_method_id')
    || !function_exists('rdf_error_is_retryable')
    || !function_exists('rdf_retry_fiscalization')
) {
    fwrite(STDERR, "FAIL fiscalization runtime missing\n");
    exit(1);
}

$checks = [
    'WooPayments is fiscalized' => ['woocommerce_payments', true, 900000019],
    'Stripe is fiscalized' => ['stripe', true, 900000019],
    'Stripe variant is fiscalized' => ['woocommerce_gateway_stripe', true, 900000019],
    'COD is fiscalized' => ['cod', true, 900000009],
    'bank transfer is not auto-fiscalized' => ['bacs', false, 900000019],
];

foreach ($checks as $label => [$method, $expected_supported, $expected_payment_id]) {
    $order = new WC_Order();
    $order->set_payment_method($method);
    $supported = rdf_supported_payment_method($order);
    $payment_id = rdf_payment_method_id($order);
    if ($supported !== $expected_supported || $payment_id !== $expected_payment_id) {
        fwrite(STDERR, "FAIL {$label}\n");
        exit(1);
    }
    echo "OK   {$label}\n";
}

if (
    !rdf_error_is_retryable('POS Action failed (HTTP 404, result -1).')
    || !rdf_error_is_retryable('POS Action failed (HTTP 503).')
    || rdf_error_is_retryable('Dotypos device is not paired.')
) {
    fwrite(STDERR, "FAIL retryable error classification\n");
    exit(1);
}
echo "OK   transient POS errors are retryable\n";

if (!has_action(RDF_RETRY_HOOK, 'rdf_retry_fiscalization')) {
    fwrite(STDERR, "FAIL retry action hook is not registered\n");
    exit(1);
}
echo "OK   retry action hook is registered\n";

if (!defined('RDF_MAX_RETRY_ATTEMPTS') || RDF_MAX_RETRY_ATTEMPTS !== 24) {
    fwrite(STDERR, "FAIL retry attempt limit\n");
    exit(1);
}
echo "OK   fiscal retry chain is bounded to 24 attempts\n";

$cod_flow = new WC_Order();
$cod_flow->set_payment_method('cod');
$cod_flow->set_status('processing');
if (rdf_should_fiscalize($cod_flow)) {
    fwrite(STDERR, "FAIL processing COD order would fiscalize before editing\n");
    exit(1);
}
$cod_flow->update_meta_data(RDF_STATE_META, RDF_COD_DEFERRED_STATE);
if (!function_exists('rcoe_is_deferred_cod_order') || !rcoe_is_deferred_cod_order($cod_flow)) {
    fwrite(STDERR, "FAIL processing COD order is not editable in deferred state\n");
    exit(1);
}
if (
    !has_filter('wc_order_is_editable', 'rcoe_allow_deferred_cod_editing')
    || !has_action('woocommerce_saved_order_items', 'rcoe_sync_saved_quantity_changes')
    || !has_action('woocommerce_ajax_order_items_added', 'rcoe_sync_added_items')
    || !has_action('woocommerce_ajax_order_items_removed', 'rcoe_sync_removed_item')
) {
    fwrite(STDERR, "FAIL COD editing stock synchronization hooks\n");
    exit(1);
}
$cod_flow->set_status('completed');
if (!rdf_should_fiscalize($cod_flow) || rcoe_is_deferred_cod_order($cod_flow)) {
    fwrite(STDERR, "FAIL completed COD finalization state\n");
    exit(1);
}
echo "OK   COD stays editable before completion and fiscalizes only when completed\n";

$rounding_order = wc_get_order(11398);
if ($rounding_order instanceof WC_Order) {
    $pos_total = 0.0;
    foreach (rdf_action_items($rounding_order) as $action_item) {
        $pos_total += round((float) $action_item['manual-price'] * (float) $action_item['qty'], 2);
    }
    if (abs($pos_total - (float) $rounding_order->get_total()) > 0.0001) {
        fwrite(STDERR, "FAIL POS line rounding total {$pos_total} differs from Woo total {$rounding_order->get_total()}\n");
        exit(1);
    }
    echo "OK   POS line rounding matches order #11398 total\n";
}

echo "Dotypos fiscalization verification complete.\n";

$audit_from = isset($argv[2]) ? (string) $argv[2] : '2026-07-01';
$context = rdf_access_context();
if (is_wp_error($context)) {
    fwrite(STDERR, "FAIL Dotypos audit authentication: {$context->get_error_message()}\n");
    exit(1);
}

$orders = wc_get_orders([
    'limit' => -1,
    'status' => ['processing', 'completed', 'on-hold'],
    'date_created' => '>=' . $audit_from . ' 00:00:00',
    'orderby' => 'date',
    'order' => 'ASC',
]);
$eligible = 0;
$verified = 0;
$missing = [];
foreach ($orders as $order) {
    if (!rdf_supported_payment_method($order)) {
        continue;
    }
    if (!rdf_should_fiscalize($order) && $order->get_meta(RDF_STATE_META, true) !== 'fiscalized') {
        continue;
    }
    if ($order->get_payment_method() !== 'cod' && !$order->is_paid()) {
        continue;
    }

    $eligible++;
    $remote = rdf_existing_order($context, $order);
    $ok = is_array($remote)
        && !empty($remote['paid'])
        && empty($remote['canceledDate'])
        && $order->get_meta(RDF_STATE_META, true) === 'fiscalized';
    if ($ok) {
        $verified++;
        continue;
    }
    $missing[] = $order->get_id();
}

echo "Fiscal sales audit from {$audit_from}: eligible={$eligible} verified={$verified} missing=" . count($missing) . "\n";
if ($missing) {
    fwrite(STDERR, 'FAIL missing fiscal receipts for WooCommerce orders: ' . implode(',', $missing) . "\n");
    exit(1);
}
PHP

scp -P "$REMOTE_PORT" "$tmp_local" "$REMOTE_HOST:$tmp_remote" >/dev/null
ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "php '$tmp_remote' '$REMOTE_ROOT' '$AUDIT_FROM'; status=\$?; rm -f '$tmp_remote'; exit \$status"
