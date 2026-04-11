# FAR Runtime Single-Owner Cut

Date: 2026-04-11

## Scope

Remove split runtime ownership for `option_active_plugins` between:

- `wordpress/wp-content/mu-plugins/rusky-runtime-shim.php`
- `wordpress/wp-content/mu-plugins/rusky-disable-far.php`

## What changed

Updated:

- `wordpress/wp-content/mu-plugins/rusky-disable-far.php`

The file no longer registers its own `option_active_plugins` filter.

It is now only a deprecated compatibility shim with no runtime decision logic.

## Result

- `rusky-runtime-shim.php` is now the single owner of the custom runtime plugin-filter decision
- split FAR ownership in the repo was removed
- `rusky-disable-far.php` remains present only to avoid a missing-file surprise during controlled deployment cleanup

## Verification

- `git diff --check` passed
- repo search now shows the custom FAR plugin filtering only in:
  - `wordpress/wp-content/mu-plugins/rusky-runtime-shim.php`

The other remaining `option_active_plugins` hit is unrelated:

- `wordpress/wp-content/mu-plugins/woocommerce-analytics-proxy-speed-module.php`
