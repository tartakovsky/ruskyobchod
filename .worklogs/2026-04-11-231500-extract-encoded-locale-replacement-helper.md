## Summary

Extracted repeated WooPayments locale marker replacement into `gls_replace_encoded_locale_markers()` inside `gastronom-lang-switcher.php`.

## Scope

- no behavior change intended
- centralizes replacement used for:
  - localized script data
  - inline `before` scripts
  - inline `after` scripts

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
