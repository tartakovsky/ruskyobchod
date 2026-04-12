## Summary

Extracted the internal fallback switcher HTML assembly into `gls_render_internal_switcher_html()`.

## Scope

- no behavior change intended
- preserves the same:
  - URLs
  - classes
  - attribute order
  - RU/SK labels
- only moves string assembly out of `gls_add_switcher()`

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
