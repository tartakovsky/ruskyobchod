# Fiscalize web orders since July 2026

## request

Fiscalize the Borodinsky bread order and verify that every completed web sale after 2026-06-30 has a Dotypos fiscal receipt.

## preflight audit

Two Stripe/COD orders in processing/completed/on-hold state existed from 2026-07-01 onward:

- WooCommerce `11383`, COD, EUR 18.31
- WooCommerce `11385`, COD, EUR 12.55 (Borodinsky bread)

Both had already been reduced from Dotypos stock by the older live `/warehouses/:id/sales` path. Neither had a fiscal order ID or a matching remote Dotypos order.

## runtime compatibility fixes

Updated `rusky-dotypos-fiscalization.php` so that:

- a Dotypos order search returning HTTP 404 is treated as an empty result, not an API failure
- POS Action `validity` is sent in Unix milliseconds as expected by the live device
- the optional `take-away` flags are omitted because takeaway mode is disabled on this register
- fiscal receipts use `print-type=local` so the register prints them physically; the initial historical backfill used email delivery and therefore only signaled on the device

The first two probes were rejected before creating a receipt:

- result `1007`: expired validity due to seconds/milliseconds mismatch
- result `8001`: takeaway is not enabled

Their failed local state was cleared only after confirming those result codes. No fiscal or stock transaction was created by either rejected probe.

## completed fiscalization

- order `11383`
  - Dotypos order `2660360362341491`
  - document type `RECEIPT`
  - paid `true`
  - Woo/Dotypos total EUR 18.31 / EUR 18.31
  - stock before/after for product `2761897669596621`: `0` / `0`
- order `11385`
  - Dotypos order `775548324230269`
  - document type `RECEIPT`
  - paid `true`
  - Woo/Dotypos total EUR 12.55 / EUR 12.55
  - stock before/after for product `1587755045505099`: `15` / `15`

Because both historical orders already had the `_dotypos_stock_synced` marker, each fiscal POS deduction was neutralized with a matching warehouse stockup. The offset is idempotently marked with `_rusky_dotypos_backfill_stock_offset=yes`.

## final audit

- eligible orders after 2026-06-30: 2
- fiscalized and remotely verified: 2
- missing: 0
- homepage after operation: HTTP 200
