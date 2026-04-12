# dotypos live boundary audit

## summary

Before any Dotypos integration cutover, live/repo parity was checked in read-only mode.

## verified facts

- live `gastronom-lang-switcher.php` matches repo
- live `gastronom-stock-fix.php` does **not** match repo
- live does **not** contain:
  - `mu-plugins/rusky-dotypos-stock-bridge.php`
  - `mu-plugins/rusky-weight-preorder.php`

## implication

Dotypos/preorder integration on live still runs through the old owner path in:

- `wordpress/wp-content/plugins/gastronom-stock-fix/gastronom-stock-fix.php`

That means the next safe move is **not** an integration cutover.

The next safe move is:

1. parity block
2. verify file set
3. only then integration proof

## stop line

Do not treat local owner split as live reality until:

- live file set matches the intended boundary
- `gastronom-stock-fix.php` parity is explained or resolved

