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

## current validated assumptions

- storefront / checkout / account / commerce / preorder paths are green
- admin order screen is green
- RU/SK order pages are green
- Dotypos readonly connectivity is green
- live MU parity has only one deferred local-only file:
  - `rusky-runtime-shim.php`
- `real-time-find-and-replace` is not active
- `gastronom-lang-switcher` is active
- `woocommerce-extension-master/dotypos.php` is active

## deferred item

- `rusky-runtime-shim.php`

It remains deferred unless a new runtime defect or integration-specific need appears.

## rollback note

If the evening roundtrip is not clean, use:

- `.plans/2026-04-13-evening-integration-rollback-runbook.md`
