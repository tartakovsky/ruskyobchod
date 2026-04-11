## Summary

Reused `gls_replace_encoded_locale_markers()` inside the WooPayments `script_loader_tag` filter.

## Scope

- no behavior change intended
- removes inline locale swap duplication for `wcpay` script tags
- keeps locale target/source derivation unchanged

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
