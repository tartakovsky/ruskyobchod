# 2026-04-15 11:45:37 Storefront Shell Readiness Hardening

## Scope
- Closed logged-in SK storefront archive shell leakage without touching Dotypos or stock logic.
- Normalized RU/SK storefront, cart, checkout, account, product, and order endpoint language through isolated server-side `mu-plugin` layers.
- Updated local verifiers only where live output was already correct and the expected string was stale.

## Files changed
- `wordpress/wp-content/mu-plugins/rusky-theme-chrome-language.php`
- `wordpress/wp-content/mu-plugins/rusky-front-page-language.php`
- `wordpress/wp-content/mu-plugins/rusky-language-switcher-lite.php`
- `tools/verify-language-runtime-surface.sh`
- `tools/verify-order-page-language.sh`

## Live verification
- Logged-in SK category page now renders search, sorting, add-to-cart, pagination, and result count in Slovak.
- RU storefront shell now stays server-rendered across home, cart, account, product, checkout, and thank-you/pay endpoints.
- `./tools/verify-tuesday-readiness.sh` completed green after the final deploy.

## Guardrails held
- No vendor Dotypos edits.
- No stock sync / preorder / checkout business logic changes.
- Deploys were minimal file syncs followed by immediate live verification.
