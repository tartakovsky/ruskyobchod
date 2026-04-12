# 2026-04-12 08:35:00 CET

- Scope: bounded no-behavior-change refactor in `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`
- Added `gls_normalize_empty_cart_shell_html()` to replace inline closure in `gls_normalize_server_rendered_html()`
- Moved the same sequence of shell normalizers into the helper without changing order
- Validation before deploy:
  - `git diff --check`
  - `./tools/verify-storefront-baseline.sh`
  - `./tools/verify-checkout-shell.sh`
