## Summary

Extracted brand description literals into `gls_brand_description_ru()` and `gls_brand_description_sk()`.

## Scope

- no behavior change intended
- preserves the same RU/SK description selection in `gls_brand_description()`
- only removes inline brand-description literals from the selector function

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
