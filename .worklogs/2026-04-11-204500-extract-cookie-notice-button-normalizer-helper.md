## Summary

Extracted duplicated cookie-notice button normalization into:

- `gls_normalize_cookie_notice_button_html()`

## Updated call sites

- `gls_normalize_server_rendered_html()`
- `gls_normalize_storefront_chrome_html()`

## Behavior goal

No behavior change.

The same button text and aria replacements remain active; they now flow through one helper.

## Verification plan

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- one-file deploy
- `tools/verify-storefront-baseline.sh` after deploy
