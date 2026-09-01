<?php
declare(strict_types=1);

$root = $argv[1] ?? '';
$profile = $argv[2] ?? '';
if ($root === '' || !in_array($profile, ['production', 'staging'], true)) {
    fwrite(STDERR, "Usage: verify-theme-inventory.php <wp-root> <production|staging>\n");
    exit(2);
}

$_SERVER['HTTP_HOST'] = $profile === 'staging' ? 'staging.ruskyobchod.sk' : 'ruskyobchod.sk';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/?verify_theme_inventory=1';

require $root . '/wp-load.php';

$expected = $profile === 'production'
    ? [
        'food-grocery-store' => '1.2.8',
        'twentytwentyfive' => '1.5',
    ]
    : [
        'twentytwentyfive' => '1.5',
    ];
$expectedActive = $profile === 'production' ? 'food-grocery-store' : 'twentytwentyfive';

$themes = wp_get_themes();
$installed = array_keys($themes);
sort($installed, SORT_STRING);
$expectedInstalled = array_keys($expected);
sort($expectedInstalled, SORT_STRING);

if ($installed !== $expectedInstalled) {
    fwrite(STDERR, "FAIL {$profile} installed theme inventory drifted: " . implode(', ', $installed) . "\n");
    exit(1);
}

$template = (string) get_option('template');
$stylesheet = (string) get_option('stylesheet');
if ($template !== $expectedActive || $stylesheet !== $expectedActive) {
    fwrite(STDERR, "FAIL {$profile} active theme drifted: template={$template} stylesheet={$stylesheet}\n");
    exit(1);
}

foreach ($expected as $slug => $version) {
    $theme = $themes[$slug] ?? null;
    if (!$theme instanceof WP_Theme || !$theme->exists()) {
        fwrite(STDERR, "FAIL {$profile} theme is missing or invalid: {$slug}\n");
        exit(1);
    }
    if ((string) $theme->get('Version') !== $version) {
        fwrite(STDERR, "FAIL {$profile} theme version drifted: {$slug}\n");
        exit(1);
    }
}

echo "OK   {$profile} theme inventory, active theme, and versions match\n";
