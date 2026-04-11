## Summary

Extracted repeated HTML text-segment localization callbacks into:

- `gls_localize_html_text_segment()`

## Updated call sites

- front-page category card name normalization
- dropdown menu link text normalization
- product tag link text normalization

## Behavior goal

No behavior change.

The helper preserves the same decode/localize/escape flow, with an explicit `strip_tags` mode for card titles.

## Verification plan

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- one-file deploy
- `tools/verify-storefront-baseline.sh` after deploy
