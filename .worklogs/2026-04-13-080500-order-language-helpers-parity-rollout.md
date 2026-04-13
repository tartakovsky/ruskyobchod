## Order Language Helpers Parity Rollout

Date: 2026-04-13

### Scope

Controlled parity rollout for:

- `wordpress/wp-content/mu-plugins/rusky-order-language-helpers.php`

### Pre-rollout proof

Before deploy on live:

- `rslh_order_lang=false`
- `rslh_localize_order_label=false`
- `rslh_with_order_locale=false`
- `gastronom_order_lang=true`
- `gastronom_localize_order_label=true`
- `gastronom_with_order_locale=true`

This proved live still used only the fallback order-language helper ownership in `gastronom-stock-fix.php`.

### Safety checks

- remote `php -l` passed
- checkout shell and account shell baselines were green before deploy

### Post-rollout proof

After deploy on live:

- `rslh_order_lang=true`
- `rslh_localize_order_label=true`
- `rslh_with_order_locale=true`
- `rslh_localize_title=true`

### Verification after deploy

All six verification contours stayed green:

- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
- `tools/verify-account-shell.sh`
- `tools/verify-commerce-shell.sh`
- `tools/verify-commerce-shell-sk.sh`
- `tools/verify-preorder-shell.sh`

### Result

The order-language helper owner gap on live is now closed.
