#!/bin/sh
set -eu

ROOT_DIR="$(CDPATH='' cd -- "$(dirname -- "$0")/.." && pwd)"

tmp_mu="$(mktemp)"
tmp_plugins="$(mktemp)"
trap 'rm -f "$tmp_mu" "$tmp_plugins"' EXIT

run() {
    label="$1"
    shift
    echo "== $label =="
    "$@"
    echo
}

run "tuesday readiness" "$ROOT_DIR/tools/verify-tuesday-readiness.sh"
run "live php syntax" "$ROOT_DIR/tools/verify-live-php-syntax.sh"

echo "== live mu parity =="
"$ROOT_DIR/tools/audit-live-mu-parity.sh" | tee "$tmp_mu"
echo

echo "== active plugins snapshot =="
"$ROOT_DIR/tools/capture-live-active-plugins.sh" | tee "$tmp_plugins"
echo

if grep -q '^real-time-find-and-replace/real-time-find-and-replace\.php$' "$tmp_plugins"; then
    echo "FAIL FAR is active on live; runtime-shim defer assumption is no longer valid" >&2
    exit 1
fi
echo "OK   FAR inactive on live"

if ! grep -q '^gastronom-lang-switcher/gastronom-lang-switcher\.php$' "$tmp_plugins"; then
    echo "FAIL main language plugin is not active on live" >&2
    exit 1
fi
echo "OK   main language plugin active on live"

if ! grep -q '^woocommerce-extension-master/dotypos\.php$' "$tmp_plugins"; then
    echo "FAIL Dotypos plugin is not active on live" >&2
    exit 1
fi
echo "OK   Dotypos plugin active on live"

if ! grep -q '^== local-only ==$' "$tmp_mu"; then
    echo "FAIL could not parse MU parity output" >&2
    exit 1
fi

local_only="$(awk '
  /^== local-only ==$/ {mode="local"; next}
  /^== remote-only ==$/ {mode=""; next}
  mode=="local" && NF {print}
' "$tmp_mu")"

remote_only="$(awk '
  /^== remote-only ==$/ {mode="remote"; next}
  /^== shared ==$/ {mode=""; next}
  mode=="remote" && NF {print}
' "$tmp_mu")"

if [ -n "$remote_only" ]; then
    echo "FAIL remote-only MU files detected:" >&2
    echo "$remote_only" >&2
    exit 1
fi
echo "OK   no remote-only MU files"

if [ "$local_only" != "rusky-runtime-shim.php" ]; then
    echo "FAIL unexpected local-only MU drift:" >&2
    printf '%s\n' "$local_only" >&2
    exit 1
fi
echo "OK   only deferred local-only MU gap is rusky-runtime-shim.php"

echo
echo "OK   admin order screen verification is included via tuesday readiness"
echo
echo "Evening integration gate verification complete."
