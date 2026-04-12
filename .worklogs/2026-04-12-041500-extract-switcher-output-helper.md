## Summary

Extracted switcher output echo path into `gls_output_switcher_html()`.

## Scope

- no behavior change intended
- preserves the same `gls_render_switcher_html()` result
- only removes the inline echo from `gls_add_switcher()`

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
