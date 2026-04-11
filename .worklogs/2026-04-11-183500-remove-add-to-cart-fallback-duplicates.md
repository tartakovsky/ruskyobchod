## Summary

Removed duplicate add-to-cart HTML fallback replacements from `gls_normalize_storefront_chrome_html()` after confirming category markup already renders Russian add-to-cart labels and success text directly in server output.

## Removed fallback entries

- `aria-label="Pridať do košíka..."`
- `» pridaný do košíka`
- reverse SK replacements for the same strings

## Safety

This step only removes confirmed duplicates. It does not add new runtime behavior.

## Verification

- category raw HTML already contained Russian add-to-cart aria labels and success messages before this removal
- `git diff --check`
