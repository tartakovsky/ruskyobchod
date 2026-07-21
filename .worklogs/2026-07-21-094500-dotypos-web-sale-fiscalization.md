# Dotypos web-sale fiscalization

## problem

WooCommerce web orders reduced Dotypos stock, but did not create a paid POS order or an eKasa fiscal receipt. The accounting result was stock movement without matching fiscal revenue, while Stripe or GLS later transferred the money.

## confirmed cause

- the repository vendor integration only posts warehouse `stockups`
- the newer live `rusky-dotypos-stock-bridge.php` posts `/warehouses/:warehouseId/sales`
- neither path calls the Dotypos POS device, so neither can fiscalize in Slovakia
- the newer live stock bridge is not yet in Git (live SHA-256 `a642f63025edeb4f5bc002ddd42c6b7365a88c551f41a9c4b148804ede8aded7`); it was deliberately not overwritten

## implementation

Added `wordpress/wp-content/mu-plugins/rusky-dotypos-fiscalization.php` as a project-owned companion plugin.

- supports successful Stripe payments and GLS cash-on-delivery orders
- uses the Slovakia-supported atomic Dotypos POS Action `order/create-issue-pay`
- maps Stripe to Dotypos `Online` (`900000019`)
- maps GLS/COD remittance to Dotypos `Bank transfer` (`900000009`)
- uses `WC-<order-id>` as the external ID and checks it before creation
- emails the fiscal receipt to the WooCommerce billing email when available
- records fiscal state and remote Dotypos order ID on the WooCommerce order
- leaves an order note and WooCommerce log on failure
- installs a single stock owner: successful POS Action owns stock deduction; a failed action falls back to the existing live `/sales` bridge (or the older vendor stock callback)
- does not automatically retry after stock fallback, avoiding a later double deduction
- excludes unconfirmed weighted preorders

Shipping, COD fees, discounts and rounding are included in the collected total by distributing their net effect over the mapped POS product lines. The response total is compared with WooCommerce and a visible error is stored if it differs by more than one cent.

## live proof

- production PHP lint: clean
- file parity after deploy: `768aee7696c87d2766bddda195a334c1148ab3a57e6ce1bf4245d28e2240a367`
- Dotypos branch resolved uniquely: `120313449`
- non-mutating `order/hello` POS Action: HTTP `200`, result code `0`
- POS device online: `neostra rockchip Swan 1`, app `2.17.48`
- runtime hooks:
  - fiscalization priority `5`
  - single stock fallback priority `10`
  - legacy live bridge callback removed from the order-stock hook
- anonymous homepage: HTTP `200`
- `wp-login.php`: HTTP `200`

## historical backlog (read-only, no customer data)

As of deployment, for orders created since 2026-01-01 and currently processing/completed/on-hold:

- 6 unfiscalized GLS/COD orders
- total `EUR 165.26`
- all 6 already carry the Dotypos stock-sync marker
- no historical fiscal receipts were generated automatically, to avoid duplicate turnover or incorrect backdated fiscal records

## deployment

Only the new MU-plugin file was deployed. No vendor plugin and no existing live stock bridge file was overwritten.
