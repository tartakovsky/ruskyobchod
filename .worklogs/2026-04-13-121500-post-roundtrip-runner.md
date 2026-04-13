## Summary

Added a single post-roundtrip verifier and switched the evening orchestration script to use it instead of calling only the generic gate after mutation.

## Changes

- added `tools/verify-post-roundtrip-state.sh`
- the new runner verifies:
  - exact Dotypos/local product state for the chosen product
  - readonly Dotypos health
  - the full evening integration gate
- updated `tools/run-evening-integration.sh` to call the new post-roundtrip runner after the guarded mutation step

## Result

The evening integration flow now has a dedicated post-mutation verification phase, not just a generic rerun of the pre-mutation gate.
