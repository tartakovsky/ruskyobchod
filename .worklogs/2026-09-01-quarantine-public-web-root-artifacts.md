# Quarantine public backup artifacts across production and staging

## Finding

A full production web-root audit found 23 non-log backup artifacts outside the
MU-plugin directory. They included production and staging config backups,
disabled cache PHP files, and historical theme and plugin PHP copies. Several
representative PHP backups returned HTTP 200 with `Content-Type: text/plain`.

WooCommerce operational logs were classified separately and were not moved.
`debug.log` and config backups returned 403, but backup files were still removed
from the public tree rather than relying only on server rules.

## Change

All 23 files were moved with their relative paths and a restoration manifest to:

`/home/u595644545/backups/codex-public-artifacts-20260901`

The live security-surface verifier now rejects backup, dump, archive, and
temporary-source patterns anywhere in the production web root, including the
nested staging site.

## Verification

- five representative former production and staging URLs now return 404;
- the manifest and backup payload both contain exactly 23 files;
- the web-root scan returns zero remaining matching artifacts;
- active PHP syntax and critical Git hashes passed;
- staging isolation, storefront baseline, and live security checks passed.
