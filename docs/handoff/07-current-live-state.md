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

Latest status at current stop-line:

- storefront baseline green
- checkout shell baseline green
- account shell baseline green
- commerce shell RU green
- commerce shell SK green

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
3. Dotypos integration boundary work
