# Remove Dotypos maintenance write endpoint

## Scope

Removed the disabled `POST /gls/v1/dotypos-fix` maintenance route and its
write-capable implementation from `rusky-dotypos-maintenance.php`.

The change intentionally did not alter the active logged-in frontend boundary
in the same file. That boundary continues to keep the vendor Dotypos plugin
out of ordinary logged-in frontend `GET`/`HEAD` requests while preserving
admin, AJAX, REST, cron, webhook, and anonymous paths.

## Preserved behavior

- `GET /gls/v1/dotypos-diag` remains available to administrators.
- The existing `option_active_plugins` boundary is byte-identical to the
  pre-change version.
- No WooCommerce order, stock, fiscalization, email, or Action Scheduler hook
  was changed.

## Verification

- Candidate passed PHP 8.2 lint before deployment in a temporary server file.
- A server-side backup was created at
  `/home/u595644545/backups/rusky-20260831-rdm-write-route-removal/`.
- Production file SHA-256 matches Git after deployment.
- Homepage and `wp-login.php` return `200`.
- REST index retains `dotypos-diag` and no longer exposes `dotypos-fix`.
- Dotypos read-only product parity passed for all three control products.
- Dotypos Action Scheduler: overdue `0`, recent failures `0`.
- Full critical file hash parity passed.
