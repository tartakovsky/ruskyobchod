## Summary

Added a comparator for precheck/postcheck evidence bundles.

## Changes

- added `tools/compare-evening-evidence.sh`
- compares:
  - `product-state.txt`
  - `active-plugins.txt`
  - `mu-parity.txt`
  - `dotypos-readonly.txt`

## Result

After a real evening roundtrip, the before/after evidence pair can now be validated by one command instead of manual file inspection.
