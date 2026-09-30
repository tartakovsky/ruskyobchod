<?php
/** Read-only by default. --persist-staging creates/deletes one isolated fixture. */
if (PHP_SAPI !== 'cli' || empty($argv[1])) {
    exit("Usage: php verify-decimal-stock-status.php WP_ROOT [--persist-staging]\n");
}
$persist = in_array('--persist-staging', $argv, true);
define('DISABLE_WP_CRON', true);
$_SERVER['HTTP_HOST'] = $persist ? 'staging.ruskyobchod.sk' : 'gastronom-bratislava.sk';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTPS'] = 'on';
require rtrim($argv[1], '/') . '/wp-load.php';

function decimal_check($condition, string $label): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL $label\n");
        throw new RuntimeException($label);
    }
    echo "OK $label\n";
}

decimal_check(function_exists('rca_preserve_decimal_stock_before_save'), 'named save handler loaded');
decimal_check(has_action('woocommerce_before_product_object_save', 'rca_preserve_decimal_stock_before_save') === 10, 'save handler registered once at priority 10');

if ($persist) {
    decimal_check(defined('RUSKY_STAGING_MODE') && RUSKY_STAGING_MODE === true
        && wp_parse_url(home_url(), PHP_URL_HOST) === 'staging.ruskyobchod.sk'
        && function_exists('rssg_block_external_http'), 'persistent fixture restricted to isolated staging');
    // Staging has newer WooCommerce with native float validation. Reproduce the
    // production integer-validation bug in this request, on our fixtures only.
    add_action('woocommerce_before_product_object_save', static function ($product) {
        if ($product->get_meta('_decimal_stock_regression') !== 'yes' || !$product->get_manage_stock('edit')) {
            return;
        }
        $in_stock = (int) $product->get_stock_quantity('edit') > absint(get_option('woocommerce_notify_no_stock_amount', 0));
        $product->set_stock_status($in_stock ? 'instock' : ($product->get_backorders('edit') === 'no' ? 'outofstock' : 'onbackorder'));
    }, 9);
    echo "Staging fixtures emulate production integer validation before the candidate hook.\n";
}

// apply_changes() models an existing product: no pending stock_quantity change.
// Neither validate_props() nor this before-save action writes the object to DB.
$cases = [
    ['barbaris', .635, 'yes', '', true, 'no', 0, 'instock'],
    ['cranberry', .82, 'yes', '', true, 'no', 0, 'instock'],
    ['snezhok', .795, 'yes', '', true, 'no', 0, 'instock'],
    ['iris', .5, 'yes', '', true, 'no', 0, 'instock'],
    ['small positive', .01, 'yes', '', true, 'no', 0, 'instock'],
    ['one kg', 1, 'yes', '', true, 'no', 0, 'instock'],
    ['above one kg', 1.175, 'yes', '', true, 'no', 0, 'instock'],
    ['zero', 0, 'yes', '', true, 'no', 0, 'outofstock'],
    ['negative', -.2, 'yes', '', true, 'no', 0, 'outofstock'],
    ['POS floating residue', 4.4408921e-16, 'yes', '', true, 'no', 0, 'outofstock'],
    ['piece product', .635, 'no', '', true, 'no', 0, 'outofstock'],
    ['unconfigured six gram product', .006, '', '', true, 'no', 0, 'outofstock'],
    ['preorder partial portion', .635, 'yes', 'yes', true, 'no', 0, 'outofstock'],
    ['preorder whole portion', 1, 'yes', 'yes', true, 'no', 0, 'instock'],
    ['unmanaged stock', .635, 'yes', '', false, 'no', 0, 'outofstock'],
    ['nonzero stock threshold', .635, 'yes', '', true, 'no', 1, 'outofstock'],
    ['positive with backorders allowed', .635, 'yes', '', true, 'yes', 0, 'instock'],
    ['zero with backorders allowed', 0, 'yes', '', true, 'yes', 0, 'onbackorder'],
];
foreach ($cases as [$label, $qty, $weighted, $preorder, $manage, $backorders, $threshold, $expected]) {
    $threshold_filter = static function () use ($threshold) { return (string) $threshold; };
    add_filter('pre_option_woocommerce_notify_no_stock_amount', $threshold_filter);
    try {
        $product = new WC_Product_Simple();
        $product->set_manage_stock($manage);
        $product->set_stock_quantity($qty);
        $product->set_backorders($backorders);
        $product->set_stock_status('outofstock');
        $product->update_meta_data('_gls_weighted', $weighted);
        $product->update_meta_data('_gastronom_weight_preorder', $preorder);
        $product->update_meta_data('_decimal_stock_regression', 'yes');
        $product->apply_changes();
        $product->set_regular_price('1.23');
        $product->validate_props();
        do_action('woocommerce_before_product_object_save', $product, null);
        decimal_check($product->get_stock_status('edit') === $expected, $label . ' -> ' . $expected);
        if ($manage) {
            decimal_check(abs((float) $product->get_stock_quantity('edit') - $qty) < 1e-12, $label . ' quantity unchanged');
        }
    } finally {
        remove_filter('pre_option_woocommerce_notify_no_stock_amount', $threshold_filter);
    }
}

