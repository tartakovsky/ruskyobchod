## Summary

Extracted switcher render dispatch into `gls_render_switcher_html()`.

## Scope

- no behavior change intended
- preserves:
  - external `rslc_render_switcher()` preference
  - internal fallback switcher markup
  - `wp_body_open` output path

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
