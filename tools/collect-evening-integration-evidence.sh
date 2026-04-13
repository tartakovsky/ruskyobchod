#!/bin/sh
set -eu

ROOT_DIR="$(CDPATH='' cd -- "$(dirname -- "$0")/.." && pwd)"

PRODUCT_ID="${1:-10617}"
LABEL="${2:-precheck}"
TIMESTAMP="$(date '+%Y-%m-%d-%H%M%S')"
OUT_DIR="$ROOT_DIR/artifacts/evening-integration/${TIMESTAMP}-${LABEL}"

mkdir -p "$OUT_DIR"

run_capture() {
    name="$1"
    shift
    echo "== $name =="
    "$@" | tee "$OUT_DIR/${name}.txt"
    echo
}

printf '%s\n' "$TIMESTAMP" > "$OUT_DIR/timestamp.txt"
printf '%s\n' "$LABEL" > "$OUT_DIR/label.txt"
git -C "$ROOT_DIR" rev-parse HEAD > "$OUT_DIR/git-head.txt"
printf '%s\n' "$PRODUCT_ID" > "$OUT_DIR/product-id.txt"

run_capture "product-state" "$ROOT_DIR/tools/verify-dotypos-product-state.sh" "$PRODUCT_ID"
run_capture "dotypos-readonly" "$ROOT_DIR/tools/verify-dotypos-readonly.sh"
run_capture "admin-order-screen" "$ROOT_DIR/tools/verify-admin-order-screen.sh"
run_capture "active-plugins" "$ROOT_DIR/tools/capture-live-active-plugins.sh"
run_capture "mu-parity" "$ROOT_DIR/tools/audit-live-mu-parity.sh"
run_capture "evening-gate" "$ROOT_DIR/tools/verify-evening-integration-gate.sh"

echo "Evidence saved to $OUT_DIR"
