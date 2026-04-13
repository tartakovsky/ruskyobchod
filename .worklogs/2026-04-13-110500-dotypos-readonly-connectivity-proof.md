## dotypos readonly connectivity proof

### scope

Add a reusable read-only verification path for live Dotypos connectivity and mapping correctness.

File added:

- `tools/verify-dotypos-readonly.sh`

### live proof

The verifier confirmed on live:

- `Dotypos::instance()->dotyposService` is present
- `syncToDotypos=true`
- warehouse id is present
- preorder products have valid `dotypos_id` mapping

Verified products:

- `10781` `Slanina "Mercur"`
- `10617` `Sleď . Vedro`
- `10310` `Ryba Makrela Údeného Chladenia`

For each of them:

- local `_gastronom_cash_stock_kg`
- exactly matched remote `stockQuantityStatus`

Observed values at proof time:

- `10781` -> `0.966`
- `10617` -> `3.64`
- `10310` -> `1.48`

### result

The integration boundary now has both:

- controlled mutation-proof through temporary-order harnesses and in-memory Dotypos stubs
- read-only proof that the real external Dotypos service, warehouse mapping, and live product mapping are all consistent

This materially reduces Tuesday-morning uncertainty without taking an unnecessary real stock mutation step.
