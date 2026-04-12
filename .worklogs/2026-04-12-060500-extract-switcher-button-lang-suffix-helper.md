## Summary

Extracted the fallback switcher button language suffix into `gls_switcher_button_lang_suffix()`.

## Scope

- no behavior change intended
- preserves the same `ru` / `sk` suffix values
- only removes the inline language suffix from `gls_switcher_button_class()`

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
