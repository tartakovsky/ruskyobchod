# Staging configured plugin inventory contract

## Finding

The runtime policy verifier checks plugins after request-context filters have
been applied. A plugin could therefore remain configured as active in the
database while being filtered out at runtime.

## Change

The staging isolation verifier now reads the persisted `active_plugins` option
directly from the options table and requires exactly the minimal staging set:
WooCommerce, the Dotypos extension, and `gastronom-stock-fix`.

## Verification

- the normal staging configuration passed;
- temporarily configuring `tinymce-advanced` as active produced the expected
  inventory failure;
- cleanup deactivated the plugin and moved its files back outside the web root;
- the restored staging configuration passed again.
