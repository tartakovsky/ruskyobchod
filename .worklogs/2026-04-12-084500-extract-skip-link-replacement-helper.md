# 2026-04-12 08:45:00 CET

- Scope: bounded no-behavior-change refactor in `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`
- Added `gls_skip_link_replacement()` to isolate regex replacement value for skip-link normalization
- Reused helper in `gls_normalize_skip_link_html()`
- Validation before deploy:
  - `git diff --check`
  - `./tools/verify-storefront-baseline.sh`
  - `./tools/verify-checkout-shell.sh`
