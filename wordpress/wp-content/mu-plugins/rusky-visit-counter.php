<?php
/**
 * Plugin Name: Rusky Visit Counter
 * Description: Stores lightweight daily unique visit counts using a first-party cookie.
 */

if (!defined('ABSPATH')) {
    exit;
}

const RVC_OPTION_KEY = 'rusky_daily_visit_counts';
const RVC_SOURCE_OPTION_KEY = 'rusky_daily_visit_sources';
const RVC_FUNNEL_OPTION_KEY = 'rusky_daily_funnel_counts';
const RVC_COOKIE_KEY = 'rusky_visit_day';
const RVC_RETENTION_DAYS = 60;
const RVC_MAX_SOURCE_BUCKETS = 25;

function rvc_is_countable_request(): bool {
    if (is_admin()) {
        return false;
    }

    if (wp_doing_ajax() || wp_doing_cron()) {
        return false;
    }

    if ((defined('REST_REQUEST') && REST_REQUEST) || (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST)) {
        return false;
    }

    if (is_feed() || is_robots() || is_trackback()) {
        return false;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        return false;
    }

    return true;
}

function rvc_touch_cookie(string $day): void {
    if (headers_sent()) {
        return;
    }

    setcookie(
        RVC_COOKIE_KEY,
        $day,
        [
            'expires' => time() + DAY_IN_SECONDS,
            'path' => COOKIEPATH ?: '/',
            'domain' => COOKIE_DOMAIN ?: '',
            'secure' => is_ssl(),
            'httponly' => false,
            'samesite' => 'Lax',
        ]
    );

    $_COOKIE[RVC_COOKIE_KEY] = $day;
}

function rvc_prune_counts(array $counts, string $today): array {
    ksort($counts);
    $cutoff = gmdate('Y-m-d', strtotime($today . ' -' . (RVC_RETENTION_DAYS - 1) . ' days'));

    foreach (array_keys($counts) as $day) {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $day)) {
            unset($counts[$day]);
            continue;
        }

        if ($day < $cutoff) {
            unset($counts[$day]);
        }
    }

    return $counts;
}

function rvc_request_path(): string {
    $path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
    if (!is_string($path) || $path === '') {
        return '/';
    }

    return '/' . ltrim($path, '/');
}

function rvc_referrer_host(): string {
    $referrer = (string) ($_SERVER['HTTP_REFERER'] ?? '');
    if ($referrer === '') {
        return '(direct)';
    }

    $host = parse_url($referrer, PHP_URL_HOST);
    if (!is_string($host) || $host === '') {
        return '(invalid)';
    }

    return strtolower(preg_replace('/^www\./', '', $host));
}

function rvc_user_agent_family(): string {
    $ua = strtolower((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));
    if ($ua === '') {
        return 'bot/empty-user-agent';
    }

    $families = [
        'googlebot' => 'bot/googlebot',
        'bingbot' => 'bot/bingbot',
        'yandex' => 'bot/yandex',
        'ahrefs' => 'bot/ahrefs',
        'semrush' => 'bot/semrush',
        'mj12bot' => 'bot/mj12bot',
        'dotbot' => 'bot/dotbot',
        'bytespider' => 'bot/bytespider',
        'gptbot' => 'bot/gptbot',
        'applebot' => 'bot/applebot',
        'facebookexternalhit' => 'bot/facebook',
        'petalbot' => 'bot/petalbot',
        'curl' => 'tool/curl',
        'wget' => 'tool/wget',
        'python-requests' => 'tool/python-requests',
        'chrome' => 'browser/chrome',
        'safari' => 'browser/safari',
        'firefox' => 'browser/firefox',
        'edg/' => 'browser/edge',
    ];

    foreach ($families as $needle => $label) {
        if (str_contains($ua, $needle)) {
            return $label;
        }
    }

    if (preg_match('/bot|crawler|spider|scrapy|httpclient|scanner|probe/', $ua)) {
        return 'bot/other';
    }

    return 'browser-or-unknown/other';
}

function rvc_is_bot_like_agent(string $family): bool {
    return str_starts_with($family, 'bot/') || str_starts_with($family, 'tool/');
}

function rvc_increment_bucket(array &$bucket, string $key): void {
    if ($key === '') {
        $key = '(empty)';
    }

    if (!isset($bucket[$key]) && count($bucket) >= RVC_MAX_SOURCE_BUCKETS) {
        $key = '(other)';
    }

    $bucket[$key] = (int) ($bucket[$key] ?? 0) + 1;
}

