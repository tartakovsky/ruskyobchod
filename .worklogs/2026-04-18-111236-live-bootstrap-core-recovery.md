# Live Bootstrap Core Recovery

## Goal
- Restore the live site from a full-site `500` outage without leaving the recovery as an undocumented emergency patch.
- Convert the outage learnings into a permanent bootstrap verification step in the repo.

## What failed
- On 2026-04-18 the live homepage and `wp-login.php` both returned `500`.
- The first blocking fatal was WordPress core calling `str_ends_with()` while the live web handler still ran on `PHP 7.4.33`.
- After switching the site to `PHP 8.2`, a second broken state surfaced:
  - live `wp-config.php` was missing from both allowed bootstrap paths
  - live WordPress core was incomplete/mixed (`wp-includes/class-wp-translation-controller.php` missing)

## Recovery
- Set the live site PHP handler to `application/x-lsphp82` through `wordpress/.htaccess`.
- Restored `wp-config.php` on live from `/home/u595644545/backups/ruskyobchod-2026-04-09-093435/wp-config.php`.
- Restored the official WordPress `6.9.4` core on live for:
  - `wp-admin/`
  - `wp-includes/`
  - root bootstrap/core files
- Left `wp-content/` untouched.

## Permanent repo changes
- Track the `PHP 8.2` handler in `wordpress/.htaccess`.
- Added `tools/verify-live-bootstrap-surface.sh` to verify:
  - homepage `200`
  - `wp-login.php` `200`
  - web PHP header is `8.2`
  - `wp-config.php` exists in an allowed bootstrap path
  - required core files exist on live
- Added that bootstrap check to `tools/verify-tuesday-readiness.sh`.

## Root cause
- The outage did not start in `wp-content`.
- The root starting point was a production bootstrap drift:
  - WordPress core reached a state that required PHP 8 semantics in the web runtime
  - the live web handler remained on PHP 7.4
  - the root bootstrap/core surface was already inconsistent or partially updated, which left the site one more failure away from a total outage

## Future prevention
- Treat WordPress root bootstrap files and `.htaccess` as controlled infrastructure, not incidental server state.
- Never allow core/PHP changes without an immediate live bootstrap verification.
- Keep `wp-config.php` outside repo, but verify its live presence as an operational invariant.
- Run the readiness suite, including bootstrap verification, after any hosting/runtime/core intervention.
