## Summary

Close the current preorder order-admin/payment-owner block in the files that actually own it:

- `wordpress/wp-content/mu-plugins/rusky-preorder-admin.php`
- `wordpress/wp-content/mu-plugins/rusky-preorder-storefront.php`

## Why

The current live issues are coupled and come from owner-layer behavior, not from isolated text defects:

1. The admin metabox is still rendered after weight confirmation, which produces a misleading "no weighted items" box.
2. Woo order items still expose the manual `calculate-action` control even though this preorder flow recalculates automatically inside the confirmation action.
3. `order-pay` still re-offers payment methods for preorder orders instead of honoring the payment method already chosen at checkout.

## Change

### `rusky-preorder-admin.php`

- Added current-order helpers for admin order screens.
- Added a dedicated predicate for whether the weight-confirmation panel should exist at all.
- Register the metabox only when the current order still requires weight confirmation.
- Stop rendering fallback text when the panel no longer applies.
- Hide Woo's `button.calculate-action` only for preorder orders on the admin order screen via a stable selector, not by matching translated button text.

### `rusky-preorder-storefront.php`

- Added a helper to resolve the current `order-pay` order.
- Reused that helper in preorder-context detection.
- Locked preorder `order-pay` to the order's already selected gateway.
- Kept `bacs` removed globally.
- Kept localized `cod` title.

## Intended Result

- No stale weight-confirmation box after confirmation.
- No misleading recalculate button in preorder admin orders.
- No second payment-method choice on preorder `order-pay`.

