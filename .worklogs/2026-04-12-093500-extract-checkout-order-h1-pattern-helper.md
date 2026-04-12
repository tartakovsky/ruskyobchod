# 2026-04-12 09:35:00 CET

- Scope: bounded no-behavior-change refactor in `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`
- Added `gls_checkout_order_title_h1_pattern()` to isolate `<h1 class="vw-page-title">` regex pattern for checkout order shell normalization
- Reused helper in both RU and SK branches of `gls_normalize_checkout_order_title_shell_html()`
- Validation before deploy:
  - `git diff --check`
  - `./tools/verify-storefront-baseline.sh`
  - `./tools/verify-checkout-shell.sh`
