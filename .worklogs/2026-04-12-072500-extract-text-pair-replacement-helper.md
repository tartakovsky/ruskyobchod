# 2026-04-12 07:25:00 CET

- Scope: bounded no-behavior-change refactor in `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`
- Added `gls_replace_text_pairs()` to centralize ordered text replacement maps
- Reused helper in `gls_normalize_account_title_text()` for RU/SK account title normalization
- Validation before deploy:
  - `git diff --check`
  - `./tools/verify-storefront-baseline.sh`
  - `./tools/verify-checkout-shell.sh`
