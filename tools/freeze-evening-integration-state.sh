#!/bin/sh
set -eu

ROOT_DIR="$(CDPATH='' cd -- "$(dirname -- "$0")/.." && pwd)"
PRODUCT_ID="${1:-10617}"
LABEL="${2:-freeze}"

output="$("$ROOT_DIR/tools/collect-evening-integration-evidence.sh" "$PRODUCT_ID" "$LABEL")"
printf '%s\n' "$output"

freeze_dir="$(printf '%s\n' "$output" | awk '/^Evidence saved to / {print substr($0, 19)}' | tail -n 1)"
if [ -z "$freeze_dir" ]; then
    echo "FAIL could not determine freeze evidence directory" >&2
    exit 1
fi

"$ROOT_DIR/tools/print-evening-integration-summary.sh" "$PRODUCT_ID" >"$freeze_dir/freeze-summary.txt"
cp "$ROOT_DIR/docs/handoff/09-evening-integration-state.md" "$freeze_dir/current-evening-state.md"
cp "$ROOT_DIR/docs/handoff/10-evening-command-sequence.md" "$freeze_dir/command-sequence.md"
cp "$ROOT_DIR/docs/handoff/11-evening-decision-matrix.md" "$freeze_dir/decision-matrix.md"

echo "Freeze snapshot saved to $freeze_dir"
