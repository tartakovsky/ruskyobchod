#!/bin/sh
set -eu

REMOTE_HOST="${REMOTE_HOST:-u595644545@46.202.156.109}"
REMOTE_PORT="${REMOTE_PORT:-65002}"
REMOTE_ROOT="${REMOTE_ROOT:-/home/u595644545/domains/ruskyobchod.sk/public_html}"
MINOR_PHP_PREFIX="${MINOR_PHP_PREFIX:-PHP/8.2}"

tmp_home="$(mktemp)"
tmp_login="$(mktemp)"
trap 'rm -f "$tmp_home" "$tmp_login"' EXIT

curl -ksI "https://ruskyobchod.sk/" >"$tmp_home"
curl -ksI "https://ruskyobchod.sk/wp-login.php" >"$tmp_login"

status_home="$(awk 'NR==1 {print $2}' "$tmp_home")"
status_login="$(awk 'NR==1 {print $2}' "$tmp_login")"
powered_by_home="$(awk -F': ' 'tolower($1)=="x-powered-by" {print $2}' "$tmp_home" | tr -d '\r' | tail -n 1)"
powered_by_login="$(awk -F': ' 'tolower($1)=="x-powered-by" {print $2}' "$tmp_login" | tr -d '\r' | tail -n 1)"

failures=0

if [ "$status_home" = "200" ]; then
    echo "OK   homepage returns 200"
else
    echo "FAIL homepage returned $status_home" >&2
    failures=$((failures + 1))
fi

if [ "$status_login" = "200" ]; then
    echo "OK   wp-login.php returns 200"
else
    echo "FAIL wp-login.php returned $status_login" >&2
    failures=$((failures + 1))
fi

case "$powered_by_home" in
    "$MINOR_PHP_PREFIX"*)
        echo "OK   homepage runs on $powered_by_home"
        ;;
    *)
        echo "FAIL homepage runs on unexpected PHP header: ${powered_by_home:-missing}" >&2
        failures=$((failures + 1))
        ;;
esac

case "$powered_by_login" in
    "$MINOR_PHP_PREFIX"*)
        echo "OK   wp-login.php runs on $powered_by_login"
        ;;
    *)
        echo "FAIL wp-login.php runs on unexpected PHP header: ${powered_by_login:-missing}" >&2
        failures=$((failures + 1))
        ;;
esac

remote_state="$(
REMOTE_ROOT="$REMOTE_ROOT" cat <<'EOF' | ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "REMOTE_ROOT='$REMOTE_ROOT' /opt/alt/php82/usr/bin/php"
<?php
$root = getenv('REMOTE_ROOT');
$config_root = $root . '/wp-config.php';
$config_parent = dirname($root) . '/wp-config.php';
$core_files = [
    'wp-includes/l10n/class-wp-translation-controller.php',
    'wp-includes/class-wp-metadata-lazyloader.php',
    'wp-load.php',
    'wp-settings.php',
];

echo 'config_root=' . (file_exists($config_root) ? 'yes' : 'no') . PHP_EOL;
echo 'config_parent=' . (file_exists($config_parent) ? 'yes' : 'no') . PHP_EOL;

foreach ($core_files as $path) {
    echo 'core:' . $path . '=' . (file_exists($root . '/' . $path) ? 'yes' : 'no') . PHP_EOL;
}
EOF
)"

printf '%s\n' "$remote_state"

config_root="$(printf '%s\n' "$remote_state" | awk -F= '/^config_root=/{print $2}')"
config_parent="$(printf '%s\n' "$remote_state" | awk -F= '/^config_parent=/{print $2}')"

if [ "$config_root" = "yes" ] || [ "$config_parent" = "yes" ]; then
    echo "OK   wp-config.php exists in an allowed bootstrap path"
else
    echo "FAIL wp-config.php missing from both bootstrap paths" >&2
    failures=$((failures + 1))
fi

while IFS= read -r line; do
    case "$line" in
        core:*=yes)
            echo "OK   ${line#core:} present"
            ;;
        core:*=no)
            echo "FAIL ${line#core:} missing" >&2
            failures=$((failures + 1))
            ;;
    esac
done <<EOF
$(printf '%s\n' "$remote_state" | grep '^core:')
EOF

if [ "$failures" -ne 0 ]; then
    echo "Live bootstrap surface verification complete with failures: $failures" >&2
    exit 1
fi

echo "Live bootstrap surface verification complete."
