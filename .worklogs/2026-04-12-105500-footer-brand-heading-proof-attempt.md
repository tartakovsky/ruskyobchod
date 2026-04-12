# 2026-04-12 10:55:00 CET

- Start of exact-output proof for retained residual slice:
  - footer brand heading normalization inside `gls_normalize_storefront_footer_shell_html()`
- Before-state captured from live RU homepage:
  - footer heading is `Гастроном`
- Test change:
  - removed only the call to `gls_normalize_footer_brand_heading_html()` inside the storefront footer shell path
- Validation path after deploy:
  - exact-output grep on RU homepage footer heading
  - `tools/verify-storefront-baseline.sh`
- Decision rule:
  - if heading regresses to `Gastronom`, classify slice as `keep`
  - if output remains identical, classify as `remove later`
