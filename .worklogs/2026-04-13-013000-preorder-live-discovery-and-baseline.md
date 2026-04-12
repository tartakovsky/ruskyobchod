## Preorder Live Discovery And Baseline

Date: 2026-04-13

### Scope

Read-only discovery for the first business-flow proof block after Dotypos parity.

### Findings

Live currently contains exactly three published weight-preorder products:

- `10781` `Slanina „Mercur“`
- `10617` `Sleď . Vedro`
- `10310` `Ryba Makrela Údeného Chladenia`

Observed live metadata:

- all three products have `_gastronom_weight_preorder=yes`
- all three have non-zero min/max kg ranges
- all three currently expose a preorder note and `/ kg` unit on public product pages

Current live order-state fact:

- `wc-await-weight` is registered on live
- current `await-weight` order count is `0`

### Verification artifact added

Added:

- `tools/verify-preorder-shell.sh`

This verifies, for all three live preorder products:

- RU product page `200`
- SK product page `200`
- RU preorder note title present
- RU preorder note body present
- SK preorder note title present
- SK preorder note body present
- `/ kg` unit present in both languages

### Result

There is now a dedicated read-only live verification contour for the preorder storefront path.

This reduces the risk of entering preorder business-flow proof blind.
