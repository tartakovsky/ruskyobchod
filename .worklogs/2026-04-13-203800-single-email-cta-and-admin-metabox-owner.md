# 2026-04-13 20:38:00 - Single email CTA and admin metabox owner

## Goal
- keep exactly one CTA in the weight-confirmation email
- stop rendering the weight-confirmation UI inside the narrow inline order column

## Changes
- `rusky-preorder-notifications.php`
  - removed secondary CTA for `non-cod`
  - renamed the `cod` CTA to "Проверить и подтвердить заказ"
- `rusky-preorder-admin.php`
  - moved the weight-confirmation UI ownership to a normal/high meta box
  - removed the narrow inline order-details placement by unregistering the old hook
  - kept the same confirmation controls and AJAX path

## Verification before deploy
- remote syntax green
- `verify-admin-order-screen.sh` green
