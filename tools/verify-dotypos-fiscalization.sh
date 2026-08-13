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
$cod_flow->set_date_created('2026-08-13 12:00:00');
if (!rdf_should_fiscalize($cod_flow)) {
    fwrite(STDERR, "FAIL processing COD order would not fiscalize immediately\n");
    exit(1);
}
$cod_flow->set_status('completed');
if (!rdf_should_fiscalize($cod_flow)) {
    fwrite(STDERR, "FAIL completed COD order is not fiscalizable\n");
    exit(1);
}
echo "OK   COD fiscalizes immediately while completion remains a fulfillment status\n";

$quarter_cod = new WC_Order();
$quarter_cod->set_payment_method('cod');
$quarter_cod->set_status('processing');
$quarter_cod->set_date_created('2026-09-26 12:00:00');
if (rdf_cod_quarter_fiscalization_date($quarter_cod) !== null || !rdf_should_fiscalize($quarter_cod)) {
    fwrite(STDERR, "FAIL COD order five calendar days from next quarter would be deferred\n");
    exit(1);
}
$quarter_cod->set_date_created('2026-09-27 12:00:00');
$quarter_date = rdf_cod_quarter_fiscalization_date($quarter_cod);
if (!$quarter_date || $quarter_date->format('Y-m-d H:i') !== '2026-10-01 00:05' || rdf_should_fiscalize($quarter_cod)) {
    fwrite(STDERR, "FAIL COD order in final four quarter days is not deferred to next month\n");
    exit(1);
}
if (!has_action(RDF_QUARTER_COD_HOOK, 'rdf_fiscalize_quarter_cod_order')) {
    fwrite(STDERR, "FAIL quarter-boundary COD fiscalization hook is not registered\n");
    exit(1);
}
echo "OK   final four quarter days defer COD fiscalization to next month's first day\n";

foreach ([
    ['2026-03-27 12:00:00', null],
    ['2026-03-28 12:00:00', '2026-04-01 00:05'],
    ['2026-06-26 12:00:00', null],
    ['2026-06-27 12:00:00', '2026-07-01 00:05'],
    ['2026-09-26 12:00:00', null],
    ['2026-09-27 12:00:00', '2026-10-01 00:05'],
    ['2026-12-27 12:00:00', null],
    ['2026-12-28 12:00:00', '2027-01-01 00:05'],
] as [$created, $expected]) {
    $quarter_cod->set_date_created($created);
    $actual = rdf_cod_quarter_fiscalization_date($quarter_cod);
    $actual_value = $actual ? $actual->format('Y-m-d H:i') : null;
    if ($actual_value !== $expected) {
        fwrite(STDERR, "FAIL quarter boundary {$created}: expected " . ($expected ?? 'immediate') . ", got " . ($actual_value ?? 'immediate') . "\n");
        exit(1);
    }
}
echo "OK   all four quarter boundaries use the correct four-day window\n";

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
