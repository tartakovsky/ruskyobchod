#!/bin/sh
set -eu

REMOTE_HOST="${REMOTE_HOST:-u595644545@46.202.156.109}"
REMOTE_PORT="${REMOTE_PORT:-65002}"
REMOTE_PATH="${REMOTE_PATH:-/home/u595644545/domains/ruskyobchod.sk/public_html}"
SSH_KEY="${SSH_KEY:-$HOME/.ssh/id_ed25519}"

need_cmd() {
    command -v "$1" >/dev/null 2>&1 || {
        echo "Missing required command: $1" >&2
        exit 1
    }
}

need_cmd ssh
need_cmd grep

ssh_base() {
    ssh -i "$SSH_KEY" -p "$REMOTE_PORT" "$REMOTE_HOST" "$@"
}

failures=0

check_contains() {
    haystack="$1"
    needle="$2"
    label="$3"
    if printf '%s' "$haystack" | grep -Fq "$needle"; then
        echo "OK   $label"
    else
        echo "FAIL $label"
        failures=$((failures + 1))
    fi
}

check_not_contains() {
    haystack="$1"
    needle="$2"
    label="$3"
    if printf '%s' "$haystack" | grep -Fq "$needle"; then
        echo "FAIL $label"
        failures=$((failures + 1))
    else
        echo "OK   $label"
    fi
}

active_plugins="$(ssh_base "cd '$REMOTE_PATH' && php -r 'include \"wp-load.php\"; foreach (get_option(\"active_plugins\") as \$plugin) { echo \$plugin, PHP_EOL; }'")"
entrypoint="$(ssh_base "sed -n '1,220p' '$REMOTE_PATH/wp-content/plugins/elementor-pro/elementor-pro.php'")"

check_contains "$active_plugins" 'elementor-pro/elementor-pro.php' 'elementor-pro active plugin present'
check_contains "$entrypoint" 'Plugin Name: Elementor Pro' 'elementor-pro entrypoint plugin header present'
check_contains "$entrypoint" 'Version: 3.18.3' 'elementor-pro entrypoint expected version present'
check_contains "$entrypoint" "add_action( 'plugins_loaded', 'elementor_pro_load_plugin' );" 'elementor-pro standard bootstrap hook present'

check_not_contains "$entrypoint" 'wppnull24' 'elementor-pro entrypoint has no nullware marker'
check_not_contains "$entrypoint" 'base64_decode(' 'elementor-pro entrypoint has no base64 decode'
check_not_contains "$entrypoint" 'wp_remote_post(' 'elementor-pro entrypoint has no direct remote POST'
check_not_contains "$entrypoint" 'curl_exec(' 'elementor-pro entrypoint has no curl exec'

if [ "$failures" -gt 0 ]; then
    echo "Elementor Pro readonly verification complete with failures: $failures"
    exit 1
fi

echo "Elementor Pro readonly verification complete."
