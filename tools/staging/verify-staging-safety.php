<?php
$_SERVER['HTTP_HOST'] = 'staging.ruskyobchod.sk';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';

require $argv[1] . '/wp-load.php';

$response = wp_remote_get('https://example.com/');
if (!is_wp_error($response) || $response->get_error_code() !== 'rusky_staging_external_http_blocked') {
    fwrite(STDERR, "FAIL external HTTP was not blocked before the network\n");
    exit(1);
}

global $wpdb;
$orders = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'shop_order'");
if ($orders !== 0) {
    fwrite(STDERR, "FAIL staging contains {$orders} shop orders\n");
    exit(1);
}

$active_plugins = (array) get_option('active_plugins', []);
if (!in_array('gastronom-stock-fix/gastronom-stock-fix.php', $active_plugins, true)) {
    fwrite(STDERR, "FAIL staging stock-fix was not restored\n");
    exit(1);
}

echo "OK external HTTP blocked before network\n";
echo "OK staging_shop_orders=0\n";
echo "OK stock-fix active after test\n";
