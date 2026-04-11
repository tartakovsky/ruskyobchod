## Summary

Extended server-side storefront translation in `gastronom-lang-switcher.php` for breadcrumb and product chrome without removing the existing HTML fallback layer.

## Changes

- added source-level Woo phrase mappings for `Kategória:` / `Категория:` and `Množstvo produktu` / `Количество товара`
- updated `woocommerce_get_breadcrumb` localization to run exact phrase translation before bilingual normalization so `Domov` can resolve through existing server-side maps

## Risk

Low. This is additive server-side translation in one file, with the existing buffer fallback left in place for the same strings during this step.

## Verification

- `git diff --check`
