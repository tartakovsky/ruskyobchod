## Summary

Removed duplicate footer/legal text replacements from `gls_normalize_storefront_chrome_html()`.

## Why safe

The same legal/company translations are already handled earlier in `gls_normalize_server_rendered_html()` for both RU and SK, so the storefront chrome layer was carrying redundant replacements.

## Removed duplicates

- `Slovenská republika` / `Словацкая Республика`
- `Zapísaná v OR OS Bratislava I,` / `Зарегистрирована в торговом реестре окружного суда Братислава I,`
- `Oddiel: Sro, Vložka č. 182562/B` / `Раздел s.r.o., № записи 182562/B`

## Verification

- `git diff --check`
