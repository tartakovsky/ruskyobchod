#!/bin/sh
set -eu

REMOTE_HOST="${REMOTE_HOST:-u595644545@46.202.156.109}"
REMOTE_PORT="${REMOTE_PORT:-65002}"
REMOTE_ROOT="${REMOTE_ROOT:-/home/u595644545/domains/ruskyobchod.sk/public_html}"
REMOTE_MU_DIR="${REMOTE_ROOT}/wp-content/mu-plugins"

tmp_local="$(mktemp)"
tmp_remote="/tmp/rusky-verify-live-php-syntax-$$.sh"
trap 'rm -f "$tmp_local"' EXIT

cat >"$tmp_local" <<'SH'
#!/bin/sh
set -eu

REMOTE_ROOT="$1"
REMOTE_MU_DIR="$2"

lint() {
    file="$1"
    echo "== $file =="
    php -l "$file"
    echo
}

lint "$REMOTE_ROOT/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php"
lint "$REMOTE_ROOT/wp-content/plugins/gastronom-stock-fix/gastronom-stock-fix.php"

find "$REMOTE_MU_DIR" -maxdepth 1 -type f -name 'rusky-*.php' | sort | while read -r file; do
    lint "$file"
done

echo "Live PHP syntax verification complete."
SH

scp -P "$REMOTE_PORT" "$tmp_local" "$REMOTE_HOST:$tmp_remote" >/dev/null
ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "sh '$tmp_remote' '$REMOTE_ROOT' '$REMOTE_MU_DIR'; rm -f '$tmp_remote'"
