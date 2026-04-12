## Summary

Extracted text-language fallback resolution into `gls_resolve_text_lang()`.

## Scope

- no behavior change intended
- preserves the same accepted explicit values: `ru`, `sk`
- preserves the same fallback to `gls_current_lang_code()`

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
