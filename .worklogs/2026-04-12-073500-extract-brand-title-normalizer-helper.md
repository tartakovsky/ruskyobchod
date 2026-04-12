# 2026-04-12 07:35:00 CET

- Scope: bounded no-behavior-change refactor in `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`
- Added `gls_normalize_brand_title_text()` to isolate brand-title replacement logic
- Reused helper in `gls_normalize_common_public_title_text()`
- Validation before deploy:
  - `git diff --check`
  - `./tools/verify-storefront-baseline.sh`
  - `./tools/verify-checkout-shell.sh`
