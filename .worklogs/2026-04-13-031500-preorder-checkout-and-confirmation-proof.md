## Preorder Checkout And Confirmation Proof

Date: 2026-04-13

### Scope

Controlled temporary-order proof on live for the preorder lifecycle, with cleanup in the same script.

No real customer order was kept.
No real email was sent.
No real Dotypos API call was made during the sync proof.

### Proven paths

#### 1. checkout-prepared preorder path

Using a temporary preorder order with a real preorder product:

- `gastronom_order_requires_weight_confirmation()` returned `true`
- `gastronom_prepare_checkout_processed_preorder()` moved the order to `await-weight`
- `_gastronom_requires_weight_confirmation` became `yes`
- `_gastronom_preorder_site_reserved` became `yes`
- stale item meta was reset:
  - `_gastronom_weight_confirmed=no`
  - `_gastronom_weight_cash_synced=no`
  - `_gastronom_actual_weight_kg` cleared
- site stock was reduced by one piece
- cleanup restored stock and deleted the order

#### 2. reserve/restore site stock helpers

Using a temporary preorder order:

- `gastronom_reserve_preorder_site_stock()` reduced `_stock`
- `gastronom_restore_preorder_site_stock()` restored `_stock`
- cleanup returned product stock to the original value and deleted the order

#### 3. confirmation-ready transition after owner parity

Using a temporary preorder order already in `await-weight` with confirmed item meta:

- `gastronom_mark_weight_confirmed_order_ready()` now transitions `await-weight -> on-hold` for COD
- `gastronom_mark_weight_confirmed_order_ready()` now transitions `await-weight -> pending` for non-COD (`bacs`)
- `_gastronom_requires_weight_confirmation` becomes `no`
- email path was intercepted through `pre_wp_mail`, confirming a send attempt without real outbound mail
- cleanup deleted the order

#### 4. Dotypos sync and restore algorithm proof through in-memory stub

Using a temporary preorder order and an in-memory replacement of `Dotypos::instance()->dotyposService`:

- `gastronom_sync_confirmed_preorder_items_to_dotypos()` performed exactly one stock update call
- delta was negative actual weight: `-0.44`
- invoice number used `WC-PREORDER-<order>-CONFIRM`
- `_gastronom_weight_cash_synced` became `yes`
- product `_gastronom_cash_stock_kg` and `_stock` updated from the stubbed warehouse response

Then:

- `gastronom_restore_confirmed_preorder_items_to_dotypos()` performed exactly one restore call
- delta was positive actual weight: `0.44`
- invoice number used `WC-PREORDER-<order>-RESTORE`
- `_gastronom_weight_cash_restored` became `yes`
- product `_gastronom_cash_stock_kg` and `_stock` returned from the stubbed warehouse response

Cleanup restored original product stock/meta and deleted the temporary order.

### Post-proof verification

After all temporary-order proofs, all six verification contours were green again.
