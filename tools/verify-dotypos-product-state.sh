#!/bin/sh
set -eu

REMOTE_HOST="${REMOTE_HOST:-u595644545@46.202.156.109}"
REMOTE_PORT="${REMOTE_PORT:-65002}"
REMOTE_ROOT="${REMOTE_ROOT:-/home/u595644545/domains/ruskyobchod.sk/public_html}"

PRODUCT_ID="${1:-10617}"

tmp_local="$(mktemp)"
tmp_remote="/tmp/rusky-dotypos-product-state-$$.php"
trap 'rm -f "$tmp_local"' EXIT

cat >"$tmp_local" <<'PHP'
<?php
$_SERVER['HTTP_HOST'] = 'ruskyobchod.sk';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_NAME'] = 'ruskyobchod.sk';
$_SERVER['SERVER_PORT'] = '443';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_URI'] = '/?verify_dotypos_product_state=1';

require $argv[1] . '/wp-load.php';

if (!class_exists('Dotypos')) {
    fwrite(STDERR, "FAIL Dotypos class missing\n");
    exit(1);
}

$product_id = (int) ($argv[2] ?? 10617);
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

$is_weight_preorder = get_post_meta($product_id, '_gastronom_weight_preorder', true) === 'yes';
$local_cash = (float) get_post_meta($product_id, '_gastronom_cash_stock_kg', true);
$local_stock = (float) get_post_meta($product_id, '_stock', true);
$remote = $dotypos->dotyposService->getProductOnWarehouse($warehouse_id, $dotypos_id);
$remote_qty = (float) ($remote['stockQuantityStatus'] ?? -1);

if ($remote_qty < 0) {
    fwrite(STDERR, "FAIL remote stock missing\n");
    exit(1);
}

$expected_source = $is_weight_preorder ? 'local_cash_stock_kg' : 'local_stock';
$expected_qty = $is_weight_preorder ? $local_cash : $local_stock;
$match = abs($remote_qty - $expected_qty) < 0.000001;

echo wp_json_encode([
    'product_id' => $product_id,
    'product_name' => $product->get_name(),
    'dotypos_id' => $dotypos_id,
    'warehouse_id' => $warehouse_id,
    'is_weight_preorder' => $is_weight_preorder,
    'expected_source' => $expected_source,
    'expected_quantity' => $expected_qty,
    'local_stock' => $local_stock,
    'local_cash_stock_kg' => $local_cash,
    'remote_stock_quantity_status' => $remote_qty,
    'remote_matches_expected_quantity' => $match,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;

if (!$match) {
    fwrite(STDERR, "FAIL remote stock does not match expected quantity source\n");
    exit(1);
}
PHP

scp -P "$REMOTE_PORT" "$tmp_local" "$REMOTE_HOST:$tmp_remote" >/dev/null
ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "php '$tmp_remote' '$REMOTE_ROOT' '$PRODUCT_ID'; rm -f '$tmp_remote'"
