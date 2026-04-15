## Context

The readiness pack already covered:
- storefront / account / checkout
- preorder / order pages
- readonly Dotypos product-state verification
- server-first language runtime verification on audited critical pages

One remaining blind spot was background Dotypos queue state in Action Scheduler.

This is important because a store can look healthy in the browser while a hidden `pending` / `in-progress` queue drifts in the background.

## Change

Added new verifier:
- [`tools/verify-dotypos-action-scheduler.sh`](/Users/alexandertartakovsky/ruskyobchod/tools/verify-dotypos-action-scheduler.sh)

It checks on live:
- no `pending` Dotypos actions
- no `in-progress` Dotypos actions
- no recent failed Dotypos actions in the last 3 days
- prints latest Dotypos action rows for operator visibility

Also updated:
- [`tools/verify-tuesday-readiness.sh`](/Users/alexandertartakovsky/ruskyobchod/tools/verify-tuesday-readiness.sh)

so the main readiness pack now includes this scheduler guard.

## Verification

Green:
- `./tools/verify-dotypos-action-scheduler.sh`
- `./tools/verify-tuesday-readiness.sh`

Observed live scheduler state:
- `pending=0`
- `failed_recent=0`

Latest visible Dotypos actions are historical complete/failed rows from March and one complete overwrite action from April 14.

## Result

The control loop is stronger now:
- browser-visible flows are green
- stock state is green
- server-first language runtime is green on audited critical pages
- Dotypos background queue is also green

This reduces the chance of calling the store “healthy” while a hidden async Dotypos problem is building in the background.
