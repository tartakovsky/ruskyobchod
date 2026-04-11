## Summary

Extracted duplicated skip-link normalization logic into a dedicated helper:

- `gls_normalize_skip_link_html()`

## Changed call sites

- `gls_normalize_server_rendered_html()`
- `gls_normalize_front_page_html()`

## Behavior goal

No behavior change.

This is a duplication reduction step inside one file, with the exact same regex and replacement output preserved.

## Verification plan

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- one-file deploy
- `tools/verify-storefront-baseline.sh` again after deploy
