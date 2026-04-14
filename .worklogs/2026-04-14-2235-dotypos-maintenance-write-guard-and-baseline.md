## Context

Production already had a working Dotypos stock lifecycle for:
- regular Woo order reduce / cancel restore
- weight preorder confirm / cancel restore

The remaining runtime risk around Dotypos was not stock behavior itself, but the always-loaded maintenance surface in `rusky-dotypos-maintenance.php`.

That file still exposed write/patch operations through REST:
- `write_plugin`
- `patch_dotypos`
- `update_option`
- lock / action cleanup

Those tools were useful during incident surgery. They are not appropriate as default live runtime capability on a now-stabilizing store.

## Change

Updated [`wordpress/wp-content/mu-plugins/rusky-dotypos-maintenance.php`](/Users/alexandertartakovsky/ruskyobchod/wordpress/wp-content/mu-plugins/rusky-dotypos-maintenance.php) so that:

- read-only diagnostics stay available to admins
- write/patch maintenance actions are disabled by default
- write actions require explicit opt-in constant:
  - `RUSKY_DOTYPOS_MAINTENANCE_WRITE`

Important:
- did **not** change stock sync logic
- did **not** change preorder hooks
- did **not** change the logged-in frontend Dotypos boundary filter

This was intentionally a narrow runtime hardening step, not a behavioral change.

## Deployment

Deployed only:
- `wordpress/wp-content/mu-plugins/rusky-dotypos-maintenance.php`

Verified live syntax with:
- `./tools/verify-live-php-syntax.sh`

## Verification

### Dotypos safety

- live `Dotypos Settings` page loads
- `GET /wp-json/dotypos/v1/settings` -> `200`
- `GET /wp-json/dotypos/v1/pairingKeys` -> `200`
- authenticated `POST /wp-json/gls/v1/dotypos-fix` now returns `403`

### Stock integrity

- `./tools/verify-dotypos-product-state.sh 10640`
  - regular product matches local stock and remote stock = `12`
- `./tools/verify-dotypos-product-state.sh 10617`
  - weight preorder product matches local cash stock and remote stock = `1.6`
- `./tools/verify-dotypos-readonly.sh` green
- `./tools/verify-tuesday-readiness.sh` green

### Site baseline

Verified green:
- `./tools/verify-storefront-baseline.sh`
- `./tools/verify-checkout-shell.sh`
- `./tools/verify-account-shell.sh`
- `./tools/verify-commerce-shell.sh`
- `./tools/verify-preorder-shell.sh`
- `./tools/verify-order-page-language.sh`
- `./tools/verify-admin-order-screen.sh`

Also aligned the storefront baseline verifier with the now-correct footer orphan fix:
- it now accepts `Братислава I,` with a non-breaking space

## Result

Current production state is stricter than before:
- Dotypos stock behavior remains unchanged
- admin Dotypos UI remains healthy
- the emergency maintenance write surface is no longer live by default
- the main storefront / checkout / account / preorder / order-page baseline is green

## Next Safe Step

Do not widen Dotypos changes.

Next work should focus on language ownership only:
- inventory which user-visible strings are already server-rendered
- identify where `gls-script.js` still owns visible translation
- move those paths server-first in small verified slices without touching stock or order logic
