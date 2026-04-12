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

## next practical move

Do not guess about preorder lifecycle behavior.

The next block should decide explicitly whether to:

1. prove the next path read-only from existing live state
2. or create one controlled temporary preorder order for end-to-end proof

If the second option is chosen, it must have:

- explicit cleanup/rollback
- exact expected state transitions
- verification before and after

That preserves the progress already achieved and avoids re-entering the same file blindly.
