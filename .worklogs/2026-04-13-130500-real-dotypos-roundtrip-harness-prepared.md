## real dotypos roundtrip harness prepared

### scope

Prepare, but do not automatically execute, a controlled real-stock Dotypos roundtrip step for evening integration work.

Files added:

- `.plans/2026-04-13-real-dotypos-roundtrip-mini-plan.md`
- `tools/real-dotypos-roundtrip.sh`

### safety shape

The script is guarded and refuses to run unless:

- `REALLY_MUTATE_DOTYPOS=1`

So it cannot accidentally mutate live Dotypos stock during daytime preparation.

### intended use

If needed in the evening, the harness will:

1. read remote stock
2. subtract a very small quantity
3. read remote stock again
4. restore the exact same quantity
5. read final stock
6. fail if final stock does not equal the initial stock

### current status

Only the safety envelope and syntax were verified now.

No real Dotypos stock mutation was performed during this preparation step.
