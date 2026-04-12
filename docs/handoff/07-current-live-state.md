# current live state

This is the authoritative current-state document.

## source of truth

- repo: `tartakovsky/ruskyobchod`
- current documented handoff point commit: `6c28431e`
- current completed safe-refactor runtime baseline:
  - stable refactor point: `00d840a7`
  - current docs marker after phase close: `6c28431e`

## live state now

The live site is currently stable on the tracked `gastronom-lang-switcher.php` path.

Validated repeatedly during the safe phase:

- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
- `tools/verify-account-shell.sh`

Latest status at phase close:

- storefront baseline green
- checkout shell baseline green

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

## stop line

Do not continue the completed safe-refactor phase into these areas without a new mini-plan:

- empty-cart shell orchestration
- retained fallback/output cleanup surface
- any change that requires exact-output proof instead of structural equivalence

## current rule for live work

- repo first
- smallest possible surface
- one changed file means one deployed file
- if live changes, verify the exact affected user path immediately
- if a regression appears, revert that one step before doing anything else

## next block

The next rational block is not more helper extraction in the same safe phase.

It should be one of:

1. explicit plan for risky residuals inside `gastronom-lang-switcher.php`
2. next server-first translation zone outside the completed safe phase
3. retained-fallback review with keep/remove/defer decisions
