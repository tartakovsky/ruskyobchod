## Summary

Extracted current-language RU check into `gls_is_current_lang_ru()`.

## Scope

- no behavior change intended
- reused only in:
  - `gls_brand_name()`
  - `gls_brand_description()`
- preserves the same RU/SK string selection

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
