## Summary

Extracted duplicated storefront title-shell stripping into:

- `gls_strip_storefront_title_shell_html()`

## Updated call sites

- empty-cart shell branch inside `gls_normalize_server_rendered_html()`
- `gls_normalize_front_page_html()`

## Behavior goal

No behavior change.

The same `bradcrumbs` and `vw-page-title` removal remains active at both previous call sites.

## Verification plan

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- one-file deploy
- `tools/verify-storefront-baseline.sh` after deploy