if (!$persist) {
    exit("Read-only decimal stock checks passed. No products saved.\n");
}
function decimal_persisted_check(int $id, float $qty, string $status, string $label): void {
    global $wpdb;
    clean_post_cache($id);
    $product = wc_get_product($id);
    $lookup = $wpdb->get_row($wpdb->prepare("SELECT stock_quantity, stock_status FROM {$wpdb->wc_product_meta_lookup} WHERE product_id = %d", $id));
    $terms = wp_get_object_terms($id, 'product_visibility', ['fields' => 'slugs']);
    decimal_check(abs((float) $product->get_stock_quantity() - $qty) < 1e-9
        && $product->get_stock_status() === $status
        && $lookup && abs((float) $lookup->stock_quantity - $qty) < 1e-9
        && $lookup->stock_status === $status
        && in_array('outofstock', $terms, true) === ($status === 'outofstock'), $label . ': object, lookup and visibility agree');
}

$fixture_id = 0;
try {
    $fixture = new WC_Product_Simple();
    $fixture->set_name('Decimal stock regression fixture ' . gmdate('YmdHis'));
    $fixture->set_status('draft');
    $fixture->set_catalog_visibility('hidden');
    $fixture->set_regular_price('1');
    $fixture->set_manage_stock(true);
    $fixture->set_backorders('no');
    $fixture->set_stock_quantity(.635);
    $fixture->update_meta_data('_gls_weighted', 'yes');
    $fixture->update_meta_data('_decimal_stock_regression', 'yes');
    $fixture_id = $fixture->save();
    decimal_persisted_check($fixture_id, .635, 'instock', 'initial fractional save');
    $fixture = wc_get_product($fixture_id);
    $fixture->set_regular_price('1.01');
    $fixture->save();
    decimal_persisted_check($fixture_id, .635, 'instock', 'price-only save');
    wc_update_product_stock_status($fixture_id, 'outofstock');
    decimal_persisted_check($fixture_id, .635, 'instock', 'status-only save');
    foreach ([0.0, .82, -.2, 1.175, .5] as $qty) {
        wc_update_product_stock($fixture_id, $qty, 'set');
        decimal_persisted_check($fixture_id, $qty, $qty > 0 ? 'instock' : 'outofstock', 'stock transition ' . $qty);
    }
    $fixture = wc_get_product($fixture_id);
    $fixture->save();
    decimal_persisted_check($fixture_id, .5, 'instock', 'unchanged repeated save');
} finally {
    if ($fixture_id) {
        wp_delete_post($fixture_id, true);
        decimal_check(get_post($fixture_id) === null, 'staging fixture deleted');
    }
}
echo "Decimal stock persistence checks passed.\n";
