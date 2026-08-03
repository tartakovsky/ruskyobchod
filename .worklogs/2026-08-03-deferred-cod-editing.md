# Deferred COD fiscalization and safe order editing

## Requirement

Processing cash-on-delivery orders must remain editable while the shop confirms
availability. Fiscalization must use the final composition, not the checkout
composition.

## Implementation

- COD orders fiscalize only on `completed` status.
- The original WooCommerce/Dotypos warehouse sale still reserves stock at
  checkout and marks the order `cod-awaiting-finalization`.
- Processing orders in that state are editable in WooCommerce admin.
- Quantity changes, additions, and removals mirror their stock delta to
  Dotypos. Weight-preorder products remain owned by their existing confirmation
  workflow.
- On completion, the guarded delayed-fiscalization path creates the final
  receipt and compensates the POS receipt's second stock movement.
- Already fiscalized orders are not reopened for direct composition edits.
- Fiscal retries remain bounded by the shared 24-attempt order counter.

## Production safety

The previous fiscalization module was backed up under
`/home/u595644545/backups/ruskyobchod-2026-08-03-deferred-cod-fiscalization`.
Both live PHP files passed syntax checks.

The live verification confirmed deferred COD editability/finalization hooks,
the retry ceiling, exact order `11398` payload rounding, and all five eligible
sales since 2026-07-01 as paid, uncanceled Dotypos receipts.

The Action Scheduler audit also reported one unrelated historical failed quiet
stock reconciliation action (`61740`, 2026-07-31). Newer quiet reconciliation
actions are completing and no overdue Dotypos actions were present.
