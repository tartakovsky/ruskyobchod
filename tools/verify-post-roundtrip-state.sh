#!/bin/sh
set -eu

ROOT_DIR="$(CDPATH='' cd -- "$(dirname -- "$0")/.." && pwd)"

PRODUCT_ID="${1:-10617}"

run() {
    label="$1"
    shift
    echo "== $label =="
    "$@"
    echo
}

run "dotypos product state" "$ROOT_DIR/tools/verify-dotypos-product-state.sh" "$PRODUCT_ID"
run "dotypos readonly" "$ROOT_DIR/tools/verify-dotypos-readonly.sh"
run "evening integration gate" "$ROOT_DIR/tools/verify-evening-integration-gate.sh"

echo "Post-roundtrip state verification complete."
