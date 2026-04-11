# Theme Skip-Link Source Fix

Date: 2026-04-11

## Scope

Remove the duplicated skip-link text at the source in the active theme instead of normalizing it later through output buffering.

## What changed

Updated:

- `wordpress/wp-content/themes/food-grocery-store/header.php`

The theme header no longer renders nested duplicate skip-link text:

- before: link text plus nested duplicate screen-reader span
- after: one server-rendered skip-link label

## Result

- the duplicate `Skip to content` / `Перейти к содержимому` source output is removed at the theme level
- this reduces another reason for fallback HTML normalization in the language layer

## Verification

- `git diff --check` passed
