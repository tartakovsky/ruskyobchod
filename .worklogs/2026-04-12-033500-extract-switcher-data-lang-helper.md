## Summary

Extracted internal switcher `data-lang` values into `gls_switcher_data_lang()`.

## Scope

- no behavior change intended
- preserves the same `ru` / `sk` data-lang attribute values
- only removes inline attribute literals from the fallback switcher renderer

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
