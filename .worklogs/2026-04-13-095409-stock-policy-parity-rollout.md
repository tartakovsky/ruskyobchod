## stock-policy parity rollout

- target file: `wordpress/wp-content/mu-plugins/rusky-stock-policy.php`
- reason: live-vs-repo MU gap remained after preorder/order-language/stock-normalization parity work
- rule followed: one file deploy only

## pre-rollout proof

- live file was missing:
  - `rusky-stock-policy.php`
- repo file syntax checked on remote host with `php -l`
- runtime probe before deploy:
  - `rsp_*` functions absent
  - fallback `gastronom_*` wrappers still present

## rollout

- deployed only `wordpress/wp-content/mu-plugins/rusky-stock-policy.php`

## post-rollout runtime proof

- `rsp_setup_decimal_stock_amount=true`
- `rsp_disable_stock_notifications=true`
- `rsp_disable_stock_email_actions=true`
- `rsp_detach_dotypos_product_updated_hook=true`
- `rsp_adjust_rest_endpoints_for_decimal_stock=true`
- `rsp_run_stock_fix_repair_once=true`
- compatibility wrappers remained present:
  - `gastronom_setup_decimal_stock_amount=true`
  - `gastronom_disable_stock_notifications=true`
  - `gastronom_run_stock_fix_repair_once=true`

## post-rollout verification

All six verification contours stayed green:

- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
- `tools/verify-account-shell.sh`
- `tools/verify-commerce-shell.sh`
- `tools/verify-commerce-shell-sk.sh`
- `tools/verify-preorder-shell.sh`

## result

- stock-policy owner gap is now closed on live
- remaining live-vs-repo MU gap is now reduced to language-runtime files only:
  - `rusky-language-switcher-lite.php`
  - `rusky-runtime-shim.php`
  - `rusky-server-language-core.php`
- next step should not be a blind parity batch; it needs a dedicated language-runtime mini-plan
