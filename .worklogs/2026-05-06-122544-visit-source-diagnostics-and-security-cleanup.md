# 2026-05-06 12:25:44 visit source diagnostics and security cleanup

Goal: explain the suspicious daily visit counts and remove the live cloaking/web-shell surface discovered during verification.

## Visit diagnostics

- Extended `wordpress/wp-content/mu-plugins/rusky-visit-counter.php` to store aggregated source diagnostics alongside daily counts:
  - referrer host
  - request path
  - normalized user-agent family
  - bot-like vs probably-human count
  - accept-language prefix
- Added `tools/report-visit-sources.sh`.
- The data is aggregate only; it does not store raw IP addresses or raw user-agent strings.

## Live security cleanup

- Found live root `index.php` had a base64/eval bootstrap that fetched remote code from `stepmomhub.com`.
- Restored clean `wordpress/index.php` from the repo to live `public_html/index.php`.
- Found open Tiny File Manager surface:
  - `public_html/moon.php`
  - `wp-content/plugins/view-source/moon.php`
- Deactivated and quarantined active file-manager plugins:
  - `file-manager-advanced/file_manager_advanced.php`
  - `wp-file-manager/file_folder_manager.php`
- Quarantined malicious/suspicious files under:
  - `/home/u595644545/backups/security-quarantine-20260506-1018`
- Added `DISALLOW_FILE_EDIT` to live `wp-config.php` after saving backup:
  - `wp-config.php.bak-20260506-1024-security`
- Added a PHP execution block to live `wp-content/uploads/.htaccess` after saving backup:
  - `wp-content/uploads/.htaccess.bak-20260506-1025-security`
- Added `tools/verify-live-security-surface.sh`.

## Verification

- Server `php -l` passed for:
  - updated live `rusky-visit-counter.php`
  - restored live `index.php`
- Googlebot UA now receives the normal Gastronom page, not the Japanese cloaked spam page.
- Public shell URLs return `404`:
  - `/moon.php`
  - `/wp-content/plugins/view-source/moon.php`
- `tools/report-visit-sources.sh 1 10` showed 2026-05-06 traffic was overwhelmingly bot-like:
  - `3490` counted visits
  - `2772` diagnosed visits
  - `2766` bot-like
  - top agent: `bot/googlebot` with `2763`
- `tools/verify-live-security-surface.sh` passed all checks.

## Notes

- The earlier "thousands of unique visits" are not trustworthy as human traffic. The current evidence points to bot/crawler traffic, likely amplified by the cloaking infection.
- Existing unrelated local working-tree changes were not included in this cleanup.
