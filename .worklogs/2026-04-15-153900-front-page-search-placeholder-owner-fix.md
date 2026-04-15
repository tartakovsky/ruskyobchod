# Front Page Search Placeholder Owner Fix

## Goal
- Localize the Woo product search placeholder on the front page without using a global gettext path and without widening storefront runtime risk.

## Constraints
- Follow handoff workflow: repo-first, minimal file deploy, immediate live verification.
- Do not touch Dotypos, checkout logic, stock sync, or legacy dirty files.
- Keep the fix server-side and narrowly scoped to the Woo product search form owner path.

## Plan
1. Add a narrow `get_product_search_form` filter in `rusky-theme-chrome-language.php`.
2. Replace only the search placeholder for the current storefront language.
3. Extend baseline verification to assert the front-page search placeholder in RU and SK.
4. Deploy only changed files, verify live, then commit and push.
