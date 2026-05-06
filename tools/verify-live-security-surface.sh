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
check_ok 'wp-config disables file editor' ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "grep -q \"DISALLOW_FILE_EDIT.*true\" '$REMOTE_ROOT/wp-config.php'"
check_ok 'uploads blocks PHP execution' ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "grep -q 'Rusky security: block PHP execution' '$REMOTE_ROOT/wp-content/uploads/.htaccess'"

if [ "$failures" -gt 0 ]; then
    echo "Security surface verification complete with failures: $failures"
    exit 1
fi

echo "Security surface verification complete."
