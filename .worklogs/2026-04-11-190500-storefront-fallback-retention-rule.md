## Summary

Locked in a stricter rule for server-first storefront cleanup after the failed footer/legal fallback removal attempt.

## Rule

Do not remove a fallback because another layer appears to translate the same phrase.

Only remove a fallback after proving the final storefront output is already correct without that exact fallback.

## Required proof before removing a fallback

1. Disable or bypass only the candidate fallback locally.
2. Check the affected live path in the browser or capture the post-buffer output for that path.
3. Confirm the exact target strings still render correctly without the fallback.
4. Only then remove the fallback and deploy one file.

## Current retained fallback surface

Keep these storefront fallback replacements until direct proof exists that the final output is correct without them:

- footer/legal company block
  - `Slovenská republika`
  - `Zapísaná v OR OS Bratislava I,`
  - `Oddiel: Sro, Vložka č. 182562/B`
- cookie notice buttons
  - `Ok`
  - `Nie`
- footer brand heading normalization
  - `Gastronom` / `Гастроном`

## Already proven safe to remove

- `Domov` / `Главная`
- `Kategória:` / `Категория:`
- `Množstvo produktu` / `Количество товара`
- add-to-cart aria/success fallback duplicates on category/product storefront paths

## Reason

The failed footer/legal removal showed that apparent duplicate translation logic in another function does not prove independence of the final rendered output.
