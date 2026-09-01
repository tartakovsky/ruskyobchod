#!/bin/sh
# Verifies that an explicitly provisioned staging site cannot act as production.
set -eu

STAGING_URL="${STAGING_URL:-https://staging.ruskyobchod.sk}"
REMOTE_HOST="${REMOTE_HOST:-u595644545@46.202.156.109}"
REMOTE_PORT="${REMOTE_PORT:-65002}"
REMOTE_ROOT="${REMOTE_ROOT:-/home/u595644545/domains/ruskyobchod.sk/public_html/staging-gastronom}"
REMOTE_SCRIPT="/tmp/rusky-verify-staging-isolation-$$.php"

cleanup() {
    ssh -n -p "$REMOTE_PORT" "$REMOTE_HOST" "rm -f '$REMOTE_SCRIPT'" >/dev/null 2>&1 || true
}
trap cleanup EXIT

case "$STAGING_URL" in
    https://*) ;;
    *) echo "FAIL STAGING_URL must use HTTPS" >&2; exit 1 ;;
esac

expected_url="${STAGING_URL%/}"
headers="$(curl -ksSIL --max-time 20 "$expected_url/")"
printf '%s\n' "$headers" | grep -Eq '^HTTP/[0-9.]+ 200' || {
    echo "FAIL staging homepage does not return 200" >&2
    exit 1
}
if printf '%s\n' "$headers" | grep -Eqi '^location: https://ruskyobchod\.sk/?$'; then
    echo "FAIL staging redirects to production" >&2
    exit 1
fi

check_http_status() {
    path="$1"
    expected="$2"
    label="$3"
    status="$(curl -ksS --max-time 20 -o /dev/null -w '%{http_code}' "$expected_url$path")"
    if [ "$status" != "$expected" ]; then
        echo "FAIL $label expected=$expected got=${status:-none}" >&2
        exit 1
    fi
    echo "OK   $label -> $status"
}

check_http_status '/wp-login.php' '200' 'staging login route'
check_http_status '/wp-json/' '200' 'staging REST index route'
check_http_status '/shop/' '200' 'staging shop route'
check_http_status '/cart/' '200' 'staging cart route'
check_http_status '/my-account/' '200' 'staging account route'
check_http_status '/checkout/' '302' 'empty staging checkout redirects to cart'

cat >"${TMPDIR:-/tmp}/rusky-verify-staging-isolation-$$.php" <<'PHP'
<?php
$_SERVER['HTTP_HOST'] = 'staging.ruskyobchod.sk';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
require $argv[1] . '/wp-load.php';

global $wpdb;
$raw_active_plugins = $wpdb->get_var(
    "SELECT option_value FROM {$wpdb->options} WHERE option_name = 'active_plugins' LIMIT 1"
);
$configured_active_plugins = maybe_unserialize($raw_active_plugins);
if (!is_array($configured_active_plugins)) {
    $configured_active_plugins = [];
}
sort($configured_active_plugins, SORT_STRING);

printf("home=%s\n", home_url('/'));
printf("siteurl=%s\n", get_option('siteurl'));
printf("blog_public=%s\n", (string) get_option('blog_public'));
printf("staging_mode=%s\n", defined('RUSKY_STAGING_MODE') && RUSKY_STAGING_MODE === true ? 'yes' : 'no');
printf("safety_guard=%s\n", function_exists('rssg_block_external_http') ? 'yes' : 'no');
printf("configured_active_plugins=%s\n", implode(',', $configured_active_plugins));
PHP

local_script="${TMPDIR:-/tmp}/rusky-verify-staging-isolation-$$.php"
staging_body="$(mktemp)"
trap 'rm -f "$local_script" "$staging_body"; cleanup' EXIT

curl -ksS --max-time 20 "$expected_url/" -o "$staging_body"
if grep -Eqi 'connect\.facebook\.net|facebook\.com/tr\?id=' "$staging_body"; then
    echo "FAIL staging renders an external Meta Pixel" >&2
    exit 1
fi
echo 'OK   staging does not render an external Meta Pixel'

scp -P "$REMOTE_PORT" "$local_script" "$REMOTE_HOST:$REMOTE_SCRIPT" >/dev/null
rm -f "$local_script"

state="$(ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "php '$REMOTE_SCRIPT' '$REMOTE_ROOT'")"
printf '%s\n' "$state"

check_line() {
    expected="$1"
    label="$2"
    if printf '%s\n' "$state" | grep -Fxq "$expected"; then
        echo "OK   $label"
    else
        echo "FAIL $label" >&2
        exit 1
    fi
}

check_line "home=$expected_url/" 'WordPress home URL is staging'
check_line "siteurl=$expected_url" 'WordPress site URL is staging'
check_line 'blog_public=0' 'search indexing is disabled'
check_line 'staging_mode=yes' 'staging mode is explicitly enabled'
check_line 'safety_guard=yes' 'mail and external WordPress HTTP guard is loaded'
check_line 'configured_active_plugins=gastronom-stock-fix/gastronom-stock-fix.php,woocommerce-extension-master/dotypos.php,woocommerce/woocommerce.php' 'configured active plugin inventory is minimal'

echo 'Staging isolation verification complete.'
