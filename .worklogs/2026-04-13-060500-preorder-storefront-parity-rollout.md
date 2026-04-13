## Preorder Storefront Parity Rollout

Date: 2026-04-13

### Scope

Controlled parity rollout for:

- `wordpress/wp-content/mu-plugins/rusky-preorder-storefront.php`

### Pre-rollout proof

Before deploy on live:

- `rpsf_add_to_cart_validation=false`
- `rpsf_available_payment_gateways=false`
- `rpsf_render_single_product_note=false`
- `gastronom_*` storefront wrappers were still present and active

This proved the live site was still running the old storefront fallback ownership for preorder storefront behavior.

### Safety checks

- remote `php -l` passed
- preorder shell baseline was green before deploy

### Post-rollout proof

After deploy on live:

- `rpsf_add_to_cart_validation=true`
- `rpsf_available_payment_gateways=true`
- `rpsf_render_single_product_note=true`
- `rpsf_checkout_create_order_line_item=true`

### Verification after deploy

All six verification contours stayed green:

- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
- `tools/verify-account-shell.sh`
- `tools/verify-commerce-shell.sh`
- `tools/verify-commerce-shell-sk.sh`
- `tools/verify-preorder-shell.sh`

### Result

The preorder storefront owner gap on live is now closed.
