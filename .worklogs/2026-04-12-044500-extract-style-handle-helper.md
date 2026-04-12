## Summary

Extracted the stylesheet handle literal into `gls_style_handle()`.

## Scope

- no behavior change intended
- preserves the same style handle, path, deps, and version
- only removes the inline handle literal from `gls_enqueue_scripts()`

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
