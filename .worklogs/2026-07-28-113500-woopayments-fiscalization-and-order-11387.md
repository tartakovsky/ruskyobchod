# WooPayments fiscalization and order 11387

## Problem

Paid WooCommerce order `#11387` reduced stock in WooCommerce and Dotypos, but
did not create a Dotypos fiscal receipt.

## Root cause

`rdf_supported_payment_method()` recognized:

- cash on delivery (`cod`);
- gateway IDs containing `stripe`.

The production WooPayments gateway uses `woocommerce_payments`, even though
the payment is processed through Stripe. The order was therefore excluded
before fiscalization. The original fiscalization verification did not include
the real gateway ID used by the shop.

## Permanent fix

Added `woocommerce_payments` to the supported automatic fiscalization methods.
Added `tools/verify-dotypos-fiscalization.sh` with runtime assertions for:

- WooPayments;
- Stripe and Stripe variants;
- COD;
- exclusion of ordinary bank transfer.

The candidate passed Hostinger PHP lint. The live module was backed up under
`/home/u595644545/backups/ruskyobchod-2026-07-28-woopayments-fiscalization`
before the single-file deploy. The post-deploy regression test passed.

## Order 11387 recovery

Preflight confirmed that the old warehouse-sale path had already reduced all
three products and set `_dotypos_stock_synced=1`.

Created fiscal receipt:

- WooCommerce order: `11387`
- Dotypos external ID: `WC-11387`
- Dotypos order ID: `2733160058614173`
- document type: `RECEIPT`
- paid: `true`
- canceled: `false`
- fiscal error meta: empty

The POS receipt made its normal stock deduction. A guarded recovery script
verified each exact movement before applying an equal warehouse offset:

- product `1587755042755191`: `2 -> 0 -> 2`
- product `1587755042764075`: `20 -> 18 -> 20`
- product `1587755042925359`: `10 -> 8 -> 10`

Each item and the order carry idempotent offset markers. No blind or duplicate
stock addition was performed.

The operator confirmed that the physical receipt printed.

## Final audit

All eligible Stripe/WooPayments/COD web sales since 2026-07-01 were checked
against live Dotypos:

- eligible: 3
- remotely verified fiscal receipts: 3
- missing: 0

Orders `11383`, `11385`, and `11387` are all fiscalized and paid remotely.
