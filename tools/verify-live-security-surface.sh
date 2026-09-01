#!/bin/sh
set -eu

REMOTE_HOST="${REMOTE_HOST:-u595644545@46.202.156.109}"
REMOTE_PORT="${REMOTE_PORT:-65002}"
REMOTE_ROOT="${REMOTE_ROOT:-/home/u595644545/domains/ruskyobchod.sk/public_html}"
BASE_URL="${BASE_URL:-https://ruskyobchod.sk}"

failures=0

check_ok() {
    label="$1"
    shift
    if "$@"; then
        echo "OK   $label"
    else
        echo "FAIL $label"
        failures=$((failures + 1))
    fi
}

googlebot_body="$(ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "curl -ks -A Googlebot '$BASE_URL/?security_surface_verify=1'")"
active_plugins="$(ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "php -r '\$_SERVER[\"HTTP_HOST\"]=\"ruskyobchod.sk\"; \$_SERVER[\"REQUEST_METHOD\"]=\"GET\"; \$_SERVER[\"REQUEST_URI\"]=\"/\"; require \"$REMOTE_ROOT/wp-load.php\"; echo implode(\"\\n\", get_option(\"active_plugins\", []));'")"

check_ok 'Googlebot sees Gastronom page' sh -c "printf '%s' \"\$1\" | grep -q 'Gastronom'" _ "$googlebot_body"
check_ok 'Googlebot does not see known cloaked Japanese spam' sh -c "! printf '%s' \"\$1\" | grep -Eq 'komeri|オリックス|mercdn|data-browse-mode'" _ "$googlebot_body"
check_ok 'root moon.php is not public' ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "test ! -f '$REMOTE_ROOT/moon.php'"
check_ok 'view-source shell is not public' ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "test ! -e '$REMOTE_ROOT/wp-content/plugins/view-source/moon.php'"
check_ok 'file manager plugins are not active' sh -c "! printf '%s' \"\$1\" | grep -Eq 'file-manager-advanced|wp-file-manager'" _ "$active_plugins"
check_ok 'security guard mu-plugin is present' ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "test -f '$REMOTE_ROOT/wp-content/mu-plugins/rusky-security-guard.php'"
check_ok 'web root contains no public backup or dump artifacts' ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "test -z \"\$(find '$REMOTE_ROOT' -xdev -type f \\( -name '*.php.bak' -o -name '*.php.bak-*' -o -name '*.php.off' -o -name '*.bak' -o -name '*.bak-*' -o -name '*.old' -o -name '*.orig' -o -name '*.save' -o -name '*~' -o -name '.env' -o -name '.env.*' -o -name '*.sql' -o -name '*.sql.gz' -o -name '*.dump' -o -name '*.zip' -o -name '*.tar' -o -name '*.tgz' -o -name '*.tar.gz' \\) -print -quit)\""
check_ok 'legacy media backup directory is outside web root' ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "test ! -d '$REMOTE_ROOT/wp-content/backup'"
check_ok 'WooCommerce logs deny direct HTTP access' ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "grep -Eqi 'deny[[:space:]]+from[[:space:]]+all|Require[[:space:]]+all[[:space:]]+denied' '$REMOTE_ROOT/wp-content/uploads/wc-logs/.htaccess'"
check_ok 'debug log is not public' sh -c "test \"\$(curl -ksS --max-time 20 -o /dev/null -w '%{http_code}' '$BASE_URL/wp-content/debug.log')\" != 200"
check_ok 'staging tree is not exposed through production URL' sh -c "test \"\$(curl -ksS --max-time 20 -o /dev/null -w '%{http_code}' '$BASE_URL/staging-gastronom/')\" != 200"
check_ok 'Git metadata is not public' sh -c "test \"\$(curl -ksS --max-time 20 -o /dev/null -w '%{http_code}' '$BASE_URL/.git/config')\" != 200"
check_ok 'web root contains no symbolic links' ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "test -z \"\$(find '$REMOTE_ROOT' -xdev -type l -print -quit)\""
check_ok 'web root contains no world-writable paths' ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "test -z \"\$(find '$REMOTE_ROOT' -xdev \\( -type f -o -type d \\) -perm -0002 -print -quit)\""
check_ok 'wp-config permissions are restricted' ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "test \"\$(stat -c '%a' '$REMOTE_ROOT/wp-config.php')\" = 640"
check_ok 'wp-config disables file editor' ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "grep -q \"DISALLOW_FILE_EDIT.*true\" '$REMOTE_ROOT/wp-config.php'"
check_ok 'uploads blocks PHP execution' ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "grep -q 'Rusky security: block PHP execution' '$REMOTE_ROOT/wp-content/uploads/.htaccess'"
check_ok 'uploads contains only known non-executable PHP guards' ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "test \"\$(find '$REMOTE_ROOT/wp-content/uploads' -xdev -type f \\( -iname '*.php' -o -iname '*.phtml' -o -iname '*.phar' -o -iname '*.php[0-9]' \\) | wc -l)\" -eq 2 && printf '%s  %s\\n%s  %s\\n' 'ae6fc10874855daddd44765416e0f28da65a2b89e9e32802dd66e7fa6690d2d9' '$REMOTE_ROOT/wp-content/uploads/gls-shipping-labels/index.php' '7aa373bd001f0bc70bfdfe37454bdc3c6796c567c3ace11930c474ed8a78e0a2' '$REMOTE_ROOT/wp-content/uploads/wpseo-redirects/index.php' | sha256sum -c - >/dev/null && test \"\$(find '$REMOTE_ROOT/staging-gastronom/wp-content/uploads' -xdev -type f \\( -iname '*.php' -o -iname '*.phtml' -o -iname '*.phar' -o -iname '*.php[0-9]' \\) 2>/dev/null | wc -l)\" -eq 0"
check_ok 'known suspicious admins are not administrators' ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "php -r '\$_SERVER[\"HTTP_HOST\"]=\"ruskyobchod.sk\"; \$_SERVER[\"REQUEST_METHOD\"]=\"GET\"; \$_SERVER[\"REQUEST_URI\"]=\"/\"; require \"$REMOTE_ROOT/wp-load.php\"; foreach ([\"osdibijl\", \"xevnijso\"] as \$login) { \$user = get_user_by(\"login\", \$login); if (\$user && in_array(\"administrator\", (array) \$user->roles, true)) { exit(1); } }'"

if [ "$failures" -gt 0 ]; then
    echo "Security surface verification complete with failures: $failures"
    exit 1
fi

echo "Security surface verification complete."
