## Summary

Extracted the plugin asset version literal into `gls_asset_version()`.

## Scope

- no behavior change intended
- preserves the same stylesheet handle, path, and version value
- only removes an inline literal from `gls_enqueue_scripts()`

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
