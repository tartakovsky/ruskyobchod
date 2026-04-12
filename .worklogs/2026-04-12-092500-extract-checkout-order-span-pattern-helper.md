# 2026-04-12 09:25:00 CET

- Scope: bounded no-behavior-change refactor in `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`
- Added `gls_checkout_order_title_span_pattern()` to isolate breadcrumb `<span>` regex pattern for checkout order shell normalization
- Reused helper in both RU and SK branches of `gls_normalize_checkout_order_title_shell_html()`
- Validation before deploy:
  - `git diff --check`
  - `./tools/verify-storefront-baseline.sh`
  - `./tools/verify-checkout-shell.sh`
