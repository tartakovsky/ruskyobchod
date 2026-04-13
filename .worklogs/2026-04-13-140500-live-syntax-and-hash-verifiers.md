## Summary

Added two read-only verifiers for live code integrity before the evening integration window.

## Changes

- added `tools/verify-live-php-syntax.sh`
- added `tools/verify-live-critical-file-hashes.sh`

## Scope

- remote `php -l` for:
  - `gastronom-lang-switcher.php`
  - `gastronom-stock-fix.php`
  - all live `rusky-*.php` MU files
- local-vs-live SHA-256 comparison for:
  - `gastronom-lang-switcher.php`
  - `gastronom-stock-fix.php`
  - shared `rusky-*.php` MU files, excluding deferred `rusky-runtime-shim.php`

## Result

The evening prep now has dedicated read-only checks for syntax integrity and repo/live file parity.
