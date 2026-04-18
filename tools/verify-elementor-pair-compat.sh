#!/bin/sh
set -eu

REMOTE_HOST="${REMOTE_HOST:-u595644545@46.202.156.109}"
REMOTE_PORT="${REMOTE_PORT:-65002}"
REMOTE_ROOT="${REMOTE_ROOT:-/home/u595644545/domains/ruskyobchod.sk/public_html}"

output="$(
REMOTE_ROOT="$REMOTE_ROOT" cat <<'EOF' | ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "REMOTE_ROOT='$REMOTE_ROOT' /opt/alt/php82/usr/bin/php"
<?php
$root = getenv('REMOTE_ROOT');

$_SERVER['HTTP_HOST'] = 'ruskyobchod.sk';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_NAME'] = 'ruskyobchod.sk';
$_SERVER['SERVER_PORT'] = '443';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

require $root . '/wp-load.php';

$active = get_option('active_plugins', []);
$free_path = $root . '/wp-content/plugins/elementor/elementor.php';
$pro_path = $root . '/wp-content/plugins/elementor-pro/elementor-pro.php';

$free_version = null;
$pro_version = null;

if (file_exists($free_path)) {
    $free_data = get_file_data($free_path, ['Version' => 'Version']);
    $free_version = $free_data['Version'] ?? '';
}

if (file_exists($pro_path)) {
    $pro_data = get_file_data($pro_path, ['Version' => 'Version']);
    $pro_version = $pro_data['Version'] ?? '';
}

$free_active = in_array('elementor/elementor.php', $active, true) ? 'yes' : 'no';
$pro_active = in_array('elementor-pro/elementor-pro.php', $active, true) ? 'yes' : 'no';

echo 'free_exists=' . (file_exists($free_path) ? 'yes' : 'no') . PHP_EOL;
echo 'free_version=' . ($free_version ?: 'missing') . PHP_EOL;
echo 'free_active=' . $free_active . PHP_EOL;
echo 'pro_exists=' . (file_exists($pro_path) ? 'yes' : 'no') . PHP_EOL;
echo 'pro_version=' . ($pro_version ?: 'missing') . PHP_EOL;
echo 'pro_active=' . $pro_active . PHP_EOL;
EOF
)"

printf '%s\n' "$output"

free_exists="$(printf '%s\n' "$output" | awk -F= '/^free_exists=/{print $2}')"
free_version="$(printf '%s\n' "$output" | awk -F= '/^free_version=/{print $2}')"
free_active="$(printf '%s\n' "$output" | awk -F= '/^free_active=/{print $2}')"
pro_exists="$(printf '%s\n' "$output" | awk -F= '/^pro_exists=/{print $2}')"
pro_version="$(printf '%s\n' "$output" | awk -F= '/^pro_version=/{print $2}')"
pro_active="$(printf '%s\n' "$output" | awk -F= '/^pro_active=/{print $2}')"

failures=0

if [ "$free_exists" = "yes" ]; then
    echo "OK   Elementor exists ($free_version)"
else
    echo "FAIL Elementor core plugin missing" >&2
    failures=$((failures + 1))
fi

if [ "$free_active" = "yes" ]; then
    echo "OK   Elementor core is active"
else
    echo "FAIL Elementor core is not active" >&2
    failures=$((failures + 1))
fi

if [ "$pro_exists" = "yes" ]; then
    echo "OK   Elementor Pro package exists ($pro_version)"
else
    echo "OK   Elementor Pro package absent"
fi

if [ "$pro_active" = "yes" ]; then
    free_major_minor="$(printf '%s' "$free_version" | awk -F. '{print $1 "." $2}')"
    pro_major_minor="$(printf '%s' "$pro_version" | awk -F. '{print $1 "." $2}')"
    case "$free_major_minor" in
        4.*)
            case "$pro_major_minor" in
                4.*)
                    echo "OK   Elementor/Elementor Pro major line is aligned ($free_version / $pro_version)"
                    ;;
                *)
                    echo "FAIL Elementor Pro is active but incompatible with Elementor $free_version (Pro $pro_version)" >&2
                    failures=$((failures + 1))
                    ;;
            esac
            ;;
        *)
            echo "OK   Elementor Pro active on non-4.x core ($free_version / $pro_version)"
            ;;
    esac
else
    echo "OK   Elementor Pro is not active"
fi

if [ "$failures" -ne 0 ]; then
    echo "Elementor pair compatibility verification complete with failures: $failures" >&2
    exit 1
fi

echo "Elementor pair compatibility verification complete."
