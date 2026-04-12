## Summary

Cached `gls_current_lang_code()` in a local variable inside `gls_add_switcher()`.

## Scope

- no behavior change intended
- preserves the same switcher render path and output
- removes the direct nested call in the echo expression

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
