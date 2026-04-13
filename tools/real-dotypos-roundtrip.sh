#!/bin/sh
set -eu

REMOTE_HOST="${REMOTE_HOST:-u595644545@46.202.156.109}"
REMOTE_PORT="${REMOTE_PORT:-65002}"
REMOTE_ROOT="${REMOTE_ROOT:-/home/u595644545/domains/ruskyobchod.sk/public_html}"

PRODUCT_ID="${1:-10617}"
DELTA="${2:-0.01}"

if [ "${REALLY_MUTATE_DOTYPOS:-0}" != "1" ]; then
    echo "Refusing to mutate Dotypos without REALLY_MUTATE_DOTYPOS=1" >&2
    echo "Plan: .plans/2026-04-13-real-dotypos-roundtrip-mini-plan.md" >&2
    exit 2
fi

tmp_local="$(mktemp)"
tmp_remote="/tmp/rusky-real-dotypos-roundtrip-$$.php"
trap 'rm -f "$tmp_local"' EXIT

cat >"$tmp_local" <<'PHP'
<?php
$_SERVER['HTTP_HOST'] = 'ruskyobchod.sk';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_NAME'] = 'ruskyobchod.sk';
$_SERVER['SERVER_PORT'] = '443';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_URI'] = '/?real_dotypos_roundtrip=1';

require $argv[1] . '/wp-load.php';

if (!class_exists('Dotypos')) {
    fwrite(STDERR, "FAIL Dotypos missing\n");
    exit(1);
}

$product_id = (int) ($argv[2] ?? 10617);
$delta = (float) ($argv[3] ?? 0.01);
if ($delta <= 0) {
    fwrite(STDERR, "FAIL delta must be > 0\n");
    exit(1);
}

$product = wc_get_product($product_id);
if (!$product instanceof WC_Product) {
    fwrite(STDERR, "FAIL product missing\n");
    exit(1);
}

$settings = get_option(Dotypos::$keys['settings'], []);
$warehouse_id = $settings['dotypos']['warehouseId'] ?? null;
$sync_to_dotypos = !empty($settings['product']['movement']['syncToDotypos']);
$dotypos = Dotypos::instance();
if (!$dotypos || empty($dotypos->dotyposService) || !$sync_to_dotypos || empty($warehouse_id)) {
    fwrite(STDERR, "FAIL dotypos service/settings unavailable\n");
    exit(1);
}

$dotypos_id = $product->get_meta(Dotypos::$keys['product']['field-id']);
if (empty($dotypos_id)) {
    fwrite(STDERR, "FAIL dotypos id missing\n");
    exit(1);
}

$service = $dotypos->dotyposService;
$before = $service->getProductOnWarehouse($warehouse_id, $dotypos_id);
$before_qty = (float) ($before['stockQuantityStatus'] ?? 0);

$invoice = 'RUSKY-ROUNDTRIP-' . $product_id . '-' . time();
$service->updateProductStock($warehouse_id, $dotypos_id, -$delta, $invoice . '-OUT');
$after_out = $service->getProductOnWarehouse($warehouse_id, $dotypos_id);
$after_out_qty = (float) ($after_out['stockQuantityStatus'] ?? 0);

$service->updateProductStock($warehouse_id, $dotypos_id, $delta, $invoice . '-BACK');
$after_back = $service->getProductOnWarehouse($warehouse_id, $dotypos_id);
$after_back_qty = (float) ($after_back['stockQuantityStatus'] ?? 0);

$result = [
    'product_id' => $product_id,
    'dotypos_id' => $dotypos_id,
    'warehouse_id' => $warehouse_id,
    'delta' => $delta,
    'before_qty' => $before_qty,
    'after_out_qty' => $after_out_qty,
    'after_back_qty' => $after_back_qty,
    'restored' => abs($after_back_qty - $before_qty) < 0.000001,
    'local_cash_stock_kg' => (float) get_post_meta($product_id, '_gastronom_cash_stock_kg', true),
    'local_stock' => (float) get_post_meta($product_id, '_stock', true),
];

echo wp_json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;

if (!$result['restored']) {
    fwrite(STDERR, "FAIL roundtrip did not restore exact quantity\n");
    exit(1);
}
PHP

scp -P "$REMOTE_PORT" "$tmp_local" "$REMOTE_HOST:$tmp_remote" >/dev/null
ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "php '$tmp_remote' '$REMOTE_ROOT' '$PRODUCT_ID' '$DELTA'; rm -f '$tmp_remote'"
