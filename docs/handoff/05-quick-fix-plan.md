# quick fix plan

This is the smallest path back to a usable site.

Do not confuse this with the proper rebuild. This plan is for getting the site back to a state where people can use it without fighting the page.

## objective

Keep the stability gain from disabling `gls-script.js`, then bring back only the minimum language behavior needed for the store to function.

## rule for the quick-fix phase

No new logic goes into legacy `gls-script.js` unless there is no other escape route.

That file already proved what it does under stress.

## quick fix 1, keep `gls-script.js` disabled

Do not re-enable it wholesale.

That is the known trigger for the site-wide input focus bug.

## quick fix 2, restore the language switcher in the smallest possible way

Implement a simple server-led switcher:

- switcher buttons set `?lang=ru` or `?lang=sk`
- PHP stores a cookie
- PHP renders the switcher active state
- page reload is allowed

Accept the reload. A one-second reload is cheaper than a broken form.

## quick fix 3, restore only essential translation output on the server

Bring back only what users need first:

- menu labels
- account labels
- key storefront text
- checkout heading and obvious payment labels if safe

Leave cosmetic translation gaps alone for now. The goal is functional clarity, not perfect bilingual elegance.

## quick fix 4, do not hide shipping methods with browser hacks

The old checkout UX trick that hides unselected shipping methods should stay off unless there is a narrow, safe rewrite.

If checkout needs to be ugly for a week but clickable, choose ugly.

## quick fix 5, keep logged-in path under suspicion

The `wordpress_logged_in_*` cookie correlated with `503`.

Immediate practical checks:

- test homepage while logged out
- test homepage while logged in
- test with admin bar off if needed
- inspect whether logged-in-only hooks or assets are still loading extra weight

Do not assume the 503 is solved because the public site looks calmer.

## quick fix 6, stop touching Dotypos unless the bug is clearly in Dotypos

The current frontend emergency is not a reason to keep rewriting the stock integration.

For the quick-fix phase:

- freeze Dotypos code
- observe it
- patch only if a business-critical stock bug is reproduced

## exact short-term task list

1. Implement a tiny PHP-based language toggle in `gastronom-lang-switcher.php`.
2. Keep `gls-script.js` off.
3. Add only the smallest PHP filters needed for visible language labels.
4. Test homepage search, account forms, cart quantity, checkout fields.
5. Test RU and SK switching with full page reload.
6. Test logged-in homepage for the cookie-linked `503`.

## success criteria

- users can type into forms
- homepage does not randomly eat focus
- language switch works again, even if it reloads the page
- checkout remains usable
- no new 503 spike is introduced by the fix itself

## failure modes to avoid

- bringing back the whole legacy JS file “just to check”
- mixing translation restoration with Dotypos rewrites
- adding more timeouts or MutationObserver passes
- hiding broken translation by adding more client-side DOM magic

The site does not need one more miracle patch. It needs fewer moving parts.
