## Summary

Extracted fallback switcher container attributes into `gls_switcher_container_attributes()`.

## Scope

- no behavior change intended
- preserves the same container `id` and `class`
- only removes the inline attribute literal from the opening wrapper helper

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
