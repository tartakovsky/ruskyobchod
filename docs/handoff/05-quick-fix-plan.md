# quick fix plan

This is the smallest path back to a usable site.

Do not confuse this with the proper rebuild. This plan is for getting the site back to a state where people can use it without fighting the page.

## objective

Preserve the existing translated site behavior, isolate the regression that broke input focus, and ship the smallest fix that restores both typing and translation.

## rule for the quick-fix phase

No new logic goes into legacy `gls-script.js` unless there is no other escape route.

That file already proved what it does under stress.

## quick fix 1, treat the full `gls-script.js` disable as a diagnostic only

Do not leave the site in the "inputs work, translation broken" state.

That experiment proved the bug lives in the legacy frontend translation path. It did not prove the correct fix is to replace the whole path during the incident.

## quick fix 2, use the last known working translated behavior as the target

Inputs reportedly worked yesterday.

That changes the quick-fix strategy:

- assume the site had a more recent working state with both translation and form usability
- identify the regression inside the existing `gastronom-lang-switcher` stack
- patch or roll back only the focus-breaking logic

The quick fix is a regression fix, not a language-system rewrite.

## quick fix 3, narrow the live culprit inside `gls-script.js`

The likely offenders are the parts that re-touch the page after load:

- MutationObserver passes
- repeated delayed reruns
- interval-based checkout refresh handling
- broad selectors that rewrite labels or containers containing inputs

The right emergency move is:

- restore `gls-script.js`
- disable or remove only the code paths that reprocess form-bearing DOM
- keep the translation dictionary and switcher behavior intact

## quick fix 4, patch the smallest safe surface

Preferred order:

1. stop rewriting nodes that contain inputs or active form controls
2. stop repeated full-page translation reruns
3. scope checkout refresh logic to the smallest container possible
4. if needed, roll back only the recent `gls-script.js` regression range while preserving the rest of translation behavior

Do not jump straight to a PHP/server-side reimplementation during the emergency unless the narrower fix fails.

## quick fix 5, do not hide shipping methods with browser hacks

The old checkout UX trick that hides unselected shipping methods should stay off unless there is a narrow, safe rewrite.

If checkout needs to be ugly for a week but clickable, choose ugly.

## quick fix 6, keep logged-in path under suspicion

The `wordpress_logged_in_*` cookie correlated with `503`.

Immediate practical checks:

- test homepage while logged out
- test homepage while logged in
- test with admin bar off if needed
- inspect whether logged-in-only hooks or assets are still loading extra weight

Do not assume the 503 is solved because the public site looks calmer.

## quick fix 7, stop touching Dotypos unless the bug is clearly in Dotypos

The current frontend emergency is not a reason to keep rewriting the stock integration.

For the quick-fix phase:

- freeze Dotypos code
- observe it
- patch only if a business-critical stock bug is reproduced

## exact short-term task list

1. Capture the current live `gastronom-lang-switcher.php`, `gls-script.js`, and `gls-style.css` as the broken baseline.
2. Identify the last known working translated state from the previous day if it can be recovered.
3. Re-enable `gls-script.js` in a controlled branch or local copy.
4. Remove or guard only the focus-breaking DOM rewrite paths.
5. Test homepage search, account forms, cart quantity, checkout fields.
6. Test RU and SK switching without losing visible translation.
7. Test logged-in homepage for the cookie-linked `503`.

## success criteria

- users can type into forms
- homepage does not randomly eat focus
- language switch still works through the existing site behavior
- visible site translation is preserved
- checkout remains usable
- no new 503 spike is introduced by the fix itself

## failure modes to avoid

- replacing the translation system during the emergency without proving it is necessary
- mixing translation restoration with Dotypos rewrites
- adding more timeouts or MutationObserver passes
- shipping a "stable" site with broken translation and calling it fixed

The site does not need one more miracle patch. It needs fewer moving parts.
