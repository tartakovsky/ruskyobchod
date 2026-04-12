## Summary

Extracted the fallback switcher container id literal into `gls_switcher_container_id()`.

## Scope

- no behavior change intended
- preserves the same `gls-switcher` id value
- only removes the inline id literal from `gls_switcher_container_attributes()`

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
