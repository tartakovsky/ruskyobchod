# Morning Readiness 2026-04-14

## State at 09:00 target

- core customer flows are working
- RU and SK storefront/checkout/order pages are green
- preorder confirmation email flow is working
- weighted stock on site is aligned with Dotypos remote stock
- current readiness runner is green:
  - `./tools/verify-tuesday-readiness.sh`

## What was fixed overnight

- gross line totals in preorder confirmation email
- order-page footer translation corruption (`OR` vs `ALEBO`)
- preorder admin order screen gross display
- stale test-order stock tail
- Dotypos remote stock drift after test orders
- outdated readiness tooling that still expected `bank transfer`

## Current weighted stock

- `10310` `Ryba Makrela Údeného Chladenia`:
  - `cash_kg=1.48`
  - `_stock=3`
- `10617` `Sleď . Vedro`:
  - `cash_kg=2`
  - `_stock=5`
- `10781` `Slanina "Mercur"`:
  - `cash_kg=0.966`
  - `_stock=3`

## Integration readiness

- safe to proceed to controlled integration work
- current risk is no longer hidden cleanup debt in checkout/order block
- next work should be a deliberate integration run, not more blind refactoring in the already stabilized customer flow
