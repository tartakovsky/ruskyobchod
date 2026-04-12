# dotypos parity constraint

## finding

Read-only live audit plus targeted diff review showed that the missing owner files cannot safely be deployed ahead of `gastronom-stock-fix.php`.

## why

Current live `gastronom-stock-fix.php` is still on the older monolith shape.

It contains Dotypos/preorder `gastronom_*` definitions without the later extraction-guard pattern in multiple areas.

That creates a concrete redeclare risk if:

- `rusky-dotypos-stock-bridge.php`
- `rusky-weight-preorder.php`

are introduced on live before the plugin parity step.

## result

Safe Dotypos parity order is now explicit:

1. `gastronom-stock-fix.php` parity first
2. verify
3. owner-file parity second
4. verify
5. integration proof third

