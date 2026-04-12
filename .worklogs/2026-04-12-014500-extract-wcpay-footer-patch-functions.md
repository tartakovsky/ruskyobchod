## Summary

Converted the nested WooPayments footer locale patch callbacks into named functions:

- `gls_apply_wcpay_locale_to_footer_scripts()`
- `gls_enqueue_wcpay_locale_footer_patch()`

## Scope

- no behavior change intended
- preserves:
  - sensitive-runtime short circuit
  - checkout-only guard
  - the same `wp_print_footer_scripts` priority
  - the same `gls_apply_wcpay_locale_to_script_extra()` application path

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
