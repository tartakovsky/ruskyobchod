#!/bin/sh
set -eu

REMOTE_HOST="${REMOTE_HOST:-u595644545@46.202.156.109}"
REMOTE_PORT="${REMOTE_PORT:-65002}"
REMOTE_ROOT="${REMOTE_ROOT:-/home/u595644545/domains/ruskyobchod.sk/public_html}"
ROOT_DIR="$(CDPATH='' cd -- "$(dirname -- "$0")/.." && pwd)"
REMOTE_SCRIPT="/tmp/rusky-verify-output-buffer-policy-$$.php"

cleanup() {
    ssh -n -p "$REMOTE_PORT" "$REMOTE_HOST" "rm -f '$REMOTE_SCRIPT'" >/dev/null 2>&1 || true
}
trap cleanup EXIT

scp -P "$REMOTE_PORT" "$ROOT_DIR/tools/verify-output-buffer-policy.php" "$REMOTE_HOST:$REMOTE_SCRIPT" >/dev/null
ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "php '$REMOTE_SCRIPT' '$REMOTE_ROOT'"
