#!/bin/sh
set -eu

if [ "$#" -ne 2 ]; then
    echo "Usage: $0 <before-dir> <after-dir>" >&2
    exit 1
fi

BEFORE="$1"
AFTER="$2"

if [ ! -d "$BEFORE" ] || [ ! -d "$AFTER" ]; then
    echo "FAIL both arguments must be existing evidence directories" >&2
    exit 1
fi

compare_file() {
    name="$1"
    before_file="$BEFORE/$name"
    after_file="$AFTER/$name"

    if [ ! -f "$before_file" ] || [ ! -f "$after_file" ]; then
        echo "FAIL missing file for comparison: $name" >&2
        exit 1
    fi

    if cmp -s "$before_file" "$after_file"; then
        echo "OK   $name matches"
        return
    fi

    echo "FAIL $name differs" >&2
    diff -u "$before_file" "$after_file" >&2 || true
    exit 1
}

compare_file "product-state.txt"
compare_file "active-plugins.txt"
compare_file "mu-parity.txt"
compare_file "dotypos-readonly.txt"

echo "Evening evidence comparison complete."
