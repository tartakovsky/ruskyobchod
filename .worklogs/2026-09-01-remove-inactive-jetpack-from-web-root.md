# Move inactive Jetpack outside the production web root

## Finding

`wp-content/plugins/jetpack.off` contained Jetpack 15.6 but was inactive,
absent from both the regular and network active-plugin options, and had no
references from `wp-config.php`, MU plugins, or the active theme tree. The
unused directory occupied about 86 MB inside the public plugin directory.

## Change

The directory was moved, not deleted, to:

`/home/u595644545/backups/codex-inactive-plugins-20260901/jetpack.off`

Restoring it is a single reverse move to
`wp-content/plugins/jetpack.off`.

## Verification

- Jetpack no longer appears in the production plugin inventory;
- the storefront baseline passed;
- the live security-surface verification passed;
- no active plugin, runtime code, database option, or site content changed.
