## Summary

Extracted regex-based footer brand heading tag normalization into:

- `gls_normalize_footer_brand_heading_tag_html()`

## Scope

This replaces the inline `preg_replace` path inside `gls_normalize_server_rendered_html()`.

## Behavior goal

No behavior change.

The same regex and replacement output remain active; they now live in one helper.

## Verification plan

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- one-file deploy
- `tools/verify-storefront-baseline.sh` after deploy
