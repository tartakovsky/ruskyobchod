## Summary

Locked in a no-pause execution rule for this workstream.

## Rule

After each completed step, immediately start the next admissible step without waiting for a new user prompt.

## Only valid blockers

- a reproduced live regression that must be stabilized first
- missing access required to continue
- no safe next step with a verifiable path

## Not valid reasons to pause

- waiting for confirmation after a successful step
- waiting because one bounded task finished
- waiting for a reminder to continue

## Operating form

1. finish one bounded step
2. verify it
3. record it in source-of-truth
4. immediately take the next bounded step

## Reason

The work was losing momentum because completion of one step was treated like a stopping point instead of a handoff into the next step.
