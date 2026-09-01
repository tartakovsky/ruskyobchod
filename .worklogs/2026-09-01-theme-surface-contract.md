# Reduce and pin the production and staging theme surface

## Finding

Staging selected Twenty Twenty-Five in its database but had an empty themes
directory, so WordPress reported `theme_not_found`. Production contained the
active standalone Food Grocery Store theme plus five inactive themes, four of
which were unnecessary and outdated.

## Change

- restored official Twenty Twenty-Five 1.5 as the active staging theme;
- upgraded the inactive production fallback from Twenty Twenty-Five 1.0 to 1.5;
- moved Catch Store, Storefront, Twenty Twenty-Four, and Twenty Twenty-Three to
  `/home/u595644545/backups/codex-theme-surface-20260901/unused`;
- retained the previous Twenty Twenty-Five 1.0 at
  `/home/u595644545/backups/codex-theme-surface-20260901/twentytwentyfive-1.0`;
- added an architecture verifier for the exact theme inventories, active themes,
  and versions on production and staging.

## Verification

- production uses Food Grocery Store 1.2.8 and retains Twenty Twenty-Five 1.5
  as its only fallback;
- staging uses a valid Twenty Twenty-Five 1.5 installation;
- staging isolation, storefront, Elementor compatibility, critical hashes, and
  security checks passed.
