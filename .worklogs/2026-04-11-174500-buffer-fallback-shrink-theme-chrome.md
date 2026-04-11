# Buffer Fallback Shrink For Theme Chrome

Date: 2026-04-11

## Scope

Reduce output-buffer fallback logic after moving theme chrome strings to source-level server translation.

## What changed

Updated:

- `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`

Removed the HTML `str_replace` fallback entries that are now covered by source-level `gettext` translation for theme/plugin chrome strings:

- `Cookie Notice`
- `Footer`
- `Top Menu`
- `All Categories`
- `Login / Register`
- `shopping cart`
- `Open Button`
- `Close Button`
- `Scroll Up`
- `Skip to content`

## Result

- output buffering is no longer the primary owner for those theme chrome phrases
- `gls_normalize_storefront_chrome_html()` is smaller and less responsible for generic theme translation
- the server-first migration now has a measurable reduction in fallback surface

## Verification

- `git diff --check` passed
