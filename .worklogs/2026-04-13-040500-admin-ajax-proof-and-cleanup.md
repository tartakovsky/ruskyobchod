## Admin AJAX Proof And Cleanup

Date: 2026-04-13

### Scope

Controlled proof of the weight-confirmation admin AJAX path on live using:

- a temporary preorder order
- a valid nonce
- an administrator user in CLI context
- intercepted mail
- an in-memory Dotypos service stub

### Positive result

`rpa_handle_weight_confirmation_ajax()` returned:

```json
{"success":true,"data":{"message":"ok"}}
```

This is a direct success signal from the live AJAX handler, not a code-only inference.

### Important behavior note

The script used for this proof did not reach its planned cleanup path because `wp_send_json_success()` terminated execution immediately after emitting the JSON response.

That left behind a temporary live artifact:

- temporary order `10975`
- product `10617` changed to:
  - `_stock=8`
  - `_gastronom_cash_stock_kg=3.2`

### Immediate remediation

The leftover artifact was then cleaned up intentionally in a separate targeted step:

- order `10975` deleted permanently
- product `10617` restored to:
  - `_stock=9`
  - `_gastronom_cash_stock_kg=3.64`

### Verification after cleanup

After cleanup, all six verification contours were green again:

- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
- `tools/verify-account-shell.sh`
- `tools/verify-commerce-shell.sh`
- `tools/verify-commerce-shell-sk.sh`
- `tools/verify-preorder-shell.sh`

### Interpretation

The live AJAX confirmation path is now proven to return success under controlled conditions.

However, future CLI proof for this handler must use a cleanup strategy that survives `wp_send_json_*()` termination, or it must clean up in a separate guaranteed step immediately afterward.
