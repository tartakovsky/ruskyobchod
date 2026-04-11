## Summary

Extracted the duplicated gettext translation-application pattern into `gls_apply_phrase_translation()` inside `gastronom-lang-switcher.php`.

## Scope

- no behavior change intended
- centralized repeated translator application for:
  - account / checkout phrases
  - theme chrome phrases
  - WooCommerce storefront phrases

## Verification

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
