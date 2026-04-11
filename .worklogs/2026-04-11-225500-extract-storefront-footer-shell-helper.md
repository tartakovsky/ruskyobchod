## Summary

Extracted the repeated storefront footer shell normalization calls into `gls_normalize_storefront_footer_shell_html()` inside `gastronom-lang-switcher.php`.

## Scope

- no behavior change intended
- preserves retained footer/legal/cookie fallback surface
- removes duplicated helper invocation branches in `gls_normalize_storefront_chrome_html()`

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
