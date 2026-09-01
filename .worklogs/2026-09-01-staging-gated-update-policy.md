# Gate production plugin and theme updates through staging

## Finding

The production `auto_update_plugins` option contained 16 entries. It included
critical active components such as WooCommerce, Elementor, and the Dotypos
extension, plus stale entries for plugins that had already been moved outside
the web root. An unattended update could therefore bypass staging and the
architecture gate.

## Change

The exact previous JSON list was saved outside the web root at:

`/home/u595644545/backups/codex-update-policy-20260901/auto_update_plugins.json`

Production plugin auto-updates were set to an empty list. Theme auto-updates
were already empty. Default WordPress minor-core update behavior was not
disabled.

The production plugin inventory verifier now requires plugin and theme
auto-update lists to remain empty so updates are staged and verified first.

## Verification

- the production active and installed plugin inventories passed;
- the storefront baseline and live security surface passed;
- no plugin, theme, core file, content, order, or integration setting changed.
