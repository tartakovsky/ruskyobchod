## Summary

Added a single orchestration script that chains the evening integration flow with evidence capture.

## Changes

- added `tools/run-evening-integration-with-evidence.sh`
- execution order:
  - precheck evidence bundle
  - guarded evening integration
  - postcheck evidence bundle
  - evidence comparison
- dry-run remains safe by default because the guarded mutation still requires `REALLY_MUTATE_DOTYPOS=1`

## Result

The evening window now has one top-level command that can drive the full flow while preserving before/after evidence automatically.
