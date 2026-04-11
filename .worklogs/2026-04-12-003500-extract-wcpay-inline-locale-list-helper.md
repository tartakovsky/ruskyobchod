## Summary

Extracted repeated WooPayments inline script locale replacement into `gls_replace_encoded_locale_markers_in_list()`.

## Scope

- no behavior change intended
- keeps `data` replacement path as scalar
- centralizes repeated replacement for:
  - `extra['before']`
  - `extra['after']`

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
