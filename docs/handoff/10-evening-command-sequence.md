# evening command sequence

## 1. precheck snapshot

Run first:

- `tools/collect-evening-integration-evidence.sh 10617 precheck`

Expected result:

- a new timestamped directory under `artifacts/evening-integration/`
- green `evening-gate.txt`
- exact before-state for product `10617`

## 2. guarded real roundtrip

Run only if the precheck bundle is green:

- `REALLY_MUTATE_DOTYPOS=1 tools/run-evening-integration.sh 10617 0.01`

Expected result:

- guarded Dotypos roundtrip exits `0`
- post-roundtrip verification exits `0`

## 3. postcheck snapshot

Run immediately after a clean mutation:

- `tools/collect-evening-integration-evidence.sh 10617 postcheck`

Expected result:

- a second timestamped directory under `artifacts/evening-integration/`
- exact after-state for product `10617`
- green `evening-gate.txt`

Then compare the two bundles:

- `tools/compare-evening-evidence.sh artifacts/evening-integration/<precheck-dir> artifacts/evening-integration/<postcheck-dir>`

Expected result:

- `product-state.txt` matches
- `active-plugins.txt` matches
- `mu-parity.txt` matches
- `dotypos-readonly.txt` matches

## 4. rollback

If mutation is not clean, stop and use:

- `.plans/2026-04-13-evening-integration-rollback-runbook.md`

Do not run a second mutation before rollback is complete.
