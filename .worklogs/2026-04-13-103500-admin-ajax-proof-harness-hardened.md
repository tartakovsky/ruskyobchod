## admin ajax proof harness hardened

### scope

Add a reusable live proof harness for `gastronom_confirm_weight` that does not leave temporary orders or stock/cash drift behind.

File added:

- `tools/prove-admin-weight-confirmation.sh`

### problem being solved

The earlier one-off CLI proof confirmed success from the live handler, but cleanup was not guaranteed because `wp_send_json_success()` terminated execution immediately.

That forced a manual cleanup step after the proof.

### harness design

The new harness:

- uploads a temporary PHP proof script to the live host
- creates a controlled temporary preorder order
- stubs Dotypos service calls in memory
- intercepts mail through `pre_wp_mail`
- traps both `wp_die_handler` and `wp_die_ajax_handler`
- restores stock/cash/meta state in guaranteed cleanup
- deletes the temporary order in guaranteed cleanup
- prints structured JSON summary instead of raw handler output only

### live proof results

`cod` run:

- returned success JSON
- `status_after=on-hold`
- `requires_confirmation_after=no`
- `weight_confirmed_after=yes`
- `cash_synced_after=yes`
- one mail call intercepted
- Dotypos stub recorded update + stock read

`bacs` run:

- returned success JSON
- `status_after=pending`
- `requires_confirmation_after=no`
- `weight_confirmed_after=yes`
- `cash_synced_after=yes`
- one mail call intercepted
- Dotypos stub recorded update + stock read

### cleanup proof

After the harness runs:

- no temporary `proof@example.com` orders remain
- product `10617` stock returned to:
  - `_stock=9`
  - `_gastronom_cash_stock_kg=3.64`

### verification after proof

All six verification contours remained green:

- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
- `tools/verify-account-shell.sh`
- `tools/verify-commerce-shell.sh`
- `tools/verify-commerce-shell-sk.sh`
- `tools/verify-preorder-shell.sh`

### result

The admin/AJAX preorder confirmation path is now backed by a reusable cleanup-safe live proof harness instead of a one-off script.
