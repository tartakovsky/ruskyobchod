## Summary

Extracted internal switcher URL collection into `gls_switcher_urls()`.

## Scope

- no behavior change intended
- preserves the same RU/SK URL generation and escaping
- only removes duplicate local URL assignments from the fallback switcher renderer

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
