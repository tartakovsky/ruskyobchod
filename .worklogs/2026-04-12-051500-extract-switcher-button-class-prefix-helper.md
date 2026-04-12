## Summary

Extracted the fallback switcher button class prefix into `gls_switcher_button_class_prefix()`.

## Scope

- no behavior change intended
- preserves the same `gls-btn gls-btn-` prefix
- only removes the inline prefix literal from `gls_switcher_button_class()`

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
