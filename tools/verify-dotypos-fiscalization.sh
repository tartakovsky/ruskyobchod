#!/bin/sh
set -eu

REMOTE_HOST="${REMOTE_HOST:-u595644545@46.202.156.109}"
REMOTE_PORT="${REMOTE_PORT:-65002}"
REMOTE_ROOT="${REMOTE_ROOT:-/home/u595644545/domains/ruskyobchod.sk/public_html}"

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

if (!function_exists('rdf_supported_payment_method') || !function_exists('rdf_payment_method_id')) {
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

echo "Dotypos fiscalization verification complete.\n";
PHP

scp -P "$REMOTE_PORT" "$tmp_local" "$REMOTE_HOST:$tmp_remote" >/dev/null
ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "php '$tmp_remote' '$REMOTE_ROOT'; status=\$?; rm -f '$tmp_remote'; exit \$status"
