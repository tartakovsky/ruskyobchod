## Summary

Extracted the stylesheet asset path into `gls_style_asset_url()`.

## Scope

- no behavior change intended
- preserves the same stylesheet handle, path, and version
- only removes the inline asset URL concatenation from `gls_enqueue_scripts()`

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
