## Summary

Extracted duplicated legal/company text normalization into:

- `gls_normalize_legal_company_text_html()`

## Updated call sites

- `gls_normalize_server_rendered_html()`
- `gls_normalize_storefront_chrome_html()`

## Behavior goal

No behavior change.

The helper preserves the exact same phrase mappings and both previous call sites remain active.

## Verification plan

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- one-file deploy
- `tools/verify-storefront-baseline.sh` after deploy
