## Summary

Added a timestamped evidence collector for the evening integration window.

## Changes

- added `tools/collect-evening-integration-evidence.sh`
- the runner saves a timestamped artifact bundle under `artifacts/evening-integration/`
- captured artifacts include:
  - exact Dotypos/local product state
  - readonly Dotypos verifier output
  - active plugins snapshot
  - MU parity snapshot
  - full evening integration gate output
  - current git head
  - product id and timestamp metadata

## Result

The evening integration window now has a reproducible before/after evidence bundle path instead of ad hoc command output.
