#!/bin/sh
set -eu

REMOTE_HOST="${REMOTE_HOST:-u595644545@46.202.156.109}"
REMOTE_PORT="${REMOTE_PORT:-65002}"
REMOTE_ROOT="${REMOTE_ROOT:-/home/u595644545/domains/ruskyobchod.sk/public_html}"

tmp_local="$(mktemp)"
tmp_remote="/tmp/rusky-dotypos-readonly-verify-$$.php"
trap 'rm -f "$tmp_local"' EXIT

cat >"$tmp_local" <<'PHP'
<?php
$_SERVER['HTTP_HOST'] = 'ruskyobchod.sk';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_NAME'] = 'ruskyobchod.sk';
$_SERVER['SERVER_PORT'] = '443';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_URI'] = '/?verify_dotypos_readonly=1';

require $argv[1] . '/wp-load.php';

if (!class_exists('Dotypos')) {
    fwrite(STDERR, "FAIL Dotypos class missing\n");
    exit(1);
}

$settings = get_option(Dotypos::$keys['settings'], []);
$warehouseId = $settings['dotypos']['warehouseId'] ?? null;
$syncToDotypos = !empty($settings['product']['movement']['syncToDotypos']);
$dotypos = Dotypos::instance();
$servicePresent = $dotypos && !empty($dotypos->dotyposService);

if (!$servicePresent) {
    fwrite(STDERR, "FAIL Dotypos service missing\n");
    exit(1);
}

if (!$syncToDotypos) {
    fwrite(STDERR, "FAIL syncToDotypos disabled\n");
    exit(1);
}

if (empty($warehouseId)) {
    fwrite(STDERR, "FAIL warehouseId missing\n");
    exit(1);
}

$products = [
    10781 => 'Slanina "Mercur"',
    10617 => 'Sleď . Vedro',
    10310 => 'Ryba Makrela Údeného Chladenia',
];

foreach ($products as $productId => $expectedName) {
    $product = wc_get_product($productId);
    if (!$product instanceof WC_Product) {
        fwrite(STDERR, "FAIL product $productId missing\n");
        exit(1);
    }

    $dotyposId = $product->get_meta(Dotypos::$keys['product']['field-id']);
    if (empty($dotyposId)) {
        fwrite(STDERR, "FAIL product $productId dotypos_id missing\n");
        exit(1);
    }

    $localCash = (string) get_post_meta($productId, '_gastronom_cash_stock_kg', true);
    $remote = $dotypos->dotyposService->getProductOnWarehouse($warehouseId, $dotyposId);
    $remoteQty = isset($remote['stockQuantityStatus']) ? (string) $remote['stockQuantityStatus'] : '';

    if ($remoteQty === '') {
        fwrite(STDERR, "FAIL product $productId remote stock missing\n");
        exit(1);
    }

    if (abs((float) $remoteQty - (float) $localCash) > 0.000001) {
        fwrite(STDERR, "FAIL product $productId local cash $localCash != remote $remoteQty\n");
        exit(1);
    }

    echo "OK   product $productId mapping and remote stock match ($remoteQty)\n";
}

echo "Dotypos readonly verification complete.\n";
PHP

scp -P "$REMOTE_PORT" "$tmp_local" "$REMOTE_HOST:$tmp_remote" >/dev/null
ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "php '$tmp_remote' '$REMOTE_ROOT'; rm -f '$tmp_remote'"
