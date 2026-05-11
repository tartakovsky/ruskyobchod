# Search Console indexing SEO fix

Date: 2026-05-11

## Context

Apple Mail contained three Google Search Console warnings for `https://ruskyobchod.sk/`:

- 2026-05-07: `5xx` and duplicate page where Google/user canonical differed
- 2026-05-11 03:48: sitemap URLs excluded as canonical variants
- 2026-05-11 03:49: redirect error and duplicate page without user-selected canonical

The business expectation is that search traffic will mostly use Slovak/default URLs, not Russian query URLs.

## Findings

- `robots.txt` pointed to `https://ruskyobchod.sk/wp-sitemap.xml`.
- `wp-sitemap.xml` existed, but child sitemaps initially returned empty bodies when fetched before the runtime guard fix.
- Public pages did not expose a stable canonical strategy for `?lang=ru`.
- `?lang=ru` pages returned `200`, which made them crawlable duplicate URLs.
- `/obchod/` correctly redirects to `/shop/` through `RuskyShopAlias`.

## Change

Updated `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php` only:

- bumped plugin version to `6.26`
- excluded indexing infrastructure requests from GLS runtime mutation:
  - `/robots.txt`
  - `wp-sitemap*.xml`
  - `sitemap*.xml`
  - feed URLs
- added one clean canonical URL for public indexable surfaces
- removed the default WordPress `rel_canonical` action to avoid duplicate canonical tags
- marked explicit `?lang=ru/sk` public URLs as `noindex, follow`
- removed the WordPress users sitemap provider from the sitemap index

## Deploy

Deployed only:

- `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`

Live path:

- `/home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`

## Verification

- Candidate PHP syntax before deploy: passed on server PHP.
- `./tools/verify-live-php-syntax.sh`: passed.
- Homepage body: `65892` bytes.
- `wp-login.php`: `200`.
- `wp-sitemap.xml` now lists:
  - `wp-sitemap-posts-page-1.xml`
  - `wp-sitemap-posts-product-1.xml`
  - `wp-sitemap-taxonomies-product_cat-1.xml`
- `wp-sitemap-users-1.xml` is no longer listed.
- Page sitemap URL count: `12`.
- Product sitemap URL count: `432`.
- Product category sitemap URL count: `22`.
- Clean contacts page exposes:
  - `canonical=https://ruskyobchod.sk/kontakty/`
- RU home URL exposes:
  - `robots=max-image-preview:large, noindex, follow`
  - `canonical=https://ruskyobchod.sk/`
- RU contacts URL exposes:
  - `robots=max-image-preview:large, noindex, follow`
  - `canonical=https://ruskyobchod.sk/kontakty/`

## Notes

`./tools/verify-storefront-baseline.sh` was rerun with live network access. Homepage/category language markers passed, but the verifier is currently not fully green because its hardcoded product URL returns `404`. That appears to be verifier drift, not a regression from this SEO change.

Existing unrelated untracked Meta scheduler files were left untouched.
