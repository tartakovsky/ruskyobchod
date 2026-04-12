# stock-fix parity rollout

## summary

`gastronom-stock-fix.php` parity rollout was executed as a single-file controlled deploy after:

- read-only live boundary audit
- remote syntax validation of the target file
- creation of a live backup copy

## rollout

Changed live file:

- `wordpress/wp-content/plugins/gastronom-stock-fix/gastronom-stock-fix.php`

Backup created on live before deploy:

- `gastronom-stock-fix.php.pre-parity-2026-04-12-2359`

## result

Post-deploy verification stayed green for all current live contours:

- storefront baseline
- checkout shell baseline
- account shell baseline
- commerce shell RU
- commerce shell SK

## implication

The first half of the Dotypos parity block is complete:

- live `gastronom-stock-fix.php` now matches the intended parity target closely enough to proceed to owner-file parity

