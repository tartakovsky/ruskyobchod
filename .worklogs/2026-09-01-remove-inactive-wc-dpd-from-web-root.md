# Move inactive WC DPD outside the production web root

## Finding

`wc-dpd` 8.4.0 was inactive and occupied about 1.4 MB in the public plugin
directory. It was absent from regular and network active-plugin options, had
no references from production configuration, MU plugins, or themes, and had
no DPD options or pending Action Scheduler jobs.

## Change

The plugin directory was moved, not deleted, to:

`/home/u595644545/backups/codex-inactive-plugins-20260901/wc-dpd`

## Verification

- the plugin no longer appears in the production inventory;
- RU and SK commerce checks passed, including local pickup, GLS, Packeta, COD,
  and card payment;
- the checkout shell and live security-surface checks passed;
- no active plugin, shipping setting, database option, runtime file, or site
  content changed.
