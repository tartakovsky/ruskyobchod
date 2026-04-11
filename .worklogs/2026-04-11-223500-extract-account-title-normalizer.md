## Summary

Extracted account-title substitutions into:

- `gls_normalize_account_title_text()`

## Updated call sites

- account-page branch inside `the_title`
- `gls_normalize_common_public_title_text()`

## Behavior goal

No behavior change.

The helper centralizes only the account-title substitutions and preserves the same RU/SK outputs.

## Verification plan

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
- one-file deploy
- both tools again after deploy
