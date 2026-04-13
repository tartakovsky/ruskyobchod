# 2026-04-13 19:49:27 - Remove bank transfer and unify preorder payment flow

## Goal
- remove `bacs` from the storefront completely
- stop restricting payment methods based on preorder/weight items
- keep preorder confirmation emails aligned only to the already chosen payment method

## Changes
- `rusky-preorder-storefront.php`
  - removed preorder-only gateway filtering
  - now only unsets `bacs`
  - updated preorder checkout notice text to refer to the chosen payment method instead of bank transfer
- `gastronom-stock-fix.php`
  - removed fallback preorder gateway restriction logic
  - now only unsets `bacs` globally on the frontend
  - removed preorder-specific `bacs` description override
- `rusky-preorder-notifications.php`
  - removed special `bacs` email branch
  - weight-confirmation email now branches only by `cod` vs `non-cod`
  - updated confirmation note text from "online payment" to "chosen payment method"

## Verification before deploy
- remote syntax:
  - `rusky-preorder-storefront.php`
  - `rusky-preorder-notifications.php`
  - `gastronom-stock-fix.php`
- controlled proofs on current code path:
  - mixed preorder + `cod`
  - mixed preorder + `woocommerce_payments`

## Expected outcome
- no bank transfer option on checkout/order-pay
- preorder carts keep the normal site gateways except `bacs`
- after weight confirmation:
  - `cod` email -> only check/confirm order CTA
  - online payment email -> pay CTA
