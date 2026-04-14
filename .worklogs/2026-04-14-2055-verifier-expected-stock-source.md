## 2026-04-14 20:55

- updated `tools/verify-dotypos-product-state.sh`
- verifier now selects expected stock source by product model:
  - regular products use Woo `_stock`
  - weight preorder products use `_gastronom_cash_stock_kg`
- verified on live:
  - product `10640` (`Krúpy Ryža 1000 gr.`) resolves as regular stock and matches remote `12`
  - product `10617` (`Sleď . Vedro`) resolves as weight preorder and matches remote `1.6`
- no production code changed; this was a local tooling fix only
