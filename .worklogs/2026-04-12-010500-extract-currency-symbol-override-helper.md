## Summary

Extracted the EUR currency symbol override into `gls_currency_symbol_override()`.

## Scope

- no behavior change intended
- preserves the existing `EUR -> €` override
- narrows inline logic inside the WooCommerce currency symbol filter

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
