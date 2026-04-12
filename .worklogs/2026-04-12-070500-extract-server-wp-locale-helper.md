# 2026-04-12 07:05:00 CET

- Scope: bounded no-behavior-change refactor in `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`
- Added `gls_server_wp_locale()` to centralize repeated `ru_RU/sk_SK` selection
- Reused helper in:
  - `gls_frontend_locale()`
  - `gls_wcpay_wp_locale()`
- Validation before deploy:
  - `git diff --check`
  - `./tools/verify-storefront-baseline.sh`
  - `./tools/verify-checkout-shell.sh`
