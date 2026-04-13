#!/bin/sh
set -eu

REMOTE_HOST="${REMOTE_HOST:-u595644545@46.202.156.109}"
REMOTE_PORT="${REMOTE_PORT:-65002}"
REMOTE_MU_DIR="${REMOTE_MU_DIR:-/home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/mu-plugins}"
LOCAL_MU_DIR="${LOCAL_MU_DIR:-$(CDPATH='' cd -- "$(dirname -- "$0")/../wordpress/wp-content/mu-plugins" && pwd)}"

need_cmd() {
    command -v "$1" >/dev/null 2>&1 || {
        echo "Missing required command: $1" >&2
        exit 1
    }
}

need_cmd ssh
need_cmd sort
need_cmd comm
need_cmd mktemp

local_list="$(mktemp)"
remote_list="$(mktemp)"
trap 'rm -f "$local_list" "$remote_list"' EXIT

find "$LOCAL_MU_DIR" -maxdepth 1 -type f -name 'rusky-*.php' -exec basename {} \; | sort >"$local_list"
ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "find '$REMOTE_MU_DIR' -maxdepth 1 -type f -name 'rusky-*.php' -exec basename {} \\; | sort" >"$remote_list"

echo "== local-only =="
comm -23 "$local_list" "$remote_list" || true
echo

echo "== remote-only =="
comm -13 "$local_list" "$remote_list" || true
echo

echo "== shared =="
comm -12 "$local_list" "$remote_list" || true
