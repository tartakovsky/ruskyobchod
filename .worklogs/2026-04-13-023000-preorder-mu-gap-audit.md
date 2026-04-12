## Preorder MU Gap Audit

Date: 2026-04-13

### Scope

Repo-vs-live audit for `wp-content/mu-plugins/rusky-*.php` after the first Dotypos parity block.

### Finding

Live `mu-plugins` was still missing multiple repo owner files.

Critical missing files for the preorder/Dotypos flow:

- `rusky-preorder-notifications.php`
- `rusky-preorder-admin.php`

Why these two were critical:

- without `rusky-preorder-notifications.php`, `gastronom_mark_weight_confirmed_order_ready()` and preorder email ownership were effectively empty wrappers on live
- without `rusky-preorder-admin.php`, the weight-confirmation admin/AJAX path was effectively empty on live

### Result

This audit changed the next action.

Instead of continuing business-flow proof on an incomplete live file set, the work switched to a narrow parity rollout for those two files first.
