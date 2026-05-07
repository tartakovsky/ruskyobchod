## Summary

Cancelled live order `#11271` after the confirmed-weight email flow had already completed, then verified that WooCommerce and Dotypos stock were both restored for every confirmed preorder-weight line item.

## Order facts

- Order: `11271`
- Created: `2026-05-01 09:47:41` local time
- Confirmed weights processed: `2026-05-01 09:49:42`
- Cancelled: `2026-05-01 09:55:46`
- Final status after action: `cancelled`

## Confirmed preorder items in the order

- `10615` Ryba Maslová Eskalop (kg): actual weight `0.23 kg`
- `11238` Saláma Ukr. vypraž.: actual weight `0.32 kg`
- `10564` Slanina Domaca "Berger": actual weight `0.35 kg`
- `10310` Ryba Makrela Údeného Chladenia: actual weight `0.41 kg`

All four items already had:

- `_gastronom_weight_confirmed = yes`
- `_gastronom_weight_cash_synced = yes`

After cancellation all four items also had:

- `_gastronom_weight_cash_restored = yes`

## Verified post-cancel stock state

### WooCommerce

- `10615`: `_gastronom_cash_stock_kg = 1.72`, `stock_quantity = 4`
- `11238`: `_gastronom_cash_stock_kg = 2.71`, `stock_quantity = 6`
- `10564`: `_gastronom_cash_stock_kg = 2.13`, `stock_quantity = 5`
- `10310`: `_gastronom_cash_stock_kg = 3.765`, `stock_quantity = 8`

### Dotypos

Verified with `tools/verify-dotypos-product-state.sh` for all four product IDs.

Remote `stockQuantityStatus` matched the expected local `_gastronom_cash_stock_kg` for:

- `10615`
- `11238`
- `10564`
- `10310`

## Notes

- The restore path was triggered by the live hook on `woocommerce_order_status_cancelled`.
- The order contained one additional confirmed preorder product beyond the three newly configured items: `10310` Makrela cold-smoked.
