#!/bin/sh
set -eu

ROOT_DIR="$(CDPATH='' cd -- "$(dirname -- "$0")/.." && pwd)"

PRODUCT_ID="${1:-10617}"
DELTA="${2:-0.01}"

run() {
    label="$1"
    shift
    echo "== $label =="
    "$@"
    echo
}

run "pre-integration gate" "$ROOT_DIR/tools/verify-evening-integration-gate.sh"

if [ "${REALLY_MUTATE_DOTYPOS:-0}" != "1" ]; then
    echo "Mutation step skipped."
    echo "Set REALLY_MUTATE_DOTYPOS=1 to run guarded Dotypos roundtrip."
    echo "Plan: $ROOT_DIR/.plans/2026-04-13-real-dotypos-roundtrip-mini-plan.md"
    exit 0
fi

run "guarded Dotypos roundtrip" "$ROOT_DIR/tools/real-dotypos-roundtrip.sh" "$PRODUCT_ID" "$DELTA"
run "post-roundtrip state" "$ROOT_DIR/tools/verify-post-roundtrip-state.sh" "$PRODUCT_ID"

echo "Evening integration run complete."
