<?php
/**
 * Plugin Name: Rusky Visit Counter
 * Description: Stores lightweight daily unique visit counts using a first-party cookie.
 */

if (!defined('ABSPATH')) {
    exit;
}

const RVC_OPTION_KEY = 'rusky_daily_visit_counts';
const RVC_COOKIE_KEY = 'rusky_visit_day';
const RVC_RETENTION_DAYS = 60;

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

function rvc_track_daily_visit(): void {
    if (!rvc_is_countable_request()) {
        return;
    }

    $today = wp_date('Y-m-d', null, wp_timezone());
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
    rvc_touch_cookie($today);
}
add_action('template_redirect', 'rvc_track_daily_visit', 1);
