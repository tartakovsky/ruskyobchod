## Summary

Extracted brand name literals into `gls_brand_name_ru()` and `gls_brand_name_sk()`.

## Scope

- no behavior change intended
- preserves the same RU/SK brand name selection in `gls_brand_name()`
- only removes inline brand-name literals from the selector function

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
