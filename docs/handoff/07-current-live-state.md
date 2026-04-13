# current live state

This is the authoritative current-state document.

## source of truth

- repo: `tartakovsky/ruskyobchod`
- current live docs marker: `72278352`
- current completed safe-refactor runtime baseline:
  - stable refactor point: `00d840a7`
  - current runtime/docs marker after commerce-tail stop-line: `72278352`

## live state now

The live site is currently stable on the tracked `gastronom-lang-switcher.php` path.

Validated repeatedly during the safe phase:

- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
- `tools/verify-account-shell.sh`
- `tools/verify-commerce-shell.sh`
- `tools/verify-commerce-shell-sk.sh`
- `tools/verify-preorder-shell.sh`

Latest status at current stop-line:

- storefront baseline green
- checkout shell baseline green
- account shell baseline green
- commerce shell RU green
- commerce shell SK green
- preorder shell green

## what is complete

### 1. safe bounded refactor phase for `gastronom-lang-switcher.php`

Completed.

Characteristics:

- one file at a time
- no intended behavior change
- deploy only after `commit -> push`
- live verification after every deploy

### 2. server/runtime cleanup improved

Repeated replacement maps, regex patterns, and replacement values were extracted into named helpers across the safe surface of:

- title normalization
- switcher rendering internals
- cookie notice normalization
- footer heading normalization
- checkout order shell normalization

### 3. post-safe-phase residual work has already produced real migrations

Not just proofs.

Completed migrations:

- footer brand/legal slice moved from late footer shell cleanup to earlier server-side ownership through `render_block`
- cookie notice labels moved from late output cleanup to source-level ownership through `cn_cookie_notice_args`
- account/login/cart shell labels moved out of late server-rendered shell ownership and are now held by earlier phrase/title/menu owners

### 4. rollback discipline was proven

One attempted extraction inside the empty-cart shell area caused a regression and was reverted immediately by single-step rollback.

That is now a hard stop-line, not an open invitation for another blind cleanup pass.

### 5. commerce late-shell tail was reduced to stop-line

The late-shell SK commerce tail in `gls_normalize_server_rendered_html()` was reduced further under full live verification.

Dead residual pairs were removed one at a time while keeping all five verification contours green.

This block is considered complete at its current stop-line.

### 6. Dotypos live boundary was identified first

Read-only live audit confirmed:

- live `gastronom-lang-switcher.php` matches repo
- live `gastronom-stock-fix.php` does not match repo
- live does not currently contain `rusky-dotypos-stock-bridge.php`
- live does not currently contain `rusky-weight-preorder.php`

That was the required entry condition for the parity block.

### 7. stock-fix parity rollout completed safely

`gastronom-stock-fix.php` parity rollout was completed as a one-file controlled deploy.

Post-deploy verification stayed green for:

- storefront baseline
- checkout shell baseline
- account shell baseline
- commerce shell RU
- commerce shell SK

### 8. owner-file parity rollout completed safely

The missing live MU owner files were then deployed intentionally:

- `rusky-dotypos-stock-bridge.php`
- `rusky-weight-preorder.php`

All five verification contours stayed green after the rollout.

### 9. Dotypos runtime proof is now positive

Read-only runtime proof on live now confirms:

- owner-file functions are loaded
- compatibility wrapper functions are available
- critical preorder/Dotypos hooks are registered

Confirmed hooks/functions include:

- `gastronom_prepare_checkout_processed_preorder`
- `gastronom_sync_confirmed_preorder_items_to_dotypos`
- `gastronom_restore_confirmed_preorder_items_to_dotypos`
- `gastronom_handle_weight_confirmation_ajax`
- `gastronom_inject_await_weight_status`

This means the live file boundary and the runtime hook boundary are now aligned closely enough to begin business-flow proof.

### 10. preorder discovery is now grounded in real live products

Read-only discovery confirmed:

- exactly 3 published live preorder products currently exist
- all 3 render the expected preorder note and `/ kg` unit on RU/SK public product pages
- `wc-await-weight` is registered on live
- current `await-weight` order count is `0`

This means the next business-flow proof block must use either:

- those exact three products
- or a controlled temporary order path

It should not rely on abstract code reasoning alone.

### 11. critical preorder owner parity gaps were closed

The live repo-vs-server audit exposed two critical missing owner files:

- `rusky-preorder-notifications.php`
- `rusky-preorder-admin.php`

Both were then deployed intentionally and verified.

Runtime markers now confirmed on live:

- `rpn_mark_weight_confirmed_order_ready`
- `rpn_send_preorder_created_emails`
- `rpa_handle_weight_confirmation_ajax`
- `rpa_render_weight_confirmation_box`

The preorder storefront owner gap was also closed:

- `rusky-preorder-storefront.php`

Runtime markers now confirmed on live:

- `rpsf_add_to_cart_validation`
- `rpsf_available_payment_gateways`
- `rpsf_render_single_product_note`
- `rpsf_checkout_create_order_line_item`

The order-page language owner gap was also closed:

- `rusky-order-page-language.php`

Runtime markers now confirmed on live:

- `ropl_localize_order_item_name`
- `ropl_render_forced_order_lang_marker`
- `ropl_maybe_redirect_context_order_lang`
- `ropl_start_order_page_buffer`
- `ropl_render_order_item_actual_weight`

### 12. preorder lifecycle proof is now partially positive

Controlled temporary-order proof on live already confirmed:

- checkout-prepared preorder path
- reserve/restore site stock path
- `await-weight -> on-hold` confirmation-ready transition for COD
- `await-weight -> pending` confirmation-ready transition for non-COD
- Dotypos sync/restore algorithm through in-memory service stub without real API mutation
- admin AJAX weight-confirmation path returned live success under controlled conditions

All temporary proofs were followed by cleanup and then by all six green verification contours.

### 13. remaining live-vs-repo MU gaps are no longer all equally urgent

The full `mu-plugins` audit showed that live still does not contain every repo `rusky-*.php` file.

Current status:

- the critical preorder owner gaps have been closed
- the remaining missing MU files are now deferred until they are proven necessary for:
  - Tuesday-morning integration readiness
  - or a specific live/runtime defect

They should not be deployed as a blind parity batch.

Current remaining missing files on live:

- `rusky-language-switcher-lite.php`
- `rusky-order-language-helpers.php`
- `rusky-runtime-shim.php`
- `rusky-server-language-core.php`
- `rusky-stock-normalization.php`
- `rusky-stock-policy.php`

## stop line

Do not continue the completed safe-refactor phase into these areas without a new mini-plan:

- empty-cart shell orchestration
- retained fallback/output cleanup surface
- any remaining commerce residual that is not already covered by exact-output proof and the five verification contours

## current rule for live work

- repo first
- smallest possible surface
- one changed file means one deployed file
- if live changes, verify the exact affected user path immediately
- if a regression appears, revert that one step before doing anything else

## next block

The next rational block is no longer more commerce-tail cleanup.

It should be one of:

1. explicit mini-plan for the remaining retained residual surface inside `gastronom-lang-switcher.php`
2. next server-first translation zone outside the already-closed commerce tail
3. remaining Dotypos/preorder proof and targeted parity cleanup
