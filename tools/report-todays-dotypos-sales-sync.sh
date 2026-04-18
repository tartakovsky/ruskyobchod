#!/bin/sh
set -eu

REMOTE_HOST="${REMOTE_HOST:-u595644545@46.202.156.109}"
REMOTE_PORT="${REMOTE_PORT:-65002}"
REMOTE_ROOT="${REMOTE_ROOT:-/home/u595644545/domains/ruskyobchod.sk/public_html}"
REPORT_DATE="${1:-}"

tmp_local="$(mktemp)"
tmp_remote="/tmp/rusky-dotypos-sales-sync-$$.php"
trap 'rm -f "$tmp_local"' EXIT

cat >"$tmp_local" <<'PHP'
<?php
$_SERVER['HTTP_HOST'] = 'ruskyobchod.sk';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_NAME'] = 'ruskyobchod.sk';
$_SERVER['SERVER_PORT'] = '443';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_URI'] = '/?report_todays_dotypos_sales_sync=1';

require $argv[1] . '/wp-load.php';

if (!class_exists('Dotypos')) {
    fwrite(STDERR, "FAIL Dotypos class missing\n");
    exit(1);
}

$requestedDate = isset($argv[2]) ? trim((string) $argv[2]) : '';
$tz = new DateTimeZone('Europe/Bratislava');

if ($requestedDate !== '') {
    $start = new DateTimeImmutable($requestedDate . ' 00:00:00', $tz);
    if ($start->format('Y-m-d') !== $requestedDate) {
        fwrite(STDERR, "FAIL invalid date, expected YYYY-MM-DD\n");
        exit(1);
    }
} else {
    $start = new DateTimeImmutable('today', $tz);
}

$end = $start->modify('+1 day');
$startMs = $start->setTimezone(new DateTimeZone('UTC'))->getTimestamp() * 1000;
$endMs = $end->setTimezone(new DateTimeZone('UTC'))->getTimestamp() * 1000;

$settings = get_option(Dotypos::$keys['settings'], []);
$warehouseId = (string) ($settings['dotypos']['warehouseId'] ?? '');
$dotypos = Dotypos::instance();

if (!$dotypos || empty($dotypos->dotyposService) || $warehouseId === '') {
    fwrite(STDERR, "FAIL Dotypos service/settings unavailable\n");
    exit(1);
}

$service = $dotypos->dotyposService;
$serviceRef = new ReflectionObject($service);

$tokenProp = $serviceRef->getProperty('token');
$tokenProp->setAccessible(true);
$refreshToken = (string) $tokenProp->getValue($service);

$cloudProp = $serviceRef->getProperty('cloudId');
$cloudProp->setAccessible(true);
$cloudId = (string) $cloudProp->getValue($service);

$signin = wp_remote_post('https://api.dotykacka.cz/v2/signin/token', [
    'headers' => [
        'Authorization' => 'User ' . $refreshToken,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ],
    'body' => wp_json_encode(['_cloudId' => $cloudId]),
    'timeout' => 20,
]);

if (is_wp_error($signin)) {
    fwrite(STDERR, 'FAIL sign-in request failed: ' . $signin->get_error_message() . "\n");
    exit(1);
}

if (wp_remote_retrieve_response_code($signin) !== 201) {
    fwrite(STDERR, 'FAIL sign-in request returned ' . wp_remote_retrieve_response_code($signin) . "\n");
    exit(1);
}

$signinBody = json_decode(wp_remote_retrieve_body($signin), true);
$accessToken = (string) ($signinBody['accessToken'] ?? '');
if ($accessToken === '') {
    fwrite(STDERR, "FAIL access token missing\n");
    exit(1);
}

$filter = rawurlencode("stockDeduct|eq|true;completed|gteq|{$startMs};completed|lt|{$endMs}");
$url = "https://api.dotykacka.cz/v2/clouds/{$cloudId}/order-items?sort=-completed&limit=100&filter={$filter}";
$response = wp_remote_get($url, [
    'headers' => [
        'Authorization' => 'Bearer ' . $accessToken,
        'Accept' => 'application/json',
    ],
    'timeout' => 20,
]);

