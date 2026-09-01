# Move inactive Real-Time Find and Replace outside the production web root

## Finding

`real-time-find-and-replace` 4.3 was inactive, absent from regular and network
active-plugin options, and had no pending related Action Scheduler jobs.

References in `rusky-disable-far.php` and retired `.off` MU files are defensive
blocklists. They do not load or depend on the plugin and remain safe when its
files are absent.

## Change

The plugin directory was moved, not deleted, to:

`/home/u595644545/backups/codex-inactive-plugins-20260901/real-time-find-and-replace`

## Verification

- the plugin no longer appears in the production inventory;
- all production runtime-policy contexts passed;
- the output-buffer ownership policy passed;
- the storefront baseline and live security-surface checks passed;
- no active-plugin option, runtime file, database setting, or content changed.
