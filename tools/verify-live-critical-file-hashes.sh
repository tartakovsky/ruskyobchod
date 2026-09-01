#!/bin/sh
set -eu

REMOTE_HOST="${REMOTE_HOST:-u595644545@46.202.156.109}"
REMOTE_PORT="${REMOTE_PORT:-65002}"
REMOTE_ROOT="${REMOTE_ROOT:-/home/u595644545/domains/ruskyobchod.sk/public_html}"
ROOT_DIR="$(CDPATH='' cd -- "$(dirname -- "$0")/.." && pwd)"

tmp_remote="$(mktemp)"
tmp_remote_hashes="$(mktemp)"
trap 'rm -f "$tmp_remote" "$tmp_remote_hashes"' EXIT
mismatch_count=0

if command -v shasum >/dev/null 2>&1; then
    hash_local() {
        LC_ALL=C shasum -a 256 "$1" | awk '{print $1}'
    }
elif command -v sha256sum >/dev/null 2>&1; then
    hash_local() {
        LC_ALL=C sha256sum "$1" | awk '{print $1}'
    }
else
    echo "FAIL no local sha256 tool found" >&2
    exit 1
fi

check_pair() {
    local_file="$1"
    remote_file="$2"
    label="$3"

    local_hash="$(hash_local "$local_file")"
    remote_hash="$(ssh -n -p "$REMOTE_PORT" "$REMOTE_HOST" "if command -v sha256sum >/dev/null 2>&1; then sha256sum '$remote_file' | cut -d ' ' -f1; else shasum -a 256 '$remote_file' | cut -d ' ' -f1; fi")"

    if [ "$local_hash" != "$remote_hash" ]; then
        echo "FAIL $label hash mismatch" >&2
        echo "local  $local_hash" >&2
        echo "remote $remote_hash" >&2
        mismatch_count=$((mismatch_count + 1))
        return
    fi
    echo "OK   $label hash matches"
}

check_pair \
    "$ROOT_DIR/wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php" \
    "$REMOTE_ROOT/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php" \
    "gastronom-lang-switcher.php"

check_pair \
    "$ROOT_DIR/wordpress/wp-content/plugins/gastronom-lang-switcher/gls-script.js" \
    "$REMOTE_ROOT/wp-content/plugins/gastronom-lang-switcher/gls-script.js" \
    "gls-script.js"

check_pair \
    "$ROOT_DIR/wordpress/wp-content/plugins/gastronom-lang-switcher/gls-style.css" \
    "$REMOTE_ROOT/wp-content/plugins/gastronom-lang-switcher/gls-style.css" \
    "gls-style.css"

check_pair \
    "$ROOT_DIR/wordpress/wp-content/plugins/gastronom-stock-fix/gastronom-stock-fix.php" \
    "$REMOTE_ROOT/wp-content/plugins/gastronom-stock-fix/gastronom-stock-fix.php" \
    "gastronom-stock-fix.php"

check_pair \
    "$ROOT_DIR/wordpress/wp-content/themes/food-grocery-store/footer.php" \
    "$REMOTE_ROOT/wp-content/themes/food-grocery-store/footer.php" \
    "food-grocery-store/footer.php"

ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "find '$REMOTE_ROOT/wp-content/mu-plugins' -maxdepth 1 -type f -name 'rusky-*.php' -exec basename {} \\; | LC_ALL=C sort" >"$tmp_remote"
ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "find '$REMOTE_ROOT/wp-content/mu-plugins' -maxdepth 1 -type f -name 'rusky-*.php' -exec sha256sum {} \\;" >"$tmp_remote_hashes"

while read -r base; do
    [ -n "$base" ] || continue
    local_file="$ROOT_DIR/wordpress/wp-content/mu-plugins/$base"
    remote_file="$REMOTE_ROOT/wp-content/mu-plugins/$base"
    if [ ! -f "$local_file" ]; then
        echo "FAIL local file missing for remote shared MU: $base" >&2
        exit 1
    fi
    local_hash="$(hash_local "$local_file")"
    remote_hash="$(awk -v base="$base" '
        {
            count = split($2, parts, "/")
            if (parts[count] == base) {
                print $1
                exit
            }
        }
    ' "$tmp_remote_hashes")"
    if [ -z "$remote_hash" ]; then
        echo "FAIL remote hash missing for $base" >&2
        mismatch_count=$((mismatch_count + 1))
        continue
    fi
    if [ "$local_hash" != "$remote_hash" ]; then
        echo "FAIL $base hash mismatch" >&2
        echo "local  $local_hash" >&2
        echo "remote $remote_hash" >&2
        mismatch_count=$((mismatch_count + 1))
        continue
    fi
    echo "OK   $base hash matches"
done <"$tmp_remote"

if [ "$mismatch_count" -ne 0 ]; then
    echo "FAIL live critical file hash verification found $mismatch_count mismatches" >&2
    exit 1
fi

echo "Live critical file hash verification complete."
