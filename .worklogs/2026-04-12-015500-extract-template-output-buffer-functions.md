## Summary

Converted the `template_redirect` output-buffer path into named functions:

- `gls_filter_template_output_html()`
- `gls_start_template_output_buffer()`

## Scope

- no behavior change intended
- preserves:
  - sensitive-runtime guard
  - server-rendered vs storefront branch
  - front-page normalization branch
  - final inactive-language strip

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
