# WordPress core 6.9.7 update

## Scope

Updated the production WordPress core from `6.9.5` to `6.9.7` after the same
minor version passed on the isolated staging environment. `wp-content`, the
database, `wp-config.php`, plugins, themes, orders, and application settings
were not changed by the core update.

## Safety controls

- Verified the pre-update `6.9.5` core against official checksums.
- Verified available disk space.
- Created a tested, 44 MB core-only archive outside the web root:
  `/home/u595644545/backups/codex-core/ruskyobchod-core-6.9.5-pre-6.9.7-20260831.tar.gz`.
- Excluded `wp-content`, `wp-config.php`, and `.user.ini` from the archive.
- Used the official `ru_RU` WordPress 6.9.7 package.

## Verification

- `wp core verify-checksums --version=6.9.7 --locale=ru_RU` passed.
- Homepage and login returned HTTP 200.
- Storefront and checkout checks passed.
- Security surface, WordPress bootstrap, Dotypos stock, runtime plugin policy,
  and output-buffer policy checks passed.

The generic English checksum lookup reported a locale-specific mismatch in
`wp-includes/version.php`; the matching Russian-package checksum verification
passed, so no rollback was necessary.
