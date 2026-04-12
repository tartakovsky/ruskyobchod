## Summary

Extracted fallback switcher container opening markup into `gls_switcher_container_open_html()`.

## Scope

- no behavior change intended
- preserves the same container `id` and `class`
- only removes the inline opening wrapper literal from the fallback switcher renderer

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
