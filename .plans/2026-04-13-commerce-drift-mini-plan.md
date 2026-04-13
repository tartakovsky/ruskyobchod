# commerce drift mini-plan

## current finding

`rusky-commerce-adjustments.php` on live does not hash-match repo.

## what is already known

- live behavior for COD fee / payment description / checkout refresh still works
- runtime audit shows the active live hooks are currently held by:
  - `gastronom_add_cod_fee`
  - `gastronom_gateway_description`
  - `gastronom_render_checkout_payment_refresh_script`
- those hooks are owned by `gastronom-stock-fix.php` fallback on live, not by the newer `rca_*` owner path

## why this is deferred

- the drift is real
- but current commerce behavior is green in all existing gates
- blindly parity-deploying `rusky-commerce-adjustments.php` before the integration window would change runtime ownership, not just file parity

## allowed next step

Only after the integration window, or earlier only if commerce behavior regresses:

1. capture exact live file diff
2. map each differing function/hook
3. prove who currently owns the active hook on live
4. deploy one-file parity only if the hook move is intentional
5. re-run:
   - `tools/verify-commerce-shell.sh`
   - `tools/verify-commerce-shell-sk.sh`
   - `tools/verify-evening-integration-gate.sh`

## forbidden step before integration

- do not parity-deploy `rusky-commerce-adjustments.php` just to make hashes green
