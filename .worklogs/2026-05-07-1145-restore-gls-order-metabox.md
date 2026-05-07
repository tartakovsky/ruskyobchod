# Restore GLS order metabox

## Context

Order `#11277` used GLS address delivery, but the order edit screen did not show the `GLS Shipping Info` metabox. The GLS plugin itself registered the metabox, and the order had no GLS label or tracking metadata yet.

## Finding

The metabox was being removed by local admin cleanup code:

- `rpa_hidden_meta_box_ids()` in `wordpress/wp-content/mu-plugins/rusky-preorder-admin.php`
- fallback hidden list in `wordpress/wp-content/plugins/gastronom-stock-fix/gastronom-stock-fix.php`

Both lists included `gls_shipping_info_meta_box`, so WordPress registered the GLS box and then the custom cleanup removed it from order screens.

## Change

Removed `gls_shipping_info_meta_box` from both hidden-metabox lists.

Also reverted an earlier unnecessary local change to the upstream GLS plugin order screen registration and redeployed the original GLS plugin file, because the root cause was the custom hiding logic.

## Deploy

Deployed only these files to live:

- `wp-content/mu-plugins/rusky-preorder-admin.php`
- `wp-content/plugins/gastronom-stock-fix/gastronom-stock-fix.php`
- `wp-content/plugins/gls-shipping-for-woocommerce/includes/admin/class-gls-shipping-order.php`

## Verification

- PHP syntax checked staged files on the server before deploy.
- Server-side metabox registration check for order `11277`: `before=yes after=yes`.
- Anonymous homepage returned `HTTP/2 200`.
- `wp-login.php` returned `HTTP/2 200`.

## Notes

No GLS label was generated and no pickup was scheduled.
