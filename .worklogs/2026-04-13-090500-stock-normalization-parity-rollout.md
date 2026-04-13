## Stock Normalization Parity Rollout

Date: 2026-04-13

### Scope

Controlled parity rollout for:

- `wordpress/wp-content/mu-plugins/rusky-stock-normalization.php`

### Pre-rollout proof

Before deploy on live:

- `rsn_sync_stock_status_after_set_stock=false`
- `rsn_sync_stock_status_after_rest_insert=false`
- `rsn_reconcile_on_updated_post_meta=false`
- `gastronom_sync_stock_status_after_set_stock=true`
- `gastronom_sync_stock_status_after_rest_insert=true`

This proved live still used only the fallback stock-normalization ownership from `gastronom-stock-fix.php`.

### Safety checks

- remote `php -l` passed
- storefront and preorder baselines were green before deploy

### Post-rollout proof

After deploy on live:

- `rsn_normalize_product_name=true`
- `rsn_catalog_policy=true`
- `rsn_reconcile_decimal_stock=true`
- `rsn_apply_catalog_policy=true`
- `rsn_enforce_product_rules=true`

### Verification after deploy

All six verification contours stayed green:

- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
- `tools/verify-account-shell.sh`
- `tools/verify-commerce-shell.sh`
- `tools/verify-commerce-shell-sk.sh`
- `tools/verify-preorder-shell.sh`

### Result

The stock-normalization owner gap on live is now closed.
