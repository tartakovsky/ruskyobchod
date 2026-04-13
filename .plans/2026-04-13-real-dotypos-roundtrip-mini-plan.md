# real dotypos roundtrip mini-plan

## purpose

Prepare one controlled reversible real-stock mutation step for Dotypos, only if it is still needed in the evening.

This is not a default daytime action.

## why it is separated

- read-only Dotypos connectivity is already proven
- in-memory mutation logic is already proven
- the remaining uncertainty is only the real external stock roundtrip

That makes this a narrow, high-risk, high-signal step.

## allowed scope

- one real preorder product only
- one tiny reversible quantity only
- immediate restore in the same controlled script
- exact before/after capture for:
  - remote stock
  - local `_gastronom_cash_stock_kg`
  - local `_stock`

## preconditions

Before running a real mutation:

- `tools/verify-tuesday-readiness.sh` must be green
- `tools/verify-dotypos-readonly.sh` must be green
- choose one product with stable cash stock
- capture exact before-state locally and remotely
- no concurrent manual stock edits

## allowed candidate

Default candidate:

- product `10617`

Reason:

- already used repeatedly in controlled proofs
- mapping and readonly checks are positive
- current stock state is known and stable

## execution shape

1. read remote stock
2. subtract a very small reversible quantity
3. read remote stock again
4. restore exactly the same quantity
5. read remote stock final
6. verify final stock equals initial stock
7. verify local cash/piece stock is still aligned or reconcile if the tested path requires it

## stop conditions

Abort immediately if:

- remote stock read fails
- update call fails
- final stock does not return to initial value
- local/remote mismatch remains after intended restore

## rollback rule

- no second experiment before first mismatch is resolved
- if mismatch appears, restore only the tested quantity first
- then re-run readonly verifier
- then re-run Tuesday readiness runner
