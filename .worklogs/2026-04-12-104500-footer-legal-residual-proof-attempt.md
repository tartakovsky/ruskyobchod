# 2026-04-12 10:45:00 CET

- Start of exact-output proof for retained residual slice:
  - legal/footer normalization inside storefront footer shell
- Before-state captured from live RU homepage:
  - `Словацкая Республика`
  - `Зарегистрирована в торговом реестре окружного суда Братислава I,`
  - `Раздел s.r.o., № записи 182562/B`
- Test change:
  - removed only the call to `gls_normalize_legal_company_text_html()` inside `gls_normalize_storefront_footer_shell_html()`
- Validation path after deploy:
  - `tools/verify-storefront-baseline.sh`
  - exact-output grep on RU homepage
- Decision rule:
  - if output regresses, classify slice as `keep`
  - if output remains identical, classify as `remove later`
