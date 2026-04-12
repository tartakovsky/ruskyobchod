# 2026-04-12 08:15:00 CET

- Scope: bounded no-behavior-change refactor in `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`
- Added `gls_footer_brand_heading_tag_replacement()` to isolate regex replacement value for footer brand heading tag normalization
- Reused helper in `gls_normalize_footer_brand_heading_tag_html()`
- Validation before deploy:
  - `git diff --check`
  - `./tools/verify-storefront-baseline.sh`
  - `./tools/verify-checkout-shell.sh`
