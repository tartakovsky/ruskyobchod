## Summary

Converted the inline `woocommerce_currency_symbol` filter callback into named function `gls_filter_woocommerce_currency_symbol()`.

## Scope

- no behavior change intended
- preserves the same `gls_currency_symbol_override()` call
- keeps the same hook priority and arity

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
