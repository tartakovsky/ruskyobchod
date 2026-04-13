#!/bin/sh
set -eu

ROOT_DIR="$(CDPATH='' cd -- "$(dirname -- "$0")/.." && pwd)"

PRODUCT_ID="${1:-10617}"
DELTA="${2:-0.01}"

run_and_capture_dir() {
    label="$1"
    shift

    echo "== $label =="
    output="$("$@")"
    printf '%s\n' "$output"
    dir="$(printf '%s\n' "$output" | awk '/^Evidence saved to / {print substr($0, 19)}' | tail -n 1)"
    if [ -z "$dir" ]; then
        echo "FAIL could not capture evidence directory for $label" >&2
        exit 1
    fi
    echo
    LAST_CAPTURED_DIR="$dir"
}

run_step() {
    label="$1"
    shift
    echo "== $label =="
    "$@"
    echo
}

LAST_CAPTURED_DIR=""

run_and_capture_dir "precheck evidence" "$ROOT_DIR/tools/collect-evening-integration-evidence.sh" "$PRODUCT_ID" precheck
PRECHECK_DIR="$LAST_CAPTURED_DIR"

if [ "${REALLY_MUTATE_DOTYPOS:-0}" != "1" ]; then
    echo "Mutation step skipped."
    echo "Precheck evidence: $PRECHECK_DIR"
    echo "Set REALLY_MUTATE_DOTYPOS=1 to continue through guarded roundtrip and postcheck comparison."
    exit 0
fi

run_step "guarded evening integration" "$ROOT_DIR/tools/run-evening-integration.sh" "$PRODUCT_ID" "$DELTA"

run_and_capture_dir "postcheck evidence" "$ROOT_DIR/tools/collect-evening-integration-evidence.sh" "$PRODUCT_ID" postcheck
POSTCHECK_DIR="$LAST_CAPTURED_DIR"

run_step "evidence comparison" "$ROOT_DIR/tools/compare-evening-evidence.sh" "$PRECHECK_DIR" "$POSTCHECK_DIR"

echo "Evening integration with evidence complete."
