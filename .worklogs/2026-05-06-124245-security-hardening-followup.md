# 2026-05-06 12:42:45 security hardening followup

Goal: continue post-compromise hardening without requiring new hosting or WordPress passwords.

## Actions

- Reviewed WordPress administrators and found two suspicious accounts:
  - `osdibijl` / `ruskyobchodnt@gmail.com` / registered 2026-04-21
  - `xevnijso` / `ruskyobchodbj@hotmail.com` / registered 2026-04-20
- Quarantined both accounts by:
  - changing their role to `subscriber`
  - assigning random long passwords
  - adding `rusky_security_quarantined_admin` user meta
- Moved remaining quarantined files/directories out of `public_html`:
  - `.tmb.quarantined-20260506-1019`
  - `moon.php.quarantined-`
  - `wp-content/wp-block.php.quarantined-20260506-1020`
  - `wp-content/wp-ver.php.quarantined-20260506-1020`
- Added and deployed `wordpress/wp-content/mu-plugins/rusky-security-guard.php`.
  - It removes known file-manager plugins from `active_plugins` if they are reactivated.
  - It logs a warning if `DISALLOW_FILE_EDIT` is missing.
- Extended `tools/verify-live-security-surface.sh` to check:
  - security guard presence
  - suspicious accounts are not administrators

## Verification

- Server `php -l` passed for live `rusky-security-guard.php`.
- `tools/verify-live-security-surface.sh` passed all checks:
  - Googlebot sees Gastronom
  - known cloaked spam is absent
  - public shell URLs are absent
  - file-manager plugins are inactive
  - security guard is present
  - `DISALLOW_FILE_EDIT` is enabled
  - uploads PHP execution block is present
  - known suspicious users are not administrators

## Notes

- Password rotation remains outstanding and should be completed by the owner in Hostinger/hPanel.
- The quarantined suspicious users were not deleted so there remains an audit trail.
