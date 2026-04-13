## Summary

Collapsed the evening integration execution order into one short command-sequence handoff document.

## Changes

- added `docs/handoff/10-evening-command-sequence.md`
- updated `docs/handoff/09-evening-integration-state.md` to point at the sequence doc
- updated rollback runbook to preserve precheck evidence and capture rollback evidence

## Result

The evening window now has one explicit execution order:

1. precheck evidence
2. guarded mutation
3. postcheck evidence
4. rollback sequence if needed
