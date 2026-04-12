## Summary

Extracted fallback switcher language key collection into `gls_switcher_langs()`.

## Scope

- no behavior change intended
- preserves the same `ru` / `sk` order
- only removes repeated inline language key literals from the fallback switcher renderer

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
