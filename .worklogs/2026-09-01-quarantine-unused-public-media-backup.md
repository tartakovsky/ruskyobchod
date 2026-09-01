# Move unused legacy media backup outside the production web root

## Finding

`wp-content/backup` contained 18,297 image files (about 967 MB). Production
posts, post meta, and options contained no references to `/wp-content/backup/`.
The directory index was denied, but individual images were publicly available.

## Change

The complete directory was moved, not deleted, to:

`/home/u595644545/backups/codex-public-media-backup-20260901`

The security verifier now requires that the legacy directory remain outside
the web root. It also verifies that WooCommerce logs deny direct access, the
debug log and Git metadata are not public, and the nested staging tree is not
served through the production URL.

## Verification

- the sampled legacy image URL now returns 404;
- all 18,297 files are present in the external backup directory;
- the storefront baseline and critical file hashes passed;
- production and staging debug settings remain disabled;
- WooCommerce logs and `debug.log` return 403;
- `/staging-gastronom/` on the production host returns 404.
