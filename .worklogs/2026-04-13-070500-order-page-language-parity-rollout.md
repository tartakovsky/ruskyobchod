## Order Page Language Parity Rollout

Date: 2026-04-13

### Scope

Controlled parity rollout for:

- `wordpress/wp-content/mu-plugins/rusky-order-page-language.php`

### Pre-rollout proof

Before deploy on live:

- `ropl_localize_order_item_name=false`
- `ropl_render_forced_order_lang_marker=false`
- `ropl_maybe_redirect_context_order_lang=false`
- `ropl_start_order_page_buffer=false`
- `gastronom_localize_order_item_name=true`

This proved live was still using only the fallback ownership in `gastronom-stock-fix.php` for order-page language behavior.

### Safety checks

- remote `php -l` passed
- preorder shell and checkout shell baselines were green before deploy

### Post-rollout proof

After deploy on live:

- `ropl_localize_order_item_name=true`
- `ropl_render_forced_order_lang_marker=true`
- `ropl_maybe_redirect_context_order_lang=true`
- `ropl_start_order_page_buffer=true`
- `ropl_render_order_item_actual_weight=true`

### Verification after deploy

All six verification contours stayed green:

- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
- `tools/verify-account-shell.sh`
- `tools/verify-commerce-shell.sh`
- `tools/verify-commerce-shell-sk.sh`
- `tools/verify-preorder-shell.sh`

### Result

The order-page language owner gap on live is now closed.
