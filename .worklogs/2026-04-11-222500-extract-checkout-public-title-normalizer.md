## Summary

Extracted checkout/order title substitutions for public-title flows into:

- `gls_normalize_checkout_public_title_text()`

## Updated call sites

- `pre_get_document_title`
- `gls_normalize_public_title()`

## Behavior goal

No behavior change.

The helper preserves the difference between the two call sites:

- `pre_get_document_title` does not normalize generic `Заказ`
- `gls_normalize_public_title()` still does

## Verification plan

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
- one-file deploy
- both tools again after deploy
