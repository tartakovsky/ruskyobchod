# 2026-04-18 13:02:11 Dotypos sales sync verification hardening

## why

- today's cash-register-to-site comparison briefly looked red for 2 products
- one mismatch was transient and self-healed through the existing reconcile path
- the other mismatch was a false comparison between Woo `_stock` pieces and Dotypos kilogram stock for a weight-preorder product
- verification needed to become reproducible and source-correct instead of relying on ad hoc manual checks

## what changed

- updated `tools/verify-commerce-shell.sh` to match the current RU terms text on checkout
- updated `tools/verify-commerce-shell-sk.sh` to match the current SK checkout shell:
  - selected country value
  - `(optional)` marker
  - current terms wording
- updated `tools/verify-dotypos-action-scheduler.sh` so it fails only on overdue or in-progress Dotypos actions, not on future scheduled actions
- added `tools/report-todays-dotypos-sales-sync.sh`
  - fetches today's Dotypos `order-items` read-only via the live plugin refresh-token flow
  - maps sold products back to Woo
  - compares the correct Woo quantity source:
    - `_stock` for normal products
    - `_gastronom_cash_stock_kg` for weight-preorder products

## live result

- report date: `2026-04-18`
- unique sold products from Dotypos today: `21`
- matched Woo/Dotypos products after verification: `21`
- mismatched products after verification: `0`

## commands / proofs

- `./tools/verify-dotypos-product-state.sh 10608`
- `./tools/report-todays-dotypos-sales-sync.sh 2026-04-18`
- `./tools/verify-dotypos-action-scheduler.sh`

## outcome

- current live conclusion is green: today's cash-register sales have reached the site
- the verification surface now distinguishes real drift from weight-product representation differences
