## Summary

Removed verified duplicate HTML fallback replacements from `gls_normalize_storefront_chrome_html()` for storefront strings that are now covered by source-level server translation.

## Removed fallback entries

- `Domov` / `Главная`
- `Kategória:` / `Категория:`
- `Množstvo produktu` / `Количество товара`
- `aria-label` for `Množstvo produktu` / `Количество товара`

## Safety

This step only removes fallback duplicates after live verification already confirmed category and product pages render these strings correctly from server-side translation.

## Verification

- `git diff --check`
