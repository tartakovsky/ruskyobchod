#!/bin/sh
set -eu

REMOTE_HOST="${REMOTE_HOST:-u595644545@46.202.156.109}"
REMOTE_PORT="${REMOTE_PORT:-65002}"
REMOTE_ROOT="${REMOTE_ROOT:-/home/u595644545/domains/ruskyobchod.sk/public_html}"
DAYS="${1:-14}"
TOP="${2:-10}"

tmp_local="$(mktemp)"
tmp_remote="/tmp/rusky-report-funnel-visits-$$.php"
trap 'rm -f "$tmp_local"' EXIT

cat >"$tmp_local" <<'PHP'
<?php
$_SERVER['HTTP_HOST'] = 'ruskyobchod.sk';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_NAME'] = 'ruskyobchod.sk';
$_SERVER['SERVER_PORT'] = '443';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_URI'] = '/?report_funnel_visits=1';

require $argv[1] . '/wp-load.php';

$days = max(1, (int) ($argv[2] ?? 14));
$top = max(1, (int) ($argv[3] ?? 10));
$funnel = get_option('rusky_daily_funnel_counts', []);
if (!is_array($funnel)) {
    $funnel = [];
}

function rfv_top(array $items, int $limit): array {
    arsort($items);
    return array_slice($items, 0, $limit, true);
}

$timezone = wp_timezone();
$today = new DateTimeImmutable('today', $timezone);
$report = [];

for ($offset = $days - 1; $offset >= 0; $offset--) {
    $dayKey = $today->modify("-{$offset} days")->format('Y-m-d');
    $day = $funnel[$dayKey] ?? [];
    if (!is_array($day)) {
        $day = [];
    }

    $report[] = [
        'date' => $dayKey,
        'events' => is_array($day['events'] ?? null) ? $day['events'] : [],
        'utm_sources' => rfv_top(is_array($day['utm_sources'] ?? null) ? $day['utm_sources'] : [], $top),
        'utm_mediums' => rfv_top(is_array($day['utm_mediums'] ?? null) ? $day['utm_mediums'] : [], $top),
        'utm_campaigns' => rfv_top(is_array($day['utm_campaigns'] ?? null) ? $day['utm_campaigns'] : [], $top),
        'utm_contents' => rfv_top(is_array($day['utm_contents'] ?? null) ? $day['utm_contents'] : [], $top),
    ];
}

echo wp_json_encode([
    'timezone' => wp_timezone_string() ?: 'UTC',
    'days' => $days,
    'top' => $top,
    'data' => $report,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
PHP

scp -P "$REMOTE_PORT" "$tmp_local" "$REMOTE_HOST:$tmp_remote" >/dev/null
ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "php '$tmp_remote' '$REMOTE_ROOT' '$DAYS' '$TOP'; rm -f '$tmp_remote'"
