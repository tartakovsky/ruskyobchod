<?php
/** Explicit, bounded repair. Defaults to a read-only plan; never changes quantity. */
if (PHP_SAPI !== 'cli' || count($argv) < 3) {
    exit("Usage: php repair-decimal-stock-status.php WP_ROOT IDS_CSV [--apply BACKUP_JSON]\n");
}
$apply = ($argv[3] ?? '') === '--apply';
$backup = $argv[4] ?? '';
$ids = array_values(array_unique(array_filter(array_map('absintval_for_repair', explode(',', $argv[2])))));
function absintval_for_repair($value): int { return abs((int) $value); }
if (!$ids || count($ids) > 20 || ($apply && (!$backup || file_exists($backup)))) {
    exit("Refusing empty/large scope or missing/existing backup path.\n");
}
define('DISABLE_WP_CRON', true);
$_SERVER['HTTP_HOST'] = 'gastronom-bratislava.sk';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTPS'] = 'on';
require rtrim($argv[1], '/') . '/wp-load.php';
if ($apply && (!function_exists('rca_preserve_decimal_stock_before_save')
    || (float) get_option('woocommerce_notify_no_stock_amount', 0) !== 0.0
    || strpos(realpath(dirname($backup)) . '/', realpath(ABSPATH) . '/') === 0)) {
    exit("Refusing repair without handler/zero threshold or with backup inside web root.\n");
}

function decimal_repair_snapshot(int $id): array {
    global $wpdb;
    $p = wc_get_product($id);
    if (!$p) { throw new RuntimeException('Missing product ' . $id); }
    return [
        'id' => $id, 'name' => $p->get_name(), 'type' => $p->get_type(),
        'post_status' => $p->get_status(), 'quantity' => $p->get_stock_quantity('edit'),
        'stock_status' => $p->get_stock_status('edit'), 'price' => $p->get_price('edit'),
        'image_id' => $p->get_image_id(), 'manage_stock' => $p->get_manage_stock('edit'),
        'backorders' => $p->get_backorders('edit'), 'visibility' => $p->get_catalog_visibility('edit'),
        'weighted' => $p->get_meta('_gls_weighted'), 'preorder' => $p->get_meta('_gastronom_weight_preorder'),
        'dotypos_id' => $p->get_meta('dotypos_product_id'),
        'terms' => wp_get_object_terms($id, 'product_visibility', ['fields' => 'slugs']),
        'lookup' => $wpdb->get_row($wpdb->prepare("SELECT stock_quantity,stock_status FROM {$wpdb->wc_product_meta_lookup} WHERE product_id=%d", $id), ARRAY_A),
        'url' => get_permalink($id), 'meta_backup' => get_post_meta($id),
    ];
}
$before = array_map('decimal_repair_snapshot', $ids);
foreach ($before as $row) {
    if ($row['type'] !== 'simple' || $row['post_status'] !== 'publish'
        || $row['weighted'] !== 'yes' || $row['preorder'] === 'yes' || $row['manage_stock'] !== true
        || $row['quantity'] <= .000001 || $row['quantity'] >= 1
        || !in_array($row['stock_status'], ['outofstock', 'instock'], true)) {
        throw new RuntimeException('Ineligible product: ' . $row['id']);
    }
}
if ($apply) {
    $encoded = wp_json_encode(['time' => gmdate('c'), 'products' => $before], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $handle = fopen($backup, 'x');
    if (!$handle || fwrite($handle, $encoded) !== strlen($encoded)) {
        throw new RuntimeException('Could not write complete backup');
    }
    fclose($handle);
    chmod($backup, 0600);
    // This repair must not send stock, product or notification writes elsewhere.
    $http_attempts = 0;
    add_filter('pre_http_request', static function () use (&$http_attempts) {
        $http_attempts++;
        return new WP_Error('decimal_repair_external_blocked', 'External HTTP disabled during stock-status repair');
    }, PHP_INT_MAX);
    add_filter('pre_wp_mail', '__return_true', PHP_INT_MAX);
    foreach ($before as $row) {
        $p = wc_get_product($row['id']);
        if ((float) $p->get_stock_quantity('edit') !== (float) $row['quantity']) {
            throw new RuntimeException('Stock changed concurrently: ' . $row['id']);
        }
        if ($p->get_stock_status('edit') !== 'instock') {
            wc_update_product_stock_status($row['id'], 'instock');
        }
        wc_delete_product_transients($row['id']);
        clean_post_cache($row['id']);
        $after = decimal_repair_snapshot($row['id']);
        foreach (['quantity', 'price', 'image_id', 'post_status', 'visibility', 'backorders'] as $key) {
            if ($row[$key] !== $after[$key]) {
                throw new RuntimeException('Unexpected change: ' . $row['id'] . ' ' . $key);
            }
        }
        if ($after['stock_status'] !== 'instock' || $after['lookup']['stock_status'] !== 'instock'
            || in_array('outofstock', $after['terms'], true)) {
            throw new RuntimeException('Status/lookup/visibility did not converge: ' . $row['id']);
        }
    }
    if ($http_attempts !== 0) {
        throw new RuntimeException('Unexpected blocked external HTTP attempts: ' . $http_attempts);
    }
}
$result = array_map('decimal_repair_snapshot', $ids);
foreach ($result as &$row) { unset($row['meta_backup']); }
unset($row);
echo wp_json_encode(['time' => gmdate('c'), 'applied' => $apply, 'external_http_attempts' => $http_attempts ?? null, 'products' => $result], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
