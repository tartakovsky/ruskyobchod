## Summary

Extracted internal switcher visible labels into `gls_switcher_label()`.

## Scope

- no behavior change intended
- preserves the same visible `RU` / `SK` labels
- only removes inline label literals from the fallback switcher renderer

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
