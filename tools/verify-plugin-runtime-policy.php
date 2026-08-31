<?php
declare(strict_types=1);

$root = $argv[1] ?? '';
$context = $argv[2] ?? '';

if ($root === '' || $context === '') {
    fwrite(STDERR, "Usage: verify-plugin-runtime-policy.php <wp-root> <context>\n");
    exit(2);
}

$_SERVER['HTTP_HOST'] = 'ruskyobchod.sk';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
$_COOKIE = [];

switch ($context) {
    case 'anonymous_frontend':
        break;
    case 'logged_frontend':
        $_COOKIE['wordpress_logged_in_runtime_policy'] = '1';
        break;
    case 'logged_order_page':
        $_SERVER['REQUEST_URI'] = '/my-account/view-order/999/';
        $_COOKIE['wordpress_logged_in_runtime_policy'] = '1';
        break;
    case 'admin':
        define('WP_ADMIN', true);
        $_SERVER['REQUEST_URI'] = '/wp-admin/';
        break;
    case 'rest':
        define('REST_REQUEST', true);
        $_SERVER['REQUEST_URI'] = '/wp-json/gls/v1/dotypos-diag';
        break;
    case 'cron':
        define('DOING_CRON', true);
        $_SERVER['REQUEST_URI'] = '/wp-cron.php';
        break;
    case 'analytics_proxy':
        $_SERVER['REQUEST_URI'] = '/wp-json/woocommerce-analytics/v1/track';
        break;
    default:
        fwrite(STDERR, "Unknown context: {$context}\n");
        exit(2);
}

require $root . '/wp-load.php';

$plugins = (array) get_option('active_plugins', []);

$required = [
    'anonymous_frontend' => [
        'woocommerce/woocommerce.php',
        'woocommerce-extension-master/dotypos.php',
        'gastronom-lang-switcher/gastronom-lang-switcher.php',
    ],
    'logged_frontend' => [
        'woocommerce/woocommerce.php',
        'gastronom-lang-switcher/gastronom-lang-switcher.php',
    ],
    'logged_order_page' => [
        'woocommerce/woocommerce.php',
    ],
    'admin' => [
        'woocommerce/woocommerce.php',
        'woocommerce-extension-master/dotypos.php',
    ],
    'rest' => [
        'woocommerce/woocommerce.php',
        'woocommerce-extension-master/dotypos.php',
    ],
    'cron' => [
        'woocommerce/woocommerce.php',
        'woocommerce-extension-master/dotypos.php',
    ],
    'analytics_proxy' => [
        'woocommerce/woocommerce.php',
    ],
];

$forbidden = [
    'anonymous_frontend' => [
        'real-time-find-and-replace/real-time-find-and-replace.php',
        'elementor-pro/elementor-pro.php',
    ],
    'logged_frontend' => [
        'woocommerce-extension-master/dotypos.php',
        'real-time-find-and-replace/real-time-find-and-replace.php',
        'elementor-pro/elementor-pro.php',
    ],
    'logged_order_page' => [
        'woocommerce-extension-master/dotypos.php',
        'gastronom-lang-switcher/gastronom-lang-switcher.php',
        'wp-super-cache/wp-cache.php',
        'google-analytics-dashboard-for-wp/gadwp.php',
        'real-time-find-and-replace/real-time-find-and-replace.php',
        'elementor-pro/elementor-pro.php',
    ],
    'admin' => [
        'real-time-find-and-replace/real-time-find-and-replace.php',
        'elementor-pro/elementor-pro.php',
    ],
    'rest' => [
        'real-time-find-and-replace/real-time-find-and-replace.php',
        'elementor-pro/elementor-pro.php',
    ],
    'cron' => [
        'real-time-find-and-replace/real-time-find-and-replace.php',
        'elementor-pro/elementor-pro.php',
    ],
    'analytics_proxy' => [
        'woocommerce-extension-master/dotypos.php',
        'packeta/packeta.php',
        'real-time-find-and-replace/real-time-find-and-replace.php',
        'elementor-pro/elementor-pro.php',
    ],
];

$profile = 'production';
if (defined('RUSKY_STAGING_MODE') && RUSKY_STAGING_MODE) {
    /*
     * The isolated staging profile intentionally enables only WooCommerce,
     * its Dotypos dependency and stock-fix.  It must still exercise the
     * request-context filters without pretending to be a production clone.
     */
    $profile = 'staging';

    foreach ($forbidden as $name => $pluginsForContext) {
        $forbidden[$name] = array_values(array_unique(array_merge(
            $pluginsForContext,
            [
                'gastronom-lang-switcher/gastronom-lang-switcher.php',
            ]
        )));
    }

    $required['anonymous_frontend'] = [
        'woocommerce/woocommerce.php',
        'woocommerce-extension-master/dotypos.php',
    ];
    $required['logged_frontend'] = ['woocommerce/woocommerce.php'];
}

foreach ($required[$context] as $plugin) {
    if (!in_array($plugin, $plugins, true)) {
        fwrite(STDERR, "FAIL {$context}: required {$plugin} is not loaded\n");
        exit(1);
    }
}

foreach ($forbidden[$context] as $plugin) {
    if (in_array($plugin, $plugins, true)) {
        fwrite(STDERR, "FAIL {$context}: forbidden {$plugin} is loaded\n");
        exit(1);
    }
}

echo "OK   {$profile}/{$context}: runtime plugin policy matches\n";
