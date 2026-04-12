# 2026-04-12 06:55:00 CET

- Scope: bounded no-behavior-change refactor in `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`
- Added `gls_server_locale_code()` to centralize repeated `ru/sk` locale selection from `gls_server_lang()`
- Reused helper in:
  - `wcpay_elements_options`
  - `wc_stripe_elements_options`
  - `gls_frontend_locale()`
  - `gls_wcpay_locale_pair()`
  - `gls_wcpay_wp_locale()`
- Validation before deploy:
  - `git diff --check`
  - `./tools/verify-storefront-baseline.sh`
  - `./tools/verify-checkout-shell.sh`
