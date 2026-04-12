# next phase plan

This file starts after the completed safe bounded-refactor phase for `gastronom-lang-switcher.php`.

## what not to do

Do not continue random helper extraction inside the same file just because a few lines are still extractable.

That phase is finished.

Do not touch:

- empty-cart shell orchestration
- retained fallback/output cleanup surface

without a dedicated mini-plan and exact-output verification path.

## recommended next step

### step 1. classify the residual surface

Create an explicit keep/remove/defer map for the remaining risky logic inside `gastronom-lang-switcher.php`.

Required output:

- `keep`: needed on live, no proof yet for safe removal
- `remove later`: likely removable but only after exact-output proof
- `defer`: too risky or too low-value for now

### step 2. choose one next architecture zone

After the residual map exists, choose one of these, not several:

- server-first translation migration for another zone
- retained fallback proof work
- separate cleanup in a different owner file

### step 3. keep the same execution contract

- `commit`
- `push`
- deploy one file if live is touched
- verify exact affected user path

## immediate practical recommendation

The highest-signal next block is:

1. document the retained fallback surface in `gastronom-lang-switcher.php`
2. classify it as `keep/remove later/defer`
3. stop there before touching live behavior again

That gives a clean handoff point and avoids re-entering the same file blindly.
