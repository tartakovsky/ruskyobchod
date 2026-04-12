# 2026-04-12 11:35:00 CET

- Second half of the cookie notice slice migration
- Removed old late output cleanup for cookie notice labels from:
  - `gls_normalize_storefront_footer_shell_html()`
  - server-rendered empty-cart shell path
- Kept the new earlier source-level owner through `cn_cookie_notice_args`
- Validation after deploy:
  - exact-output check on RU home and RU cart cookie notice
  - `tools/verify-storefront-baseline.sh`
  - `tools/verify-checkout-shell.sh`
