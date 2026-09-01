# Move inactive Google Analytics Dashboard outside the production web root

## Finding

`google-analytics-dashboard-for-wp` 9.1.2 was inactive, absent from both the
regular and network active-plugin options, and occupied about 51 MB in the
public plugin directory.

The order-page MU plugin mentions `google-analytics-dashboard-for-wp/gadwp.php`
only in a defensive list that removes selected plugins from logged-in order
requests. That filter safely ignores an entry that is not installed and does
not load or depend on the plugin.

## Change

The plugin directory was moved, not deleted, to:

`/home/u595644545/backups/codex-inactive-plugins-20260901/google-analytics-dashboard-for-wp`

## Verification

- the plugin no longer appears in the production inventory;
- the storefront baseline passed;
- RU and SK order-received and order-pay language checks passed;
- the live security-surface verification passed;
- no active plugin, database option, runtime file, or site content changed.
