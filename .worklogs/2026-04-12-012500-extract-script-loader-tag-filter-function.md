## Summary

Converted the inline `script_loader_tag` WooPayments locale callback into named function `gls_filter_script_loader_tag_for_wcpay_locale()`.

## Scope

- no behavior change intended
- preserves the same sensitive-runtime short circuit
- preserves the same `wcpay` handle check and locale replacement path

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