function rvc_track_visit_sources(string $today): void {
    $sources = get_option(RVC_SOURCE_OPTION_KEY, []);
    if (!is_array($sources)) {
        $sources = [];
    }

    $family = rvc_user_agent_family();
    $is_bot_like = rvc_is_bot_like_agent($family);

    if (!isset($sources[$today]) || !is_array($sources[$today])) {
        $sources[$today] = [];
    }

    $day = $sources[$today];
    $day['total'] = (int) ($day['total'] ?? 0) + 1;
    $day[$is_bot_like ? 'bot_like' : 'probably_human'] = (int) ($day[$is_bot_like ? 'bot_like' : 'probably_human'] ?? 0) + 1;

    foreach (['referrers', 'paths', 'agents', 'accept_languages'] as $group) {
        if (!isset($day[$group]) || !is_array($day[$group])) {
            $day[$group] = [];
        }
    }

    rvc_increment_bucket($day['referrers'], rvc_referrer_host());
    rvc_increment_bucket($day['paths'], rvc_request_path());
    rvc_increment_bucket($day['agents'], $family);
    rvc_increment_bucket($day['accept_languages'], substr((string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '(empty)'), 0, 32));

    $sources[$today] = $day;
    $sources = rvc_prune_counts($sources, $today);

    update_option(RVC_SOURCE_OPTION_KEY, $sources, false);
}

function rvc_funnel_events_for_request(): array {
    $events = [];

    if (isset($_GET['add-to-cart'])) {
        $events[] = 'add_to_cart';
    }

    if (function_exists('is_product') && is_product()) {
        $events[] = 'product_view';
    }

    if (function_exists('is_cart') && is_cart()) {
        $events[] = 'cart_view';
    }

    if (function_exists('is_checkout') && is_checkout()) {
        if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-received')) {
            $events[] = 'order_received';
        } elseif (function_exists('is_checkout_pay_page') && is_checkout_pay_page()) {
            $events[] = 'order_pay_view';
        } else {
            $events[] = 'checkout_view';
        }
    }

    return array_values(array_unique($events));
}

function rvc_sanitize_funnel_bucket_value($value): string {
    if (!is_scalar($value)) {
        return '(invalid)';
    }

    $value = sanitize_text_field(wp_unslash((string) $value));
    $value = trim(strtolower($value));

    if ($value === '') {
        return '(empty)';
    }

    return substr($value, 0, 80);
}

function rvc_increment_nested_bucket(array &$bucket, string $group, string $key): void {
    if (!isset($bucket[$group]) || !is_array($bucket[$group])) {
        $bucket[$group] = [];
    }

    rvc_increment_bucket($bucket[$group], $key);
}

function rvc_track_funnel_request(string $today, string $family): void {
    if (rvc_is_bot_like_agent($family)) {
        return;
    }

    $events = rvc_funnel_events_for_request();
    $has_utm = isset($_GET['utm_source']) || isset($_GET['utm_medium']) || isset($_GET['utm_campaign']) || isset($_GET['utm_content']);

    if ($events === [] && !$has_utm) {
        return;
    }

    $funnel = get_option(RVC_FUNNEL_OPTION_KEY, []);
    if (!is_array($funnel)) {
        $funnel = [];
    }

    if (!isset($funnel[$today]) || !is_array($funnel[$today])) {
        $funnel[$today] = [
            'events' => [],
            'utm_sources' => [],
            'utm_mediums' => [],
            'utm_campaigns' => [],
            'utm_contents' => [],
        ];
    }

    $day = $funnel[$today];
    if (!isset($day['events']) || !is_array($day['events'])) {
        $day['events'] = [];
    }

    foreach ($events as $event) {
        $day['events'][$event] = (int) ($day['events'][$event] ?? 0) + 1;
    }

    foreach ([
        'utm_source' => 'utm_sources',
        'utm_medium' => 'utm_mediums',
        'utm_campaign' => 'utm_campaigns',
        'utm_content' => 'utm_contents',
    ] as $param => $group) {
        if (isset($_GET[$param])) {
            rvc_increment_nested_bucket($day, $group, rvc_sanitize_funnel_bucket_value((string) $_GET[$param]));
        }
    }

    $funnel[$today] = $day;
    $funnel = rvc_prune_counts($funnel, $today);

    update_option(RVC_FUNNEL_OPTION_KEY, $funnel, false);
}

function rvc_track_daily_visit(): void {
    if (!rvc_is_countable_request()) {
        return;
    }

    $today = wp_date('Y-m-d', null, wp_timezone());
    $family = rvc_user_agent_family();

    rvc_track_funnel_request($today, $family);

    if (($existing = $_COOKIE[RVC_COOKIE_KEY] ?? '') === $today) {
        return;
    }

    $counts = get_option(RVC_OPTION_KEY, []);
    if (!is_array($counts)) {
        $counts = [];
    }

    $counts[$today] = (int) ($counts[$today] ?? 0) + 1;
    $counts = rvc_prune_counts($counts, $today);

    update_option(RVC_OPTION_KEY, $counts, false);
    rvc_track_visit_sources($today);
    rvc_touch_cookie($today);
}
add_action('template_redirect', 'rvc_track_daily_visit', 1);
