# 2026-04-12 07:55:00 CET

- Scope: bounded no-behavior-change refactor in `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`
- Added `gls_cookie_notice_button_pairs()` to isolate cookie notice button replacement maps
- Reused helper in `gls_normalize_cookie_notice_button_html()`
- Validation before deploy:
  - `git diff --check`
  - `./tools/verify-storefront-baseline.sh`
  - `./tools/verify-checkout-shell.sh`
