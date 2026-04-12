## Summary

Extracted switcher current-language retrieval into `gls_switcher_current_lang()`.

## Scope

- no behavior change intended
- preserves the same `gls_current_lang_code()` source
- only removes the direct call from `gls_add_switcher()`

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
