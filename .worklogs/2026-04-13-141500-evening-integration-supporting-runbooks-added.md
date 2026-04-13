## evening integration supporting runbooks added

### scope

Add the missing support pieces around the guarded evening mutation path.

Files added:

- `tools/verify-dotypos-product-state.sh`
- `.plans/2026-04-13-evening-integration-rollback-runbook.md`
- `docs/handoff/09-evening-integration-state.md`

### what this closes

Before this step, the evening path had:

- gate
- guarded mutation command
- orchestration script

But it still lacked:

- one exact post-mutation product-state verifier
- one explicit rollback order
- one short current-state file dedicated to the evening window

### verification

`tools/verify-dotypos-product-state.sh 10617` currently reports:

- local `_stock=9`
- local `_gastronom_cash_stock_kg=3.64`
- remote `stockQuantityStatus=3.64`
- exact local/remote cash-stock match

### result

The evening integration path is now covered by:

- go/no-go runner
- guarded mutation runner
- product-state verifier
- rollback runbook
- concise current-state summary
