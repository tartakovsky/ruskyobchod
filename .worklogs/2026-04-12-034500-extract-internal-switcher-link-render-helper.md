## Summary

Extracted fallback switcher link rendering into `gls_render_internal_switcher_link()`.

## Scope

- no behavior change intended
- preserves the same:
  - class
  - data-lang
  - href
  - title
  - visible label
- only removes duplicated anchor assembly from the fallback switcher renderer

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
