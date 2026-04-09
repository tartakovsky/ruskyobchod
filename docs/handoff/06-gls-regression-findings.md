# gls regression findings

This is the plain-language version.

## short conclusion

The current `gls-script.js` is probably breaking input focus because it keeps reprocessing the whole page after load.

It does not just translate text once.

It:

- watches the whole page for DOM changes
- reruns translation several more times after load
- reruns again after WooCommerce AJAX updates
- rewrites some elements through `innerHTML`

That combination can recreate DOM nodes while the user is typing. When that happens, focus gets kicked out of inputs.

## what is most suspicious

### 1. whole-page MutationObserver

Current file:

- [gls-script.js](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/plugins/gastronom-lang-switcher/gls-script.js#L324)

What it does:

- watches `document.body`
- reacts to subtree changes
- calls `translateAll()` again

That means the script reacts not only to WooCommerce changes, but also to changes caused by the script itself.

### 2. repeated retry passes

Current file:

- [gls-script.js](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/plugins/gastronom-lang-switcher/gls-script.js#L347)
- [gls-script.js](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/plugins/gastronom-lang-switcher/gls-script.js#L1316)
- [gls-script.js](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/plugins/gastronom-lang-switcher/gls-script.js#L1324)

What it does:

- extra `setTimeout(translateAll, ...)` passes after load
- extra `translateAll()` passes after WooCommerce AJAX events
- extra `setInterval()` passes during startup

This is not a small translation helper anymore. It is a retry engine.

### 3. `innerHTML` rewrites

Current file:

- [gls-script.js](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/plugins/gastronom-lang-switcher/gls-script.js#L893)
- [gls-script.js](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/plugins/gastronom-lang-switcher/gls-script.js#L1097)
- [gls-script.js](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/plugins/gastronom-lang-switcher/gls-script.js#L1118)

What it does:

- rewrites `innerHTML` on broad sets of elements
- rewrites checkout shipping labels
- rewrites payment labels

This is the riskiest behavior for focus loss because `innerHTML` can recreate descendants.

## what history says

### last clearly documented stable point

- [plugin-v44-verified.md](/Users/tartakovsky/Projects/kb/ruskyobchod/worklogs-plans-archive-2026-04-09/.worklogs/2026-03-15-122959-plugin-v44-verified.md#L9)

Meaning:

- by `v4.4`, they already had MutationObserver trouble
- they added debounce and a guard
- that version was explicitly called stable

### first major risk increase

- [checkout-translations-v54.md](/Users/tartakovsky/Projects/kb/ruskyobchod/worklogs-plans-archive-2026-04-09/.worklogs/2026-03-23-111542-checkout-translations-v54.md#L15)

Meaning:

- `v5.4` added 5 repeated `translateAll()` passes through `setInterval`
- it also added WooCommerce `updated_checkout` reruns

This is a major point where the script became much more aggressive.

### second major risk increase

- [cart-checkout-ux.md](/Users/tartakovsky/Projects/kb/ruskyobchod/worklogs-plans-archive-2026-04-09/.worklogs/2026-03-23-220914-cart-checkout-ux.md#L18)

Meaning:

- `v5.8` added checkout shipping hiding
- it also added more timeout-driven checkout DOM handling

That increases the chance of form instability on checkout.

### hard mismatch in current file

- [fix-bank-transfer-translation.md](/Users/tartakovsky/Projects/kb/ruskyobchod/worklogs-plans-archive-2026-04-09/.worklogs/2026-03-30-095230-fix-bank-transfer-translation.md#L14)

That worklog says:

- `SHIP_LABELS` was removed in `v6.4`
- shipping label rewriting was deliberately dropped

But the current imported script still contains the `SHIP_LABELS` block:

- [gls-script.js](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/plugins/gastronom-lang-switcher/gls-script.js#L1087)

That means one of these is true:

- the script was rolled back later
- risky code was manually reintroduced later
- the worklog trail after `v6.4` is incomplete

In all three cases, the current script is not trustworthy as a clean versioned source.

## what to tell the next agent

Do not start by rewriting the whole translation system.

Do not start by leaving `gls-script.js` disabled.

The first job is to isolate the focus-breaking regression inside the existing script.

## exact exploration order

1. Capture the current live versions of:
   - `gastronom-lang-switcher.php`
   - `gls-script.js`
   - `gls-style.css`

2. Confirm whether live PHP actually enqueues `gls-script.js`.
   - The imported PHP in this repo currently does not enqueue it.
   - If live PHP differs, record that difference first.

3. Compare the current `gls-script.js` against the documented stable behavior from `v4.4`.
   - focus on:
     - MutationObserver behavior
     - retry timers
     - AJAX reruns
     - `innerHTML` rewrites

4. First disable only the highest-risk lines in a controlled copy:
   - the broad `innerHTML` rewrite for `a, button, span, div`
   - shipping label `innerHTML` rewrite
   - payment label `innerHTML` rewrite if still needed for diagnosis

5. Test after each tiny change:
   - homepage search input
   - login/account form input
   - cart quantity field
   - checkout fields
   - RU/SK switching

6. If focus still breaks, test the next layer:
   - disable startup retry passes
   - disable WooCommerce AJAX reruns
   - disable or narrow the MutationObserver scope

7. Keep notes on exactly which removal stops the focus bug.

## likely first candidates to cut

Start here:

- [gls-script.js](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/plugins/gastronom-lang-switcher/gls-script.js#L893)
- [gls-script.js](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/plugins/gastronom-lang-switcher/gls-script.js#L1097)
- [gls-script.js](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/plugins/gastronom-lang-switcher/gls-script.js#L1118)

If that is not enough, test these next:

- [gls-script.js](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/plugins/gastronom-lang-switcher/gls-script.js#L347)
- [gls-script.js](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/plugins/gastronom-lang-switcher/gls-script.js#L1316)
- [gls-script.js](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/plugins/gastronom-lang-switcher/gls-script.js#L1324)
- [gls-script.js](/Users/tartakovsky/Projects/kb/ruskyobchod/wordpress/wp-content/plugins/gastronom-lang-switcher/gls-script.js#L324)

## the simplest current working theory

The bug is not "translation" in general.

The bug is:

- repeated page-wide DOM rewriting
- plus retries
- plus observer-triggered reruns
- plus at least one undocumented rollback or manual reintroduction of risky code

That is the regression surface the next agent should explore first.
