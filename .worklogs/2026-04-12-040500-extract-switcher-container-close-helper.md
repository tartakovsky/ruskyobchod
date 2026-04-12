## Summary

Extracted fallback switcher container closing markup into `gls_switcher_container_close_html()`.

## Scope

- no behavior change intended
- preserves the same closing wrapper markup
- only removes the inline closing literal from the fallback switcher renderer

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
