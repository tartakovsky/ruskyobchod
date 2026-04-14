## Context

`Dotypos Settings` in wp-admin showed false disconnected/disabled state even though live `dotypos_settings` had stock-only integration enabled.

Observed behavior:
- page shell loaded
- `GET /wp-json/dotypos/v1/settings` returned `503` only in logged-in admin browser context
- anonymous request to the same route returned `200`
- other `wc-admin` REST requests succeeded

## Cause

The plugin frontend in [`src/index.js`](/Users/alexandertartakovsky/ruskyobchod/wordpress/wp-content/plugins/woocommerce-extension-master/src/index.js) used plain `fetch()` for its own REST namespace and did not send `X-WP-Nonce`.

As a result:
- Woo admin REST requests that used nonce worked
- Dotypos requests without nonce failed on the authenticated admin REST path
- the React UI fell back to first-step/disabled-looking state and lied about the real live settings

## Fix

Implemented owner-level request helper in the Dotypos plugin frontend:
- adds `X-WP-Nonce` from `window.wpApiSettings.nonce`
- routes all plugin REST calls through that helper

Rebuilt plugin assets and deployed:
- [`src/index.js`](/Users/alexandertartakovsky/ruskyobchod/wordpress/wp-content/plugins/woocommerce-extension-master/src/index.js)
- [`build/index.js`](/Users/alexandertartakovsky/ruskyobchod/wordpress/wp-content/plugins/woocommerce-extension-master/build/index.js)
- [`build/index.asset.php`](/Users/alexandertartakovsky/ruskyobchod/wordpress/wp-content/plugins/woocommerce-extension-master/build/index.asset.php)

## Verification

After deploy and hard reload:
- `GET /wp-json/dotypos/v1/settings` -> `200`
- `GET /wp-json/dotypos/v1/pairingKeys` -> `200`
- Dotypos settings screen shows real live state:
  - products sync enabled
  - warehouse connected
  - `syncFromDotypos` checked
  - `syncToDotypos` checked
  - product field sync flags unchecked
  - category sync unchecked

## Current Safe State

Live integration mode remains:
- cash -> site stock sync enabled
- site -> cash stock movement sync enabled
- title / price / vat / category / EAN / note sync disabled
- product webhook absent
- movement webhook present

