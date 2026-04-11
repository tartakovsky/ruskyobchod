## Summary

Extracted repeated WooPayments script-handle detection into `gls_is_wcpay_handle()`.

## Scope

- no behavior change intended
- reuses the same case-sensitive `strpos` checks as before
- applied in:
  - `script_loader_tag`
  - checkout footer script locale replacement loop

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
