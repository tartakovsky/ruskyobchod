# 2026-04-12 10:20:00 CET

## scope

Residual risky surface inside:

- `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`

This document starts after the completed safe bounded-refactor phase.

## keep

These areas stay in place for now because they still participate in live output shaping and do not yet have exact-output proof for safe removal.

### 1. `gls_normalize_server_rendered_html()`

Keep.

Reason:

- still owns a large server-rendered translation cleanup surface for cart, checkout, account, notices, labels, and other mixed UI strings
- touches exact live output in business-critical flows

### 2. `gls_normalize_storefront_chrome_html()`

Keep.

Reason:

- still acts as retained storefront fallback/output cleanup surface
- removal or reshaping requires exact-output proof, not structural confidence

### 3. empty-cart shell orchestration inside `gls_normalize_server_rendered_html()`

Keep.

Reason:

- one extraction attempt already caused a live regression
- this is a proven risky zone

### 4. cookie notice button normalization inside the server-rendered shell path

Keep.

Reason:

- exact-output proof attempt showed that removing the residual call changed RU cart output
- observed regression:
  - button text stayed `Ок`
  - `cn-accept-cookie` aria-label regressed from `Ок` to `Ok`
- this means the slice still owns part of the live output contract

### 5. legal/footer normalization inside `gls_normalize_storefront_footer_shell_html()`

Keep.

Reason:

- exact-output proof attempt on RU homepage showed immediate regression to Slovak footer/legal text
- baseline failures after removal:
  - `home RU legal country present`
  - `home RU legal registry line present`
  - `home RU legal company line present`

## remove later

These areas are plausible cleanup candidates, but only after explicit proof.

### 1. duplicate phrase maps that are now partly covered by source/server helpers

Remove later.

Reason:

- some phrases may now be redundant after the safe phase and earlier server-first migration work
- but redundancy must be proven against exact live output, route by route

### 2. narrow checkout/order shell rewrites beyond the already extracted helper boundary

Remove later.

Reason:

- structure is now cleaner
- future cleanup is possible, but only under a dedicated mini-plan with exact-output checks

## defer

These areas are not worth touching in the immediate next step.

### 1. low-value cosmetic micro-refactors in already stable helper blocks

Defer.

Reason:

- low signal
- no meaningful architecture gain

### 2. any broad rewrite of retained output cleanup into a new abstraction in one pass

Defer.

Reason:

- too much blast radius
- violates current stop-line discipline

## recommended next execution step

Do not continue refactoring inside the same completed safe phase.

Instead:

1. pick one retained zone
2. define exact-output proof for it
3. prove whether a small subset is truly removable
4. only then touch live behavior
