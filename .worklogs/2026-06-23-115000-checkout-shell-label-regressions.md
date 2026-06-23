# Checkout shell label regressions

## Context
- User asked to fix the two commerce shell regressions found during the order/PageSpeed audit.
- Read `docs/handoff.zip` / `docs/handoff/` operating rules before deployment.
- Followed repo-first workflow and deployed only the changed MU-plugin file.

## Changes
- `wordpress/wp-content/mu-plugins/rusky-theme-chrome-language.php`
  - Normalized the RU terms link label to the project/test contract: `правила и условия`.
  - Added a checkout-only DOM fallback that inserts the WooCommerce terms checkbox before `#place_order` only if the checkout form has a place-order button but no terms checkbox text.
  - The fallback preserves `terms` and `terms-field` field names for WooCommerce validation and uses RU/SK localized copy.
- `tools/verify-commerce-shell-sk.sh`
  - Updated the SK optional marker expectation from English `(optional)` to localized `(voliteľné)`.

## Deployment
- Verified candidate syntax on live PHP:
  - `php -l /tmp/rusky-theme-chrome-language.candidate.php`
- Deployed only:
  - `wp-content/mu-plugins/rusky-theme-chrome-language.php`

## Verification
- `tools/verify-commerce-shell.sh`: green.
- `tools/verify-commerce-shell-sk.sh`: green.
- `tools/verify-checkout-shell.sh`: green.
- `tools/verify-storefront-baseline.sh`: green.
- Live headers:
  - `https://ruskyobchod.sk/`: `200`
  - `https://ruskyobchod.sk/wp-login.php`: `200`

## Notes
- Dotypos was not touched.
- Existing unrelated Meta scheduler artifacts remained outside this change set.
