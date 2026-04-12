# 2026-04-12 10:40:00 CET

- Start of exact-output proof for retained residual slice:
  - cookie notice button normalization inside server-rendered cart/checkout shell
- Before-state captured from live RU cart/checkout shell:
  - `cn-accept-cookie` button text is `Ок`
  - `cn-accept-cookie` aria-label is `Ок`
  - `cn-close-notice` aria-label is `Нет`
- Test change:
  - removed only the call to `gls_normalize_cookie_notice_button_html()` inside the server-rendered shell closure
- Validation path after deploy:
  - `tools/verify-storefront-baseline.sh`
  - `tools/verify-checkout-shell.sh`
- Decision rule:
  - if checkout shell fails, classify this slice as `keep`
  - if exact output stays identical, classify as `remove later`
