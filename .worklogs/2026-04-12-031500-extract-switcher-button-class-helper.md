## Summary

Extracted internal switcher button class assembly into `gls_switcher_button_class()`.

## Scope

- no behavior change intended
- preserves the same `gls-btn gls-btn-<lang>` format
- preserves the same ` active` suffix condition
- only removes inline class assembly from the fallback switcher renderer

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