if (is_wp_error($response)) {
    fwrite(STDERR, 'FAIL order-items request failed: ' . $response->get_error_message() . "\n");
    exit(1);
}

if (wp_remote_retrieve_response_code($response) !== 200) {
    fwrite(STDERR, 'FAIL order-items request returned ' . wp_remote_retrieve_response_code($response) . "\n");
    fwrite(STDERR, 'FAIL order-items url ' . $url . "\n");
    fwrite(STDERR, 'FAIL order-items body ' . wp_remote_retrieve_body($response) . "\n");
    exit(1);
}

$payload = json_decode(wp_remote_retrieve_body($response), true);
$items = $payload['data'] ?? [];
$metaKey = Dotypos::$keys['product']['field-id'];
$groups = [];

foreach ($items as $item) {
    $dotyposId = (string) ($item['_productId'] ?? '');
    if ($dotyposId === '') {
        continue;
    }

    if (!isset($groups[$dotyposId])) {
        $groups[$dotyposId] = [
            'dotypos_id' => $dotyposId,
            'name' => (string) ($item['name'] ?? ''),
            'unit' => (string) ($item['unit'] ?? ''),
            'sold_qty' => 0.0,
            'lines' => 0,
            'latest_completed' => (string) ($item['completed'] ?? ''),
        ];
    }

    $groups[$dotyposId]['sold_qty'] += (float) ($item['quantity'] ?? 0);
    $groups[$dotyposId]['lines']++;
    if (($item['completed'] ?? '') > $groups[$dotyposId]['latest_completed']) {
        $groups[$dotyposId]['latest_completed'] = (string) $item['completed'];
    }
}

uasort($groups, static function (array $left, array $right): int {
    return strcmp($right['latest_completed'], $left['latest_completed']);
});

$matched = 0;
$mismatched = 0;

foreach ($groups as &$group) {
    $postIds = get_posts([
        'post_type' => ['product', 'product_variation'],
        'post_status' => 'any',
        'meta_key' => $metaKey,
        'meta_value' => $group['dotypos_id'],
        'numberposts' => 1,
        'fields' => 'ids',
    ]);

    $wooId = $postIds ? (int) $postIds[0] : 0;
    $group['woo_id'] = $wooId;
    $group['woo_name'] = $wooId ? get_the_title($wooId) : null;
    $group['is_weight_preorder'] = $wooId > 0 && get_post_meta($wooId, '_gastronom_weight_preorder', true) === 'yes';
    $group['expected_source'] = $group['is_weight_preorder'] ? '_gastronom_cash_stock_kg' : '_stock';
    $group['woo_quantity'] = $wooId > 0 ? (float) get_post_meta($wooId, $group['expected_source'], true) : null;

    $remote = $service->getProductOnWarehouse($warehouseId, $group['dotypos_id']);
    $group['dotypos_quantity'] = isset($remote['stockQuantityStatus']) ? (float) $remote['stockQuantityStatus'] : null;
    $group['sync_ok'] = is_numeric($group['woo_quantity'])
        && is_numeric($group['dotypos_quantity'])
        && abs((float) $group['woo_quantity'] - (float) $group['dotypos_quantity']) < 0.000001;

    if ($group['sync_ok']) {
        $matched++;
    } else {
        $mismatched++;
    }
}
unset($group);

echo wp_json_encode([
    'date' => $start->format('Y-m-d'),
    'timezone' => 'Europe/Bratislava',
    'unique_products' => count($groups),
    'matched_products' => $matched,
    'mismatched_products' => $mismatched,
    'products' => array_values($groups),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;

if ($mismatched > 0) {
    fwrite(STDERR, "FAIL some sold products do not match current Woo/Dotypos quantity\n");
    exit(1);
}
PHP

scp -P "$REMOTE_PORT" "$tmp_local" "$REMOTE_HOST:$tmp_remote" >/dev/null
ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "php '$tmp_remote' '$REMOTE_ROOT' '$REPORT_DATE'; rm -f '$tmp_remote'"
