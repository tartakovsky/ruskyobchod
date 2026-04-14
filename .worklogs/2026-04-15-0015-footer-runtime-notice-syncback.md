## Context

Readonly server log audit showed old PHP notices in:
- `wp-content/themes/food-grocery-store/footer.php`

The cause was the historical use of an undefined variable:
- `$food_grocery_store_colmd`

While auditing, the live server file was checked directly and it already contained the corrected variable:
- `$colmd`

That meant production had been fixed previously, but the repo had not been synced back.

Per handoff rules, repo drift must be corrected immediately.

## Change

Synced the footer runtime notice fix back into the repo only:
- [`wordpress/wp-content/themes/food-grocery-store/footer.php`](/Users/alexandertartakovsky/ruskyobchod/wordpress/wp-content/themes/food-grocery-store/footer.php)

Replaced the four stale references:
- `$food_grocery_store_colmd`

with the existing local variable:
- `$colmd`

## Live impact

None.

This was not a deploy.
The live server already had the corrected version.

## Result

- repo now matches the already-correct live footer template
- historical footer notice cause is no longer represented as stale code in version control
- this reduces repo/live drift without touching the running site
