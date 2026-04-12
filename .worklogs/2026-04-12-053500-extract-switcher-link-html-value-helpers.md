## Summary

Extracted fallback switcher link payload helpers:

- `gls_switcher_link_data_lang_html()`
- `gls_switcher_link_title_html()`
- `gls_switcher_link_label_html()`

## Scope

- no behavior change intended
- preserves the same link structure and attribute order
- only removes inline value assembly from `gls_render_internal_switcher_link()`

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
