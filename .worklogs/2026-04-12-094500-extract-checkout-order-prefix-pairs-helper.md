# 2026-04-12 09:45:00 CET

- Scope: bounded no-behavior-change refactor in `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`
- Replaced positional `str_replace(...array_merge())` with `gls_replace_text_pairs()` and `gls_checkout_order_title_prefix_pairs()`
- Scope limited to RU prefix replacement in `gls_normalize_checkout_order_title_shell_html()`
- Validation before deploy:
  - `git diff --check`
  - `./tools/verify-storefront-baseline.sh`
  - `./tools/verify-checkout-shell.sh`
