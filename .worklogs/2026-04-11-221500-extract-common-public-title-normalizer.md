## Summary

Extracted shared public-title normalization into:

- `gls_normalize_common_public_title_text()`

## Updated call sites

- `pre_get_document_title`
- `gls_normalize_public_title()`

## Behavior goal

No behavior change.

The helper only centralizes the shared brand/account title substitutions. Route-specific replacements remain in their original call sites.

## Verification plan

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
- one-file deploy
- both verification tools again after deploy
