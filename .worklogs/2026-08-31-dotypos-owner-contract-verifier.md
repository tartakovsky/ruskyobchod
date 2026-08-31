# Dotypos owner contract verifier

## Scope

Added a read-only verifier for the current Dotypos ownership contract.

The verifier loads WordPress through a temporary server-side PHP file, inspects
reflection owners and the registered hook registry, then removes the temporary
file. It creates no orders and makes no Dotypos API calls.

## Contract captured

- `gastronom_apply_dotypos_stock_to_wc_product` and
  `gastronom_resolve_dotypos_order_sync_quantity` are owned by
  `rusky-dotypos-stock-bridge.php`.
- `rdf_fiscalize_order` is owned by `rusky-dotypos-fiscalization.php`.
- `woocommerce_reduce_order_stock` order remains:
  - fiscalization at priority `5`;
  - single fallback at priority `10`;
  - COD quarter deferral at priority `20`.
- The direct bridge sale callback is absent from priority `10`; restore remains
  owned by the bridge at priority `10`.

## Verification

- shell syntax passed;
- PHP 8.2 lint passed on the server;
- live owner and hook assertions passed;
- existing Dotypos read-only, fiscalization, and Action Scheduler baselines
  were green before the verifier was added.
