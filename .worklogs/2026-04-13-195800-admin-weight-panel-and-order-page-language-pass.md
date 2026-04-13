# 2026-04-13 19:58:00 - Admin weight panel and order page language pass

## Goal
- make the Woo weight-confirmation block readable and stable
- reduce mixed RU/SK/EN strings on `order-received`, `view-order`, and `order-pay`

## Changes
- `rusky-preorder-admin.php`
  - replaced narrow inline grid styling with class-based layout
  - widened weight input column
  - added responsive CSS for stacked admin layout
  - normalized footer/action area layout
- `rusky-order-page-language.php`
  - added common English payment/order replacements
  - kept order-page normalization focused on frontend order contexts
  - removed stale special-label dependence from the page normalizer map where possible

## Verification before deploy
- remote syntax green
- `verify-admin-order-screen.sh` green
- `verify-order-page-language.sh` green
