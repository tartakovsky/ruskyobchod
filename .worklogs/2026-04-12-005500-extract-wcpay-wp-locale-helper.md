## Summary

Extracted WooPayments `ru_RU/sk_SK` locale selection into `gls_wcpay_wp_locale()`.

## Scope

- no behavior change intended
- reuses existing page-language decision
- narrows direct locale string duplication in the `wcpay_locale` filter

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
