# 2026-04-15 11:09 front page subtitle cleanup

## Context

After the previous storefront language cleanup, RU front page still exposed a visible mixed-language shell string in the hero subtitle:

- `Братислава • Palisády`

This is storefront shell, not product content, so it should follow the current server-side language path.

## Change

Added a tiny front-page-only language normalizer:

- `wordpress/wp-content/mu-plugins/rusky-front-page-language.php`

This normalizes only the hero subtitle string by current storefront language and does not touch product/business logic.

Also kept the earlier local fallback in:

- `wordpress/wp-content/mu-plugins/rusky-language-switcher-lite.php`

but the live effective fix is now the dedicated front-page normalizer.

## Result

- RU front page hero subtitle now resolves as `Братислава • Палисады`
- SK front page stays `Bratislava • Palisády`
- readiness pack remains green

## Verification

- deployed only the new `mu-plugin` file over SSH
- verified the raw live HTML subtitle in RU and SK
- ran `./tools/verify-tuesday-readiness.sh` successfully after the change
