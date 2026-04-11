# WooCommerce Storefront Source Translation Cut

Date: 2026-04-11

## Scope

Move a small storefront label subset from HTML fallback replacement to source-level WooCommerce translation.

## What changed

Updated:

- `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`

Added:

- `gls_translate_woocommerce_storefront_phrase()`

Extended the existing `gettext` translation flow for the `woocommerce` domain to cover:

- `Do košíka` / `В корзину`
- `Súvisiace produkty` / `Похожие товары`
- `Na sklade` / `В наличии`
- `SKU:` / `Артикул:`

Removed the matching HTML fallback replacements for that same subset from:

- `gls_normalize_storefront_chrome_html()`

## Result

- these Woo storefront labels now translate earlier in the render chain
- output buffering keeps fewer storefront-owned responsibilities
- the buffer fallback is smaller and more focused on residual chrome/runtime cleanup

## Verification

- `git diff --check` passed
