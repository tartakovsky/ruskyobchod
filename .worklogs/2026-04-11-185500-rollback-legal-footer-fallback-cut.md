## Summary

Rolled back the previous attempt to remove footer/legal fallback replacements from `gls_normalize_storefront_chrome_html()`.

## Reason

Immediate browser verification on RU category and product pages showed the footer legal block regressed back to Slovak:

- `Slovenská republika`
- `Zapísaná v OR OS Bratislava I,`
- `Oddiel: Sro, Vložka č. 182562/B`

## Action

Restored only the removed footer/legal replacements in `gastronom-lang-switcher.php`.

## Rule applied

The step was treated as failed and rolled back immediately instead of leaving the regression in place.
