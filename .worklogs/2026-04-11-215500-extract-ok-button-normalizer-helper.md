## Summary

Extracted regex-based button normalization into:

- `gls_normalize_ok_button_html()`

## Scope

This replaces the inline `<button>Ok/Ок</button>` normalization inside the empty-cart shell branch of `gls_normalize_server_rendered_html()`.

## Behavior goal

No behavior change.

The exact same regex and replacement output remain active.

## Verification plan

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- one-file deploy
- `tools/verify-storefront-baseline.sh` after deploy
