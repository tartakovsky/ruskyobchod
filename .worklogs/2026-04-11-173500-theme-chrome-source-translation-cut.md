# Theme Chrome Source Translation Cut

Date: 2026-04-11

## Scope

Move a subset of storefront chrome translation from output-buffer string replacement to source-level server translation.

## What changed

Updated:

- `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`

Added:

- `gls_translate_theme_chrome_phrase()`

Extended the existing `gettext` filter so source strings from these domains are translated server-side before HTML normalization:

- `food-grocery-store`
- `cookie-notice`

## Covered phrase set

- `Footer`
- `Top Menu`
- `All Categories`
- `Login / Register`
- `shopping cart`
- `Open Button`
- `Close Button`
- `Scroll Up`
- `Skip to content`
- `Cookie Notice`

## Result

- these storefront/theme strings no longer depend only on later HTML `str_replace`
- output-buffer normalization for those phrases is now a fallback layer rather than the first translation mechanism
- this continues the server-first migration without widening the live risk surface

## Verification

- `git diff --check` passed
