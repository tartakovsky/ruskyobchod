## Summary

Extracted the fallback switcher container class literal into `gls_switcher_container_class()`.

## Scope

- no behavior change intended
- preserves the same `gls-switcher` class value
- only removes the inline class literal from `gls_switcher_container_attributes()`

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
