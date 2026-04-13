# Fix preorder gross line totals

## Context
Customer preorder confirmation email and admin weighted item summary used net line totals while storefront/order pages showed gross totals. For B2C storefront prices, this produced inconsistent customer-visible sums.

## Change
- added owner helper in `rusky-preorder-notifications.php` to render gross line totals (`item total + item total tax`) in weight-confirmation emails
- added matching owner helper in `rusky-preorder-admin.php` so admin weighted-item summary also shows gross item totals

## Verification plan
- remote syntax check both files on Hostinger PHP 7.4 after deploy
- verify existing order `11101` still opens on `order-received`
- inspect live order `11101` admin/customer totals consistency
