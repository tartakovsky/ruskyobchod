# GLS Auth Boundary Hardening

- Date: 2026-04-13 17:15 Europe/Bratislava
- Scope: `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`

## Why

`Gastronom Language Switcher` was still globally affecting `wp-login.php` and all same-host redirects through:

- `init` cookie write
- `wp_redirect` language propagation
- `locale` / `determine_locale` runtime path

That was too broad for a language plugin and matched the recovery-email suspicion around `wp-login.php`.

## Change

- added explicit `wp-login` request detection
- marked `wp-login` as a sensitive runtime context
- stopped language cookie writes on `wp-login`
- narrowed `wp_redirect` language propagation so it no longer touches:
  - sensitive runtime contexts
  - requests without explicit language context
  - auth targets (`/wp-login.php`, `/wp-admin`)

## Verification

- live deploy of the single changed file
- remote `php -l` green
- `verify-storefront-baseline.sh` green
- `verify-checkout-shell.sh` green
- `verify-preorder-shell.sh` green
- `verify-account-shell.sh` green
- `wp-login.php` responds `200`
- exact `order-pay` URL for order `11058` responds `200`

## Notes

This does not prove the external/client-side `503` issue is fully eliminated, but it removes a real systemic overreach from `GLS` and narrows the auth/order surface to a more defensible ownership boundary.
