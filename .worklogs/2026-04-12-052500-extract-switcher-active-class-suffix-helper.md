## Summary

Extracted the fallback switcher active-class suffix into `gls_switcher_active_class_suffix()`.

## Scope

- no behavior change intended
- preserves the same `' active'` suffix rule
- only removes the inline ternary suffix from `gls_switcher_button_class()`

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
