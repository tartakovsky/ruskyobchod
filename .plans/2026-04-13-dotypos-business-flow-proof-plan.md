# Dotypos Business-Flow Proof Plan

## current position

Completed:

- `gastronom-stock-fix.php` parity rollout
- owner-file parity rollout
- runtime proof that owner functions and critical hooks are active on live

## next proof block

Do not jump directly into broad integration cleanup.

Take one business-flow path at a time:

1. preorder checkout path
2. await-weight order state path
3. weight-confirmation admin/AJAX path
4. confirmed Dotypos stock sync path
5. cancelled/refunded restore path

## execution rule

For each path:

1. define the exact expected state transition
2. prove current live behavior read-only where possible
3. change only one file if a fix is needed
4. verify the exact affected path immediately
5. update handoff/worklog before taking the next path

## stop line

If a path cannot be proven safely without creating or mutating live orders blindly, stop and build a dedicated verification method first.
