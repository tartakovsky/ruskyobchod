#!/bin/sh
set -eu

REMOTE_HOST="${REMOTE_HOST:-u595644545@46.202.156.109}"
REMOTE_PORT="${REMOTE_PORT:-65002}"
REMOTE_ROOT="${REMOTE_ROOT:-/home/u595644545/domains/ruskyobchod.sk/public_html}"
SSH_KEY="${SSH_KEY:-/Users/alexandertartakovsky/.ssh/id_ed25519}"

output="$(
REMOTE_ROOT="$REMOTE_ROOT" cat <<'EOF' | ssh -i "$SSH_KEY" -p "$REMOTE_PORT" "$REMOTE_HOST" "REMOTE_ROOT='$REMOTE_ROOT' bash"
cd "$REMOTE_ROOT"
/opt/alt/php82/usr/bin/php <<'PHP'
<?php
require 'wp-load.php';

global $wpdb;

$table = $wpdb->prefix . 'actionscheduler_actions';
$exists = $wpdb->get_var("SHOW TABLES LIKE '{$table}'");

if (!$exists) {
    echo "FAIL missing_action_scheduler_table\n";
    exit(1);
}

$overdue = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE hook LIKE '%dotypos%' AND ((status = 'pending' AND scheduled_date_gmt <= UTC_TIMESTAMP()) OR status = 'in-progress')");
$future_pending = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE hook LIKE '%dotypos%' AND status = 'pending' AND scheduled_date_gmt > UTC_TIMESTAMP()");
$failed_recent = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE hook LIKE '%dotypos%' AND status = 'failed' AND scheduled_date_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 3 DAY)");
$latest = $wpdb->get_results("SELECT action_id, hook, status, scheduled_date_gmt, last_attempt_gmt FROM {$table} WHERE hook LIKE '%dotypos%' ORDER BY action_id DESC LIMIT 10", ARRAY_A);

echo 'overdue=' . $overdue . PHP_EOL;
echo 'future_pending=' . $future_pending . PHP_EOL;
echo 'failed_recent=' . $failed_recent . PHP_EOL;

foreach ($latest as $row) {
    echo 'latest=' . implode('|', [
        $row['action_id'],
        $row['hook'],
        $row['status'],
        $row['scheduled_date_gmt'],
        $row['last_attempt_gmt'],
    ]) . PHP_EOL;
}
PHP
EOF
)"

printf '%s\n' "$output"

overdue="$(printf '%s\n' "$output" | awk -F= '/^overdue=/{print $2}')"
future_pending="$(printf '%s\n' "$output" | awk -F= '/^future_pending=/{print $2}')"
failed_recent="$(printf '%s\n' "$output" | awk -F= '/^failed_recent=/{print $2}')"

failures=0

if [ "${overdue:-1}" -ne 0 ]; then
    echo "FAIL dotypos action scheduler has overdue/in-progress actions" >&2
    failures=$((failures + 1))
else
    echo "OK   dotypos action scheduler has no overdue/in-progress actions"
fi

if [ "${future_pending:-0}" -ne 0 ]; then
    echo "OK   dotypos action scheduler has future pending actions (${future_pending})"
fi

if [ "${failed_recent:-1}" -ne 0 ]; then
    echo "FAIL dotypos action scheduler has recent failed actions" >&2
    failures=$((failures + 1))
else
    echo "OK   dotypos action scheduler has no recent failed actions"
fi

if [ "$failures" -ne 0 ]; then
    echo "Dotypos action scheduler verification complete with failures: $failures" >&2
    exit 1
fi

echo "Dotypos action scheduler verification complete."
