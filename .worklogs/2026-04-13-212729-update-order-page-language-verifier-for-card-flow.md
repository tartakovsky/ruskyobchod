## Summary

Update the order-page language verifier to match the current payment model after removing bank transfer from the site.

## Why

`tools/verify-order-page-language.sh` was still creating `bacs` orders and asserting bank-transfer labels even though the live payment model now uses ordinary store methods and no longer exposes `bacs`.

That made the verifier stale even though the live owner behavior had already moved on.

## Change

- Switched the synthetic verification order from `bacs` to `woocommerce_payments`.
- Updated RU assertions from `Банковский перевод` to `Оплата картой`.
- Updated SK assertions from `Bankový prevod` to `Platba kartou`.

## Intended Result

The verifier now checks the actual permanent order-page payment path instead of an already removed branch.

