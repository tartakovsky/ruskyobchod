# 2026-04-12 09:55:00 CET

- Scope: bounded no-behavior-change refactor in `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`
- Added `gls_normalize_checkout_order_title_span_html()` to centralize breadcrumb `<span>` normalization for checkout order shell
- Reused helper in both RU and SK branches of `gls_normalize_checkout_order_title_shell_html()`
- Validation before deploy:
  - `git diff --check`
  - `./tools/verify-storefront-baseline.sh`
  - `./tools/verify-checkout-shell.sh`
