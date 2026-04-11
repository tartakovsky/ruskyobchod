## Summary

Added source-level translation coverage for cookie notice button strings in the existing `gettext` owner path.

## Changes

Extended `gls_translate_theme_chrome_phrase()` with:

- `Ok` <-> `Ок`
- `No` <-> `Нет`

## Safety

This is additive only.

The existing storefront fallback for cookie notice buttons remains in place, so this step reduces dependency on the late HTML layer without removing it.

## Verification plan

- `git diff --check`
- one-file deploy
- `tools/verify-storefront-baseline.sh`
- browser check that cookie notice button/close aria remain RU on homepage
