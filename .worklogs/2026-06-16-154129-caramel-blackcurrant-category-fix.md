# Caramel blackcurrant category fix

## Context

User reported that `Karamelky Čierny ríbezle /Карамель Черная смородина` appeared on the storefront under alcohol, while the cashier-side category should be caramel.

## Live finding

Live WooCommerce DB showed product `11326` assigned to:

- before: `196 Алкогольные напитки/ Alkoholické nápoje`
- expected storefront category: `201 Карамель/ Karamelky`

Product metadata:

- `dotypos_product_id`: `1915883613071306`
- `_stock`: `3`
- `_regular_price`: `15.00`
- `_stock_status`: `instock`

Dotypos API read-only lookup for product `1915883613071306` returned the matching name and EAN `4620004256379`, but `categoryId` was `null`. Because of that, direct category parity from Dotypos API is not currently usable for this product.

## Change

Applied a targeted live SQL data fix:

- removed product `11326` from product category term taxonomy `196`
- inserted product `11326` into product category term taxonomy `201`
- recalculated published-product counts for term taxonomies `196` and `201`
- deleted WooCommerce term-count transient entries

No code files were deployed.

## Verification

SQL verification after the fix:

- product `11326` category is now only `Карамель/ Karamelky`
- alcohol category count is now `29`
- caramel category count is now `11`
- published products without a `product_cat`: `0`
- no product category count mismatch found in SQL count audit

HTTP checks:

- `https://ruskyobchod.sk/produkt/karamelky-ierny-r-bezle-karamel-chernaya-smorodina/` returns `200`
- `https://ruskyobchod.sk/kategoria-produktu/karamel-karamelky/` returns `200`
- `https://ruskyobchod.sk/wp-json/wp/v2/product/11326` returns `200`

Broader audit:

- checked 486 published WooCommerce products
- checked all published alcohol-category products by name
- no remaining alcohol-category products matched caramel/candy/zephyr/waffle/gingerbread suspicious terms
- all 486 published WooCommerce products have Dotypos product matches by `dotypos_product_id`

## Notes

WP-CLI commands that fully load WordPress currently segfault during bootstrap, after plugin loading starts. Direct DB reads/writes were used because `wp option get`, `wp post list`, and similar full-load commands failed with segmentation fault.

Dotypos category parity by API is limited right now because product responses for all 486 matched published WooCommerce products returned `categoryId=null` in the audit.
