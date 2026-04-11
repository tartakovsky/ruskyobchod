## Summary

Extracted repeated `preg_replace_callback(... gls_localize_html_text_segment ...)` usage into `gls_localize_html_text_pattern()` inside `gastronom-lang-switcher.php`.

## Scope

- no behavior change intended
- preserves existing regex patterns and strip-tags behavior
- updates:
  - front page card name localization
  - dropdown menu text localization
  - product tag text localization

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
