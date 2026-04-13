# evening decision matrix

## case 1: gate is not green

Condition:

- `tools/verify-evening-integration-gate.sh` fails

Decision:

- do not run real Dotypos mutation
- fix the failing gate first

## case 2: gate is green and readonly proof is sufficient

Condition:

- `tools/verify-evening-integration-gate.sh` is green
- `tools/verify-dotypos-readonly.sh` is green
- business decision does not require proof of a real external write tonight

Decision:

- skip real mutation
- keep the current integration state
- use `tools/collect-evening-integration-evidence.sh 10617 precheck` as the recorded state

## case 3: gate is green and real write proof is required

Condition:

- `tools/verify-evening-integration-gate.sh` is green
- `tools/verify-dotypos-readonly.sh` is green
- business decision requires proof of a real reversible Dotypos write

Decision:

Run in this exact order:

1. `tools/collect-evening-integration-evidence.sh 10617 precheck`
2. `REALLY_MUTATE_DOTYPOS=1 tools/run-evening-integration.sh 10617 0.01`
3. `tools/collect-evening-integration-evidence.sh 10617 postcheck`
4. `tools/compare-evening-evidence.sh artifacts/evening-integration/<precheck-dir> artifacts/evening-integration/<postcheck-dir>`

Success means:

- roundtrip exits `0`
- post-roundtrip verifier exits `0`
- evidence comparison exits `0`

## case 4: mutation is not clean

Condition:

- mutation fails
- post-roundtrip verifier fails
- evidence comparison fails

Decision:

- stop immediately
- do not run a second mutation
- use `.plans/2026-04-13-evening-integration-rollback-runbook.md`
