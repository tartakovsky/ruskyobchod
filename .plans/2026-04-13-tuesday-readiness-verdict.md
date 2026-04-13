# tuesday readiness verdict

Date: 2026-04-13

## verdict

The project is now in a materially stronger position for Tuesday-morning operation.

The site is not "perfectly cleaned", but the integration-critical paths are now verified enough to operate without returning to already-closed blocks blindly.

## what is already positively proven

### storefront / checkout / account / commerce

The following verification contours are green:

- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
- `tools/verify-account-shell.sh`
- `tools/verify-commerce-shell.sh`
- `tools/verify-commerce-shell-sk.sh`
- `tools/verify-preorder-shell.sh`
- `tools/verify-order-page-language.sh`
- `tools/verify-dotypos-readonly.sh`

### preorder lifecycle

Controlled live proof already confirmed:

- checkout-prepared preorder path
- reserve/restore site stock path
- confirmation-ready transition for COD
- confirmation-ready transition for non-COD
- admin AJAX confirmation path

### admin/AJAX confirmation path

This path is now backed by a cleanup-safe reusable harness:

- `tools/prove-admin-weight-confirmation.sh`

Confirmed summaries:

- `cod -> on-hold`
- `bacs -> pending`

The harness also guarantees cleanup of:

- temporary order
- stock/cash state

### Dotypos connectivity

Read-only live proof confirmed:

- Dotypos service is callable
- warehouse mapping exists
- `syncToDotypos=true`
- all 3 live preorder products have valid `dotypos_id`
- local `_gastronom_cash_stock_kg` matches remote `stockQuantityStatus`

This is now backed by:

- `tools/verify-dotypos-readonly.sh`

### one-command readiness run

The combined readiness runner now exists and passes:

- `tools/verify-tuesday-readiness.sh`

### one-command evening integration gate

The higher-level gate runner now also exists and passes:

- `tools/verify-evening-integration-gate.sh`

It confirms in one pass:

- all Tuesday readiness verifiers
- MU parity shape
- active plugin assumptions
- FAR inactivity
- single deferred MU gap only

## what was intentionally deferred

### `rusky-runtime-shim.php`

This is the last live-vs-repo MU gap.

It is intentionally deferred because:

- it changes the runtime control surface
- it filters `option_active_plugins`
- `real-time-find-and-replace` is not currently active on live

So it is not a default Tuesday blocker, and forcing parity would add risk without proven immediate value.

An explicit read-only parity audit now confirms it is the only remaining local-only `rusky-*.php` file:

- `tools/audit-live-mu-parity.sh`

See:

- `.plans/2026-04-13-runtime-shim-mini-plan.md`

## practical operating conclusion

For Tuesday morning, the site should now be treated as:

- operational
- integration-aware
- verified on key customer and preorder paths

Further work should continue, but from controlled mini-plans, not from emergency cleanup instinct.

## prepared next controlled step

If an evening real Dotypos mutation proof is still required, the guarded harness and plan now exist:

- `.plans/2026-04-13-real-dotypos-roundtrip-mini-plan.md`
- `tools/real-dotypos-roundtrip.sh`

The script refuses to run unless:

- `REALLY_MUTATE_DOTYPOS=1`
