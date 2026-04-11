## Summary

Converted the inline `wcpay_locale` filter callback into named function `gls_filter_wcpay_locale()`.

## Scope

- no behavior change intended
- preserves the same sensitive-runtime short circuit
- preserves the same `gls_wcpay_wp_locale()` return path

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
