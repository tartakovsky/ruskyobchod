# evening integration state

## current go/no-go command

- `tools/verify-evening-integration-gate.sh`

Current expected result:

- green

## current mutation command

Only if still needed in the evening:

- `REALLY_MUTATE_DOTYPOS=1 tools/real-dotypos-roundtrip.sh 10617 0.01`

## current orchestrated command

Dry-run safe by default:

- `tools/run-evening-integration.sh 10617 0.01`

Real guarded path:

- `REALLY_MUTATE_DOTYPOS=1 tools/run-evening-integration.sh 10617 0.01`

## current post-roundtrip verifier

- `tools/verify-post-roundtrip-state.sh 10617`

## current evidence capture command

- `tools/collect-evening-integration-evidence.sh 10617 precheck`

## current summary command

- `tools/print-evening-integration-summary.sh 10617`

## current freeze command

- `tools/freeze-evening-integration-state.sh 10617 precheck`

## current validated assumptions

- storefront / checkout / account / commerce / preorder paths are green
- admin order screen is green
- RU/SK order pages are green
- Dotypos readonly connectivity is green
- live PHP syntax is green
- live MU parity has only one deferred local-only file:
  - `rusky-runtime-shim.php`
- `real-time-find-and-replace` is not active
- `gastronom-lang-switcher` is active
- `woocommerce-extension-master/dotypos.php` is active

## known deferred drift

- `rusky-commerce-adjustments.php` on live does not hash-match repo
- current commerce behavior still works through `gastronom_*` fallback ownership in `gastronom-stock-fix.php`
- do not parity-deploy this file blindly before the integration window

## deferred item

- `rusky-runtime-shim.php`

It remains deferred unless a new runtime defect or integration-specific need appears.

## rollback note

If the evening roundtrip is not clean, use:

- `.plans/2026-04-13-evening-integration-rollback-runbook.md`

## command sequence

Use the exact evening order from:

- `docs/handoff/10-evening-command-sequence.md`

## decision matrix

Use the go/skip/mutate criteria from:

- `docs/handoff/11-evening-decision-matrix.md`

## operator checklist

Use the short operator checklist from:

- `docs/handoff/12-evening-operator-checklist.md`
