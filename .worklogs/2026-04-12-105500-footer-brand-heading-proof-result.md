# 2026-04-12 10:55:00 CET

- Completed exact-output proof for retained residual slice:
  - footer brand heading normalization inside `gls_normalize_storefront_footer_shell_html()`
- Before-state on live RU homepage:
  - footer heading `Гастроном`
- Test:
  - removed only the call to `gls_normalize_footer_brand_heading_html()` inside the storefront footer shell path
- After-state on live RU homepage:
  - footer heading regressed to `Gastronom`
- Baseline result:
  - existing storefront baseline remained green because it does not currently assert the footer brand heading string
- Decision:
  - classify this slice as `keep`
- Action:
  - reverted the test change immediately with single-step rollback
