## Summary

Extracted duplicated footer brand heading normalization into:

- `gls_normalize_footer_brand_heading_html()`

## Updated call sites

- `gls_normalize_storefront_chrome_html()`

## Behavior goal

No behavior change.

This keeps the same retained fallback behavior while reducing duplication inside `gastronom-lang-switcher.php`.

## Verification plan

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- one-file deploy
- `tools/verify-storefront-baseline.sh` after deploy
