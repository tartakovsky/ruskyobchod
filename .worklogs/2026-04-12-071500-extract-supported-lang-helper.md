# 2026-04-12 07:15:00 CET

- Scope: bounded no-behavior-change refactor in `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`
- Added `gls_is_supported_lang()` to centralize repeated `ru/sk` validation
- Reused helper in:
  - `gls_current_lang_code()`
  - `gls_resolve_text_lang()`
  - `gls_server_lang()`
  - `init` language cookie sync
  - `wp_redirect` language propagation
- Validation before deploy:
  - `git diff --check`
  - `./tools/verify-storefront-baseline.sh`
  - `./tools/verify-checkout-shell.sh`
