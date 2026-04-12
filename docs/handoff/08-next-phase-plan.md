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
2. execute Dotypos parity block in the safe order:
   - `gastronom-stock-fix.php` parity first
   - owner-file parity second
   - integration proof third
3. do not re-enter the already-closed commerce tail unless a real regression appears

That preserves the progress already achieved and avoids re-entering the same file blindly.
