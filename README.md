# ruskyobchod

Public working repo for the `ruskyobchod.sk` WordPress codebase.

## What is included

- `wordpress/.htaccess`
- safe WordPress root bootstrap files
- `wordpress/wp-content/themes/`
- `wordpress/wp-content/plugins/` except excluded premium/private bundles
- `wordpress/wp-content/mu-plugins/`

## What is intentionally excluded

- `wordpress/wp-config.php` and any secret-bearing environment config
- uploads, caches, backups, logs, migration archives, and generated runtime files
- premium/private plugin bundles:
  - `elementor-pro`
  - `wordpress-seo-premium`
- disabled diagnostic residue:
  - `jetpack.off`
- ad hoc operational scripts found in the live web root

## Why this repo is scoped this way

This repository is meant to be safe to publish and useful for agent-driven maintenance. It captures the versionable code surface without exposing credentials or operational debris from the live Hostinger server.

## Live source

- Hostinger shared hosting
- site root: `/home/u595644545/domains/ruskyobchod.sk/public_html`

## Deployment note

This repo is a source-of-truth working copy, not an automated deploy target. Changes should be pushed back to Hostinger over SSH with a deliberate sync step.
