<?php
/** Read-only regression checks for the existing server-side translation owner. */
if (PHP_SAPI !== 'cli' || count($argv) < 3 || !in_array($argv[2], ['ru', 'sk'], true)) {
    exit("Usage: php verify-translation-fallback.php WP_ROOT ru|sk\n");
}
$lang = $argv[2];
define('DISABLE_WP_CRON', true);
$_GET['lang'] = $lang;
$_COOKIE['gastronom_lang'] = $lang;
$_SERVER['HTTP_HOST'] = strpos($argv[1], 'staging-gastronom') !== false ? 'staging.ruskyobchod.sk' : 'gastronom-bratislava.sk';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/?lang=' . $lang;
$_SERVER['HTTPS'] = 'on';
require rtrim($argv[1], '/') . '/wp-load.php';
switch_to_locale($lang === 'ru' ? 'ru_RU' : 'sk_SK');

// The deliberately minimal staging profile has no language packs. It may read
// the production catalogues into this CLI process only; no files/options change.
if (!empty($argv[3])) {
    if (!defined('RUSKY_STAGING_MODE') || RUSKY_STAGING_MODE !== true) {
        exit("External test catalogue path is restricted to staging.\n");
    }
    $locale = $lang === 'ru' ? 'ru_RU' : 'sk_SK';
    add_filter('locale', static function () use ($locale) { return $locale; }, 999);
    add_filter('determine_locale', static function () use ($locale) { return $locale; }, 999);
    unload_textdomain('woocommerce', true);
    if (!load_textdomain('woocommerce', rtrim($argv[3], '/') . '/woocommerce-' . $locale . '.mo', $locale)) {
        exit("Could not load staging test catalogue.\n");
    }
    echo "Using production language catalogue read-only in isolated staging process.\n";
}

function translation_check($condition, string $label): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL $label\n");
        exit(1);
    }
    echo "OK $label\n";
}

function translation_owner_callback(string $hook): Closure {
    global $wp_filter;
    $matches = [];
    foreach (($wp_filter[$hook]->callbacks[120] ?? []) as $entry) {
        $callback = $entry['function'];
        if ($callback instanceof Closure && basename((new ReflectionFunction($callback))->getFileName()) === 'rusky-theme-chrome-language.php') {
            $matches[] = $callback;
        }
    }
    translation_check(count($matches) === 1, "$hook has exactly one existing owner");
    return $matches[0];
}

translation_check(rtcl_current_lang() === $lang, "requested language $lang");
$gettext = translation_owner_callback('gettext');
$ngettext = translation_owner_callback('ngettext');
$native_text = $lang === 'ru' ? 'Готовый перевод пакета' : 'Hotový preklad balíka';
translation_check($gettext($native_text, 'Unmapped source text', 'woocommerce') === $native_text, 'unknown source preserves existing translation');
translation_check($gettext('Untranslated source', 'Untranslated source', 'woocommerce') === 'Untranslated source', 'missing native and custom translation preserves source');
$expected = $lang === 'ru' ? 'В наличии' : 'Na sklade';
translation_check($gettext('In stock', 'In stock', 'woocommerce') === $expected, 'explicit source override preserved');
$incoming = $lang === 'ru' ? 'Môj účet' : 'Мой аккаунт';
$expected = $lang === 'ru' ? 'Мой аккаунт' : 'Môj účet';
translation_check($gettext($incoming, 'Unmapped account source', 'woocommerce') === $expected, 'existing translated-phrase normalization preserved');

foreach ([1, 2, 5, 21, 22] as $number) {
    $native_plural = $lang === 'ru'
        ? (in_array($number, [1, 21], true) ? '%d единица' : (in_array($number, [2, 22], true) ? '%d единицы' : '%d единиц'))
        : ($number === 1 ? '%d položka' : ($number >= 2 && $number <= 4 ? '%d položky' : '%d položiek'));
    translation_check($ngettext($native_plural, '%d inventory unit', '%d inventory units', $number, 'woocommerce') === $native_plural, "native plural form preserved for $number");
}

$strings = [
    'Out of stock',
    'You cannot add that amount of &quot;%1$s&quot; to the cart because there is not enough stock (%2$s remaining).',
    'You cannot add that amount to the cart &mdash; we have %1$s in stock and you already have %2$s in your cart.',
];
foreach ($strings as $source) {
    $effective = __($source, 'woocommerce');
    $native = get_translations_for_domain('woocommerce')->translate($source);
    translation_check($native !== $source, 'native language pack contains: ' . $source);
    translation_check($effective === $native, 'full filter chain preserves: ' . $source);
    echo 'RESULT ' . wp_json_encode(['source' => $source, 'effective' => $effective], JSON_UNESCAPED_UNICODE) . "\n";
}
echo "Translation fallback checks passed ($lang); no data saved.\n";
