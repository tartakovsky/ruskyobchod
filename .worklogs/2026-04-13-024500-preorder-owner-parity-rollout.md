## Preorder Owner Parity Rollout

Date: 2026-04-13

### Scope

Controlled live rollout for the two critical missing preorder owner files:

- `wordpress/wp-content/mu-plugins/rusky-preorder-notifications.php`
- `wordpress/wp-content/mu-plugins/rusky-preorder-admin.php`

### Safety checks before deploy

- remote `php -l` passed for both files
- rollout performed one file at a time
- after each deploy, all six verification contours were run

### Runtime proof after deploy

Confirmed on live:

- `function_exists('rpn_mark_weight_confirmed_order_ready') === true`
- `function_exists('rpn_send_preorder_created_emails') === true`
- `function_exists('rpa_handle_weight_confirmation_ajax') === true`
- `function_exists('rpa_render_weight_confirmation_box') === true`

### Verification after deploy

All remained green:

- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
- `tools/verify-account-shell.sh`
- `tools/verify-commerce-shell.sh`
- `tools/verify-commerce-shell-sk.sh`
- `tools/verify-preorder-shell.sh`

### Result

The critical preorder owner gap on live is now closed.
