#!/bin/sh
set -eu

ROOT_DIR="$(CDPATH='' cd -- "$(dirname -- "$0")/.." && pwd)"

run() {
    label="$1"
    shift
    echo "== $label =="
    "$@"
    echo
}

run "storefront baseline" "$ROOT_DIR/tools/verify-storefront-baseline.sh"
run "checkout shell" "$ROOT_DIR/tools/verify-checkout-shell.sh"
run "account shell" "$ROOT_DIR/tools/verify-account-shell.sh"
run "commerce shell RU" "$ROOT_DIR/tools/verify-commerce-shell.sh"
run "commerce shell SK" "$ROOT_DIR/tools/verify-commerce-shell-sk.sh"
run "preorder shell" "$ROOT_DIR/tools/verify-preorder-shell.sh"
run "admin order screen" "$ROOT_DIR/tools/verify-admin-order-screen.sh"
run "order page language" "$ROOT_DIR/tools/verify-order-page-language.sh"
run "language runtime surface" "$ROOT_DIR/tools/verify-language-runtime-surface.sh"
run "live bootstrap surface" "$ROOT_DIR/tools/verify-live-bootstrap-surface.sh"
run "elementor pair compat" "$ROOT_DIR/tools/verify-elementor-pair-compat.sh"
run "dotypos readonly" "$ROOT_DIR/tools/verify-dotypos-readonly.sh"
run "dotypos action scheduler" "$ROOT_DIR/tools/verify-dotypos-action-scheduler.sh"
run "elementor pro readonly" "$ROOT_DIR/tools/verify-elementor-pro-readonly.sh"

echo "Tuesday readiness verification complete."
