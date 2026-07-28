# Restore WooCommerce order actions for Packeta

## Context

Paid order `#11387` uses `packeta_method_zpointsk`. The Packeta order row contains
the selected pickup point, but its manual weight and packet ID are empty. In the
classic WooCommerce order editor the Packeta weight field was visible, while the
standard `Update` control was absent, so the entered weight could not be saved.

## Root cause

The project admin cleanup removed the standard
`woocommerce-order-actions` metabox from every order screen. This is the same
regression pattern previously documented for the missing GLS metabox: a
project-specific cleanup removed a box owned by another component.

The hidden ID existed in both:

- `wp-content/mu-plugins/rusky-preorder-admin.php`
- the fallback implementation in
  `wp-content/plugins/gastronom-stock-fix/gastronom-stock-fix.php`

## Change

Removed `woocommerce-order-actions` from both hidden-metabox lists. Existing
cleanup of duplicate notes and weight-confirmation UI remains unchanged.

Extended `tools/verify-admin-order-screen.sh` with a regression assertion that
the standard order-actions metabox survives the cleanup and is absent from the
hidden list.

## Packeta pipeline audit

Read-only live diagnostics for order `#11387` confirmed:

- official Packeta plugin `2.2.0` is active;
- Packeta API password and Sender are configured;
- shipping method is `packeta_method_zpointsk`;
- pickup point `18413` is stored in `wp_packetery_order`;
- the packet has not been submitted (`packet_id` is empty);
- the saved manual weight is empty;
- all ordered products currently have zero WooCommerce product weight.

Therefore the expected flow after this fix is:

1. enter the parcel weight;
2. use the restored WooCommerce `Update` button;
3. submit the saved order to Packeta;
4. print the label after Packeta returns a packet ID.

No packet was submitted and no label was created during diagnosis.

## Verification

- candidate PHP syntax passed on the Hostinger PHP runtime;
- shell syntax for the changed verifier passed;
- `git diff --check` passed;
- deployed files were backed up first under
  `/home/u595644545/backups/ruskyobchod-2026-07-28-packeta-order-actions`;
- post-deploy `tools/verify-admin-order-screen.sh` passed, including the new
  order-actions assertions;
- post-deploy live PHP syntax verification passed;
- anonymous homepage and `wp-login.php` both returned HTTP 200;
- unrelated existing marketing worktree changes were preserved.
