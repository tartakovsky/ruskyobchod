## Summary

Extracted internal switcher link titles into `gls_switcher_link_title()`.

## Scope

- no behavior change intended
- preserves the same RU/SK title values
- only removes inline title literals from the fallback switcher renderer

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
