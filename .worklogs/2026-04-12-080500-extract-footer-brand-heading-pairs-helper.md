# 2026-04-12 08:05:00 CET

- Scope: bounded no-behavior-change refactor in `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`
- Added `gls_footer_brand_heading_pairs()` to isolate footer brand heading replacement map
- Reused helper in `gls_normalize_footer_brand_heading_html()`
- Validation before deploy:
  - `git diff --check`
  - `./tools/verify-storefront-baseline.sh`
  - `./tools/verify-checkout-shell.sh`
