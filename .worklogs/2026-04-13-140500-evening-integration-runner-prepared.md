## evening integration runner prepared

### scope

Add one orchestrator for the evening integration window.

File added:

- `tools/run-evening-integration.sh`

### behavior

By default it:

1. runs `tools/verify-evening-integration-gate.sh`
2. stops without mutation
3. prints the exact guarded next step

Only if `REALLY_MUTATE_DOTYPOS=1` is set, it will:

1. run the pre-integration gate
2. execute `tools/real-dotypos-roundtrip.sh`
3. run the post-mutation gate again

### current result

The dry-run path was executed successfully:

- full gate passed
- mutation was skipped
- the script stopped on the guard exactly as intended

### value

This removes improvisation from the evening integration window:

- one entrypoint
- one guard
- one post-mutation verification path
