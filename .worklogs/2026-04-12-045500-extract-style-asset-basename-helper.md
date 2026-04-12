## Summary

Extracted the stylesheet basename literal into `gls_style_asset_basename()`.

## Scope

- no behavior change intended
- preserves the same final stylesheet URL
- only removes the inline basename literal from `gls_style_asset_url()`

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
