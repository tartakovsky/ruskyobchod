# evening integration rollback runbook

## use case

Only for the evening integration window if a real Dotypos roundtrip is executed and the result is not clean.

## clean success looks like

1. `tools/verify-evening-integration-gate.sh` is green before mutation
2. `REALLY_MUTATE_DOTYPOS=1 tools/real-dotypos-roundtrip.sh 10617 0.01` exits `0`
3. roundtrip JSON shows:
   - `restored: true`
   - `after_back_qty == before_qty`
4. `tools/verify-dotypos-product-state.sh 10617` is green
5. `tools/verify-evening-integration-gate.sh` is green again

## rollback triggers

Rollback immediately if any of these happens:

- roundtrip script fails
- `restored` is false
- final remote stock does not equal initial remote stock
- `tools/verify-dotypos-product-state.sh 10617` fails
- post-mutation gate fails

## rollback order

1. Stop. Do not run a second mutation.
2. Keep the `precheck` evidence bundle for comparison.
3. Run the readonly product-state check:
   - `tools/verify-dotypos-product-state.sh 10617`
4. Run the readonly global verifier:
   - `tools/verify-dotypos-readonly.sh`
5. If the remote stock still differs from the initial known value, run one exact compensating restore:
   - same product
   - exact missing delta only
6. Re-run:
   - `tools/verify-dotypos-product-state.sh 10617`
   - `tools/verify-evening-integration-gate.sh`
7. Capture a fresh evidence bundle after rollback:
   - `tools/collect-evening-integration-evidence.sh 10617 rollback`

## hard rules

- do not improvise with a larger second mutation
- do not switch products mid-incident
- do not touch `rusky-runtime-shim.php` during rollback
- do not patch storefront/runtime while stock parity is unresolved

## current default test product

- `10617`

Reason:

- repeatedly proven in controlled flows
- mapping is live
- current stock state is known
