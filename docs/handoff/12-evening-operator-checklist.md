# evening operator checklist

## start

- run `tools/print-evening-integration-summary.sh 10617`
- if `gate_status=red`, stop

## if readonly proof is enough

- run `tools/freeze-evening-integration-state.sh 10617 precheck`
- stop there

## if real write proof is required

Run in this exact order:

1. `tools/freeze-evening-integration-state.sh 10617 precheck`
2. `REALLY_MUTATE_DOTYPOS=1 tools/run-evening-integration-with-evidence.sh 10617 0.01`
3. `tools/freeze-evening-integration-state.sh 10617 postcheck`

## success means

- guarded mutation exits `0`
- post-roundtrip verifier exits `0`
- evidence comparison exits `0`
- final summary still shows:
  - `gate_status=green`
  - `remote_matches_local_cash=true`

## stop immediately if

- gate is red
- mutation exits non-zero
- evidence comparison fails
- product state does not match

## rollback

- use `.plans/2026-04-13-evening-integration-rollback-runbook.md`
