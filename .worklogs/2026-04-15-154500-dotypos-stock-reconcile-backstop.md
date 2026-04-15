# Dotypos Stock Reconcile Backstop

## Goal
- Fix the partial `cash -> site` stock drift discovered on 2026-04-15.
- Keep the cash register as the source of truth for stock.
- Add a narrow server-side backstop for missed movement webhook updates.

## Constraints
- Follow handoff workflow: repo-first, minimal deploy, immediate live verification.
- Do not touch vendor Dotypos code.
- Do not change `site -> Dotypos` behavior.
- Do not affect preorder confirmation logic except through the existing stock bridge helper.

## Plan
1. Add a small `mu-plugin` that performs a quiet bulk stock reconcile from Dotypos to Woo.
2. Reuse the existing paired `dotypos_product_id` mapping and the existing preorder-aware helper when present.
3. Schedule the reconcile as a recurring Action Scheduler backstop with an overlap lock.
4. Deploy the single new file, run one immediate reconcile, and compare the previously drifted products again.
