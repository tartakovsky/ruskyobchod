# GLS Operator Frontend Boundary Fix

- Date: 2026-04-13 17:45 Europe/Bratislava
- Scope: `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`

## Why

The live `503` issue was reproducible specifically on frontend requests carrying a valid WordPress logged-in cookie for an operator/admin user.

Public requests stayed `200`, while the same homepage and account/cart frontend URLs returned `503` with an authenticated operator cookie.

That pointed to a runtime boundary regression in `GLS`: during cleanup, the plugin stopped treating logged-in operator frontend requests as sensitive runtime, so the full language/locale/output-buffer layer started running there.

## Change

- added `gls_is_logged_in_operator_request()`
- marked operator/admin frontend requests as sensitive runtime
- excluded those requests from the heavy `GLS` runtime path via `gls_is_sensitive_runtime_context()`

This is a boundary correction, not a fallback patch:
- operator/admin frontend no longer goes through the storefront language buffer stack
- customer/public storefront paths are unchanged

## Verification

- deployed only `gastronom-lang-switcher.php`
- remote `php -l` green
- repeated live probe with a real `wordpress_logged_in_*` admin cookie:
  - `/` -> `200`
  - `/?lang=ru` -> `200`
  - `/my-account/?lang=ru` -> `200`
  - `/cart/?lang=ru` -> `200`
  - 8 repeated homepage requests with the same cookie -> all `200`
- baselines green:
  - `verify-storefront-baseline.sh`
  - `verify-account-shell.sh`
  - `verify-checkout-shell.sh`
  - `verify-preorder-shell.sh`

## Result

The previously reproduced logged-in frontend `503` is no longer reproduced by direct HTTP proof after this boundary fix.
