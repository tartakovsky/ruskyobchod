# Move inactive legacy Yoast Premium outside the production web root

## Finding

`wordpress-seo-premium` 5.7 was inactive and occupied about 19 MB in the public
plugin directory. It was absent from regular and network active-plugin options,
had no references from production configuration, MU plugins, or themes, and
had no pending Yoast Action Scheduler jobs.

## Change

The plugin directory was moved, not deleted, to:

`/home/u595644545/backups/codex-inactive-plugins-20260901/wordpress-seo-premium`

## Verification

- the plugin no longer appears in the production inventory;
- the storefront baseline passed;
- the WordPress sitemap remained available and valid;
- the live security-surface verification passed;
- no active plugin, SEO data, database option, runtime file, or site content
  changed.
