## Summary

Added a decision matrix for the evening integration window.

## Changes

- added `docs/handoff/11-evening-decision-matrix.md`
- separated four cases:
  - gate red
  - gate green and readonly proof sufficient
  - gate green and real write proof required
  - mutation not clean

## Result

The evening window now has an explicit rule for when to skip real mutation and when to execute it.
