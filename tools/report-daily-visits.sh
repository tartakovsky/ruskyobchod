#!/bin/sh
set -eu

REMOTE_HOST="${REMOTE_HOST:-u595644545@46.202.156.109}"
REMOTE_PORT="${REMOTE_PORT:-65002}"
REMOTE_ROOT="${REMOTE_ROOT:-/home/u595644545/domains/ruskyobchod.sk/public_html}"
DAYS="${1:-7}"

tmp_local="$(mktemp)"
tmp_remote="/tmp/rusky-report-daily-visits-$$.php"
trap 'rm -f "$tmp_local"' EXIT

cat >"$tmp_local" <<'PHP'
<?php
$_SERVER['HTTP_HOST'] = 'ruskyobchod.sk';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_NAME'] = 'ruskyobchod.sk';
$_SERVER['SERVER_PORT'] = '443';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_URI'] = '/?report_daily_visits=1';

require $argv[1] . '/wp-load.php';

$days = max(1, (int) ($argv[2] ?? 7));
$counts = get_option('rusky_daily_visit_counts', []);
if (!is_array($counts)) {
    $counts = [];
}

$timezone = wp_timezone();
$today = new DateTimeImmutable('today', $timezone);
$report = [];

for ($offset = $days - 1; $offset >= 0; $offset--) {
    $day = $today->modify("-{$offset} days")->format('Y-m-d');
    $report[] = [
        'date' => $day,
        'unique_visits' => (int) ($counts[$day] ?? 0),
    ];
}

echo wp_json_encode([
    'timezone' => wp_timezone_string() ?: 'UTC',
    'days' => $days,
    'data' => $report,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
PHP

scp -P "$REMOTE_PORT" "$tmp_local" "$REMOTE_HOST:$tmp_remote" >/dev/null
ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "php '$tmp_remote' '$REMOTE_ROOT' '$DAYS'; rm -f '$tmp_remote'"
