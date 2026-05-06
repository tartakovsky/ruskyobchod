#!/bin/sh
set -eu

REMOTE_HOST="${REMOTE_HOST:-u595644545@46.202.156.109}"
REMOTE_PORT="${REMOTE_PORT:-65002}"
REMOTE_ROOT="${REMOTE_ROOT:-/home/u595644545/domains/ruskyobchod.sk/public_html}"
DAYS="${1:-7}"
TOP="${2:-10}"

tmp_local="$(mktemp)"
tmp_remote="/tmp/rusky-report-visit-sources-$$.php"
trap 'rm -f "$tmp_local"' EXIT

cat >"$tmp_local" <<'PHP'
<?php
$_SERVER['HTTP_HOST'] = 'ruskyobchod.sk';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_NAME'] = 'ruskyobchod.sk';
$_SERVER['SERVER_PORT'] = '443';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_URI'] = '/?report_visit_sources=1';

require $argv[1] . '/wp-load.php';

$days = max(1, (int) ($argv[2] ?? 7));
$top = max(1, (int) ($argv[3] ?? 10));
$counts = get_option('rusky_daily_visit_counts', []);
$sources = get_option('rusky_daily_visit_sources', []);

if (!is_array($counts)) {
    $counts = [];
}
if (!is_array($sources)) {
    $sources = [];
}

function rvs_top(array $items, int $limit): array {
    arsort($items);
    return array_slice($items, 0, $limit, true);
}

$timezone = wp_timezone();
$today = new DateTimeImmutable('today', $timezone);
$report = [];

for ($offset = $days - 1; $offset >= 0; $offset--) {
    $day = $today->modify("-{$offset} days")->format('Y-m-d');
    $source = $sources[$day] ?? [];
    if (!is_array($source)) {
        $source = [];
    }

    $report[] = [
        'date' => $day,
        'unique_visits' => (int) ($counts[$day] ?? 0),
        'diagnosed_visits' => (int) ($source['total'] ?? 0),
        'bot_like' => (int) ($source['bot_like'] ?? 0),
        'probably_human' => (int) ($source['probably_human'] ?? 0),
        'top_referrers' => rvs_top(is_array($source['referrers'] ?? null) ? $source['referrers'] : [], $top),
        'top_paths' => rvs_top(is_array($source['paths'] ?? null) ? $source['paths'] : [], $top),
        'top_agents' => rvs_top(is_array($source['agents'] ?? null) ? $source['agents'] : [], $top),
        'top_accept_languages' => rvs_top(is_array($source['accept_languages'] ?? null) ? $source['accept_languages'] : [], $top),
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
