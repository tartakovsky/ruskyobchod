# 2026-04-12 09:15:00 CET

- Scope: bounded no-behavior-change refactor in `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`
- Added `gls_checkout_order_title_prefix_replacement()` to isolate the direct prefix replacement pair for RU checkout order shell normalization
- Reused helper in RU branch of `gls_normalize_checkout_order_title_shell_html()`
- Validation before deploy:
  - `git diff --check`
  - `./tools/verify-storefront-baseline.sh`
  - `./tools/verify-checkout-shell.sh`
