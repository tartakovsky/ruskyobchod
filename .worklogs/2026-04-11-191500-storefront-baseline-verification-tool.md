## Summary

Added a repo-local storefront baseline verification script:

- `tools/verify-storefront-baseline.sh`

## Purpose

Make repeatable live checks part of the working loop instead of relying on memory and ad hoc curl commands.

## What it checks

- homepage status `200`
- `wp-login.php` status `200`
- RU category status `200`
- RU product status `200`
- home skip-link marker
- RU category sorting label
- RU category add-to-cart label
- RU product stock label
- RU product quantity label
- RU product SKU label
- RU product category label
- RU product related products heading

## Implementation note

The script decodes HTML entities before matching phrases, because the raw storefront HTML contains many labels in entity-encoded form.

## Result

Ran the script against live successfully after creation.
