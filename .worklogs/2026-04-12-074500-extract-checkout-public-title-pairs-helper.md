# 2026-04-12 07:45:00 CET

- Scope: bounded no-behavior-change refactor in `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`
- Added `gls_checkout_public_title_pairs()` to isolate checkout title replacement maps
- Reused helper in `gls_normalize_checkout_public_title_text()`
- Validation before deploy:
  - `git diff --check`
  - `./tools/verify-storefront-baseline.sh`
  - `./tools/verify-checkout-shell.sh`
