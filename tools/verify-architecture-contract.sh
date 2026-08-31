#!/bin/sh
#
# Verifies the production contracts that architecture-only changes must preserve.
#
# This runner has no deploy or Dotypos mutation command. Some underlying checks
# create temporary WooCommerce proof orders; each owns and cleans up its own
# fixtures. A real Dotypos stock mutation remains opt-in through the separate
# REALLY_MUTATE_DOTYPOS workflow documented in docs/handoff.

set -eu

ROOT_DIR="$(CDPATH='' cd -- "$(dirname -- "$0")/.." && pwd)"

run() {
    label="$1"
    shift
    printf '\n== %s ==\n' "$label"
    "$@"
}

# Customer-facing and order-lifecycle contracts are explicit so that an
# individual failed contour can never be hidden by a nested aggregate runner.
run 'storefront baseline' "$ROOT_DIR/tools/verify-storefront-baseline.sh"
run 'checkout shell' "$ROOT_DIR/tools/verify-checkout-shell.sh"
run 'account shell' "$ROOT_DIR/tools/verify-account-shell.sh"
run 'commerce shell RU' "$ROOT_DIR/tools/verify-commerce-shell.sh"
run 'commerce shell SK' "$ROOT_DIR/tools/verify-commerce-shell-sk.sh"
run 'preorder shell' "$ROOT_DIR/tools/verify-preorder-shell.sh"
run 'admin order screen' "$ROOT_DIR/tools/verify-admin-order-screen.sh"
run 'order-page language' "$ROOT_DIR/tools/verify-order-page-language.sh"
run 'language runtime surface' "$ROOT_DIR/tools/verify-language-runtime-surface.sh"
run 'live bootstrap surface' "$ROOT_DIR/tools/verify-live-bootstrap-surface.sh"
run 'runtime plugin policy' "$ROOT_DIR/tools/verify-plugin-runtime-policy.sh"
run 'Elementor pair compatibility' "$ROOT_DIR/tools/verify-elementor-pair-compat.sh"

# Dotypos and fiscalization contract. The confirmation proof intercepts its
# external calls; the remaining verifiers are read-only.
run 'Dotypos readonly state' "$ROOT_DIR/tools/verify-dotypos-readonly.sh"
run 'Dotypos Action Scheduler' "$ROOT_DIR/tools/verify-dotypos-action-scheduler.sh"
run 'Dotypos owner contract' "$ROOT_DIR/tools/verify-dotypos-owner-contract.sh"
run 'confirmed-weight fiscal contract' "$ROOT_DIR/tools/prove-admin-weight-confirmation.sh"
run 'fiscal receipt and retry contract' "$ROOT_DIR/tools/verify-dotypos-fiscalization.sh"

# Production integrity and safety boundaries.
run 'MU-plugin filename parity' "$ROOT_DIR/tools/audit-live-mu-parity.sh"
run 'critical file hash parity' "$ROOT_DIR/tools/verify-live-critical-file-hashes.sh"
run 'live PHP syntax' "$ROOT_DIR/tools/verify-live-php-syntax.sh"
run 'security surface' "$ROOT_DIR/tools/verify-live-security-surface.sh"

printf '\nArchitecture contract verification complete.\n'
