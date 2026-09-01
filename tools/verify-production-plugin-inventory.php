<?php
declare(strict_types=1);

$root = $argv[1] ?? '';
if ($root === '') {
    fwrite(STDERR, "Usage: verify-production-plugin-inventory.php <wp-root>\n");
    exit(2);
}

$_SERVER['HTTP_HOST'] = 'ruskyobchod.sk';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/?verify_production_plugin_inventory=1';

require $root . '/wp-load.php';

if (defined('RUSKY_STAGING_MODE') && RUSKY_STAGING_MODE) {
    fwrite(STDERR, "FAIL production plugin inventory verifier ran against staging\n");
    exit(1);
}

$expected = [
    'all-in-one-wp-migration/all-in-one-wp-migration.php',
    'cookie-notice/cookie-notice.php',
    'cyr3lat/cyr-to-lat.php',
    'elementor/elementor.php',
    'ewww-image-optimizer/ewww-image-optimizer.php',
    'facebook-for-woocommerce/facebook-for-woocommerce.php',
    'gastronom-lang-switcher/gastronom-lang-switcher.php',
    'gastronom-stock-fix/gastronom-stock-fix.php',
    'gls-shipping-for-woocommerce/gls-shipping-for-woocommerce.php',
    'packeta/packeta.php',
    'slovenska-posta-epodaci-harok/slovenska-posta-epodaci-harok.php',
    'so-css/so-css.php',
    'tinymce-advanced/tinymce-advanced.php',
    'woocommerce-extension-master/dotypos.php',
    'woocommerce-legacy-rest-api/woocommerce-legacy-rest-api.php',
    'woocommerce-payments/woocommerce-payments.php',
    'woocommerce/woocommerce.php',
    'wp-pagenavi/wp-pagenavi.php',
    'wp-super-cache/wp-cache.php',
];
sort($expected, SORT_STRING);

global $wpdb;
$raw = $wpdb->get_var(
    "SELECT option_value FROM {$wpdb->options} WHERE option_name = 'active_plugins' LIMIT 1"
);
$configured = maybe_unserialize($raw);
if (!is_array($configured)) {
    fwrite(STDERR, "FAIL active_plugins is not a serialized array\n");
    exit(1);
}
sort($configured, SORT_STRING);

$missing = array_values(array_diff($expected, $configured));
$unexpected = array_values(array_diff($configured, $expected));

if ($missing !== []) {
    fwrite(STDERR, 'FAIL missing configured production plugins: ' . implode(', ', $missing) . "\n");
}
if ($unexpected !== []) {
    fwrite(STDERR, 'FAIL unexpected configured production plugins: ' . implode(', ', $unexpected) . "\n");
}
if ($missing !== [] || $unexpected !== []) {
    exit(1);
}

foreach ($configured as $plugin) {
    if (!is_file($root . '/wp-content/plugins/' . $plugin)) {
        fwrite(STDERR, "FAIL configured plugin entry file is missing: {$plugin}\n");
        exit(1);
    }
}

echo 'OK   configured production plugin inventory matches allowlist (' . count($configured) . ")\n";
echo "OK   every configured production plugin entry file exists\n";
