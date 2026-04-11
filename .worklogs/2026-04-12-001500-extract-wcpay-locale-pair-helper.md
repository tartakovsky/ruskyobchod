## Summary

Extracted shared WooPayments locale target/source derivation into `gls_wcpay_locale_pair()`.

## Scope

- no behavior change intended
- reused by:
  - `script_loader_tag`
  - footer script locale replacement path

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
