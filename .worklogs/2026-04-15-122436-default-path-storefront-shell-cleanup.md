# 2026-04-15 12:24:36 Default Path Storefront Shell Cleanup

## Scope
- Closed remaining storefront translation defects on default/cookie-driven paths without touching cash, stock sync, or Dotypos logic.
- Extended server-side language detection to handle path-prefixed requests like `/sk/...` and `/ru/...`.
- Normalized Woo storefront shell strings that still leaked through on uncached public paths.

## Files changed
- `wordpress/wp-content/mu-plugins/rusky-theme-chrome-language.php`

## User-visible fixes
- Removed `Sorted by ...` English tails from category result counts on default category pages.
- Localized add-to-cart notices and the `View cart` action on product pages.
- Localized cart table chrome like `Remove item`, `Thumbnail image`, and `Update cart`.
- Localized checkout `Place order` and remaining default/cookie-path Woo shell phrases.

## Verification
- Exact cookie-path checks for RU/SK category, product add-to-cart notice, cart, and checkout returned translated output.
- `./tools/verify-tuesday-readiness.sh` completed green after deploy.
