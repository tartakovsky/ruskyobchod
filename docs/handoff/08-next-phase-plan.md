# next phase plan

This file starts after:

- the completed safe bounded-refactor phase for `gastronom-lang-switcher.php`
- the completed SK/RU commerce-tail reduction block

## what not to do

Do not continue random helper extraction inside the same file just because a few lines are still extractable.

That phase is finished.

Do not touch:

- empty-cart shell orchestration
- retained fallback/output cleanup surface
- remaining low-value commerce residuals

without a dedicated mini-plan and exact-output verification path.

## recommended next step

### step 1. classify the remaining residual surface

Create an explicit keep/remove/defer map for the remaining risky logic inside `gastronom-lang-switcher.php`.

Required output:

- `keep`: needed on live, no proof yet for safe removal
- `remove later`: likely removable but only after exact-output proof
- `defer`: too risky or too low-value for now

### step 2. choose one next architecture zone

After the residual map exists, choose one of these, not several:

- server-first translation migration for another zone
- retained fallback proof work
- Dotypos integration boundary work
- separate cleanup in a different owner file

### step 3. keep the same execution contract

- `commit`
- `push`
- deploy one file if live is touched
- verify exact affected user path

## immediate practical recommendation

The highest-signal next block is now:

1. freeze the current commerce-tail result as complete
2. treat Dotypos parity as complete:
   - `gastronom-stock-fix.php` parity done
   - owner-file parity done
   - runtime proof done
3. start Dotypos business-flow proof one path at a time:
   - preorder checkout path
   - await-weight path
   - weight confirmation path
   - Dotypos sync path
   - restore path
4. do not re-enter the already-closed commerce tail unless a real regression appears

## current entry conditions for business-flow proof

Now confirmed:

- live parity is complete for `gastronom-stock-fix.php` and the missing owner MU files
- runtime proof is positive
- there are exactly 3 live preorder products to use as real fixtures
- current `await-weight` order count is `0`
- preorder storefront path now has its own verification contour:
  - `tools/verify-preorder-shell.sh`
- critical preorder owner gaps have been closed on live:
  - `rusky-preorder-notifications.php`
  - `rusky-preorder-admin.php`
  - `rusky-preorder-storefront.php`
  - `rusky-order-page-language.php`
  - `rusky-order-language-helpers.php`
  - `rusky-stock-normalization.php`
  - `rusky-stock-policy.php`
- language-runtime parity also moved forward safely:
  - `rusky-server-language-core.php`
  - `rusky-language-switcher-lite.php`

## next practical move

Do not guess about the remaining preorder/Dotypos lifecycle behavior.

Already proven:

- checkout-prepared preorder path
- reserve/restore site stock path
- confirmation-ready transition for COD
- confirmation-ready transition for non-COD
- sync/restore algorithm via in-memory Dotypos stub
- admin AJAX confirmation path success with controlled cleanup
- customer-visible RU/SK order-page language through:
  - `tools/verify-order-page-language.sh`
- read-only live Dotypos connectivity, warehouse mapping, and product stock parity via:
  - `tools/verify-dotypos-readonly.sh`

The next block should be one of these, not several:

1. classify the remaining live-vs-repo MU gaps by criticality and parity only what is required before Tuesday morning
2. if runtime filtering becomes necessary again, take `rusky-runtime-shim.php` only under an explicit mini-plan with exact before/after active-plugin proof
3. use the cleanup-safe admin proof harness for any further preorder confirmation verification:
   - `tools/prove-admin-weight-confirmation.sh`
4. keep using the read-only Dotypos verifier before any real external-stock mutation step:
   - `tools/verify-dotypos-readonly.sh`

The runtime-shim mini-plan now exists explicitly:

- `.plans/2026-04-13-runtime-shim-mini-plan.md`

If option 2 is chosen, it must have:

- explicit cleanup/rollback
- exact expected state transitions
- verification before and after

Current read-only conclusion:

- `rusky-runtime-shim.php` is the only remaining live-vs-repo MU gap
- `real-time-find-and-replace/real-time-find-and-replace.php` is currently not active on live
- because of that, forcing runtime-shim parity tonight would add risk without clear Tuesday-morning value

That preserves the progress already achieved and avoids re-entering the same file blindly.
