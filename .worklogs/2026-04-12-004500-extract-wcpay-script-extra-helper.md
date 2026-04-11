## Summary

Extracted repeated WooPayments script-extra locale mutation into `gls_apply_wcpay_locale_to_script_extra()`.

## Scope

- no behavior change intended
- centralizes locale replacement for:
  - `extra['data']`
  - `extra['before']`
  - `extra['after']`

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
