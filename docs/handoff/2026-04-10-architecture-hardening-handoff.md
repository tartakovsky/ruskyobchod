# Architecture Hardening Handoff

## Restored context

Recovered from these saved artifacts:

- `/Users/alexandertartakovsky/Downloads/rusky-suspicious-products-for-review-2026-04-10.xlsx`
- `/Users/alexandertartakovsky/Projects/gastronom-migration/.plans/2026-04-10-dotypos-readonly-audit.json`
- `/Users/alexandertartakovsky/Projects/gastronom-migration/.plans/2026-04-10-pre-dotypos-resync-checklist.md`
- `/Users/alexandertartakovsky/Projects/gastronom-migration/.plans/2026-04-10-custom-runtime-inventory.md`
- `/Users/alexandertartakovsky/Projects/gastronom-migration/.plans/2026-04-10-custom-runtime-inventory.json`
- `/Users/alexandertartakovsky/Projects/gastronom-migration/.plans/2026-04-10-architecture-hardening-plan.md`
- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-10-091200-product-slug-normalization-audit.md`

## What was confirmed

- The current hardening direction is correct.
- The immediate architectural duplication was the runtime override split across:
  - `mu-plugins/rusky-disable-far.php`
  - `mu-plugins/rusky-runtime-shim.php`
- The main next extraction target is still:
  - `plugins/gastronom-stock-fix/gastronom-stock-fix.php`

## Local hardening changes applied

- `mu-plugins/rusky-runtime-shim.php`
  was updated to become the single owner of runtime plugin overrides.
- `real-time-find-and-replace`
  is now treated as globally blocked by the runtime shim.
- `gastronom-lang-switcher`
  remains conditionally blocked only for sensitive runtime or logged-in non-admin flows.
- `mu-plugins/rusky-disable-far.php`
  was removed locally as a duplicate owner of `option_active_plugins`.
- `inventory_custom_runtime.py`
  was updated so future inventory runs no longer include the deleted duplicate file.
- Added runtime ownership map:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/.plans/2026-04-10-runtime-ownership-map.md`
- Added Dotypos write-surface inventory for `gastronom-stock-fix`:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/.plans/2026-04-10-gastronom-stock-fix-dotypos-write-surface.md`
- Added preorder state-machine inventory:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/.plans/2026-04-10-gastronom-preorder-state-machine.md`
- Added preorder language helper surface:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/.plans/2026-04-10-preorder-language-helper-surface.md`
- Added concrete target split for `gastronom-stock-fix`:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/.plans/2026-04-10-gastronom-stock-fix-target-split.md`
- Added extraction-ready helper scaffold:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/mu-plugins/rusky-order-language-helpers.php`
- Added extraction-ready preorder core scaffold:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/mu-plugins/rusky-weight-preorder.php`
- Added extraction-ready Dotypos bridge scaffold:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/mu-plugins/rusky-dotypos-stock-bridge.php`
- Added extraction-ready stock normalization scaffold:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/mu-plugins/rusky-stock-normalization.php`
- Added extraction-ready stock policy scaffold:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/mu-plugins/rusky-stock-policy.php`
- Added current shell-state note for the residual monolith:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/.plans/2026-04-11-gastronom-stock-fix-shell-state.md`
- Added wrapper-retention classification for the residual shell:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/.plans/2026-04-11-gastronom-stock-fix-wrapper-retention.md`
- Added explicit owner map for the residual shell:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/.plans/2026-04-11-gastronom-stock-fix-owner-map.md`
- Added wrapper-overlap audit for owner files:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-11-103744-wrapper-overlap-audit.md`
- Added end-state checklist for this hardening phase:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/.plans/2026-04-11-integration-readiness-checklist.md`
- Added extraction-ready frontend order-language scaffold:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/mu-plugins/rusky-order-page-language.php`
- Added extraction-ready preorder notification scaffold:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/mu-plugins/rusky-preorder-notifications.php`
- Added extraction-ready preorder admin scaffold:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/mu-plugins/rusky-preorder-admin.php`
- Added language-plugin boundary split map:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/.plans/2026-04-10-gastronom-lang-switcher-boundary-split.md`
- Added language-plugin failure-path candidate note:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-10-193000-gls-failure-path-candidate.md`
- Added storefront messaging dedupe note:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-10-194500-gls-storefront-messaging-dedupe.md`
- Added commerce dedupe note:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-10-195500-gls-commerce-dedupe.md`
- Added Dotypos maintenance quarantine note:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-10-201000-gls-dotypos-maintenance-quarantine.md`
- Added local clean baseline note:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-10-202500-gls-local-clean-baseline.md`
- Added controlled re-enable plan:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/.plans/2026-04-10-gls-controlled-reenable-plan.md`
- Added controlled re-enable stage log:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-10-210800-gls-reenable-attempt-stage.md`

## Current blockers / caveats

- Local `php` is not available in `PATH`, so PHP lint was not run in this session.
- Chrome DevTools MCP access partially recovered.
  It now sees at least one real live tab:
  - `https://ruskyobchod.sk/?lang=ru`
- Full attachment to the user's pre-existing Chrome tab set is still not confirmed.
- A regression was found on the front page for logged-in frontend sessions:
  runtime shim was trimming `gastronom-lang-switcher` too broadly, which exposed raw bilingual content blocks.
  Local fix applied:
  `mu-plugins/rusky-runtime-shim.php` now trims `gastronom-lang-switcher` only for sensitive runtime requests, not for ordinary logged-in frontend views.
- Live recovery fact:
  the active MU runtime owner on production was still `rusky-disable-far.php`, not `rusky-runtime-shim.php`.
  During a site-wide `503`, the site recovered only after temporarily filtering `gastronom-lang-switcher` out of active runtime through that live MU file.
  Current live state should be treated as:
  - site restored
  - `gastronom-lang-switcher` temporarily bypassed on live
  - plugin must not be re-enabled until its failure path is isolated
  - emergency storefront shell now runs through:
    `/Users/alexandertartakovsky/Projects/gastronom-migration/mu-plugins/rusky-language-switcher-lite.php`
  - that lite MU currently owns only:
    - language cookie/query state
    - switcher output + active button state
    - switcher stylesheet reuse
    - emergency front-page cleanup for duplicated bilingual blocks and category labels
    - emergency normalization of front-page menu/search/account/cart chrome
  - it is intentionally not a full replacement for `gastronom-lang-switcher`
  - public live verification completed after multiple tiny uploads:
    - homepage stayed on `200`
    - RU and SK front-page menu labels normalized
    - RU and SK `screen-reader-text` / `aria-label` menu strings normalized
    - duplicate skip-link text removed
    - active switcher state verified in isolated RU Chrome tab
  - this lite MU must remain temporary and must not keep growing into a second full language system

## Next recommended step

1. Freeze further live storefront patching unless a new production regression appears.
2. Isolate the exact failure path inside `gastronom-lang-switcher` outside the emergency window.
3. Rebuild or refresh access to the real Chrome session.
4. Validate the runtime shim change with PHP lint if a PHP binary is available.
5. Regenerate:
   - `2026-04-10-custom-runtime-inventory.json`
   - `2026-04-10-custom-runtime-inventory.md`
6. Continue architecture hardening by choosing the next low-risk real migration boundary from `gastronom_*` into the new scaffold surfaces.
   Current best candidate from the language plugin boundary map:
   - storefront messaging dedupe is now completed locally
   - shop display dedupe is now completed locally
   - weighted / commerce dedupe is now completed locally
   - sorting dedupe is now completed locally
   - `gls/v1` Dotypos diagnostic / repair REST surface is now quarantined into:
     `/Users/alexandertartakovsky/Projects/gastronom-migration/mu-plugins/rusky-dotypos-maintenance.php`
   - local `gastronom-lang-switcher` is now reduced to a mostly language/runtime shell
   - diff vs `gastronom-lang-switcher.live-current.php` is now zero again
   - next operational step is no longer another refactor cut
   - next operational step is the controlled re-enable experiment described in:
     `/Users/alexandertartakovsky/Projects/gastronom-migration/.plans/2026-04-10-gls-controlled-reenable-plan.md`

## Latest findings

- Direct Dotypos writes inside `gastronom-stock-fix.php` are limited to two guarded code paths:
  - confirmed preorder deduction
  - confirmed preorder restore
- The file also contains a high-leverage vendor integration override:
  - removal of Dotypos `handle_product_updated` from `woocommerce_update_product`
- Two Dotypos-intent helper functions exist in the file but are not referenced elsewhere by name in the current repo snapshot:
  - `gastronom_apply_dotypos_stock_to_wc_product()`
  - `gastronom_resolve_dotypos_order_sync_quantity()`
- The preorder-weight flow has now been documented as its own state machine:
  - preorder product config
  - order-item metadata at checkout
  - Woo-only piece reservation
  - `await-weight`
  - manager confirmation AJAX
  - delayed Dotypos sync
  - transition to `pending` or `on-hold`
- The target split is now explicit:
  - `rusky-weight-preorder.php`
  - `rusky-dotypos-stock-bridge.php`
  - `rusky-stock-normalization.php`
  - residual cleanup of `gastronom-stock-fix.php`
- The first implementation candidate is intentionally smaller:
  - `rusky-order-language-helpers.php`
- The first helper scaffold is now implemented locally with `rslh_*` names to avoid collisions while the old `gastronom_*` functions still exist.
- The preorder core now also has a local scaffold surface with `rwp_*` names for helper/core logic only, still without live hook migration.
- The full target split now exists locally as scaffold surfaces:
  - `rslh_*`
  - `rwp_*`
  - `rdsb_*`
  - `rsn_*`
- Controlled delegation is now already in place for:
  - stock normalization helpers -> `rsn_*`
  - stock normalization compatibility wrappers -> `rsn_*`
  - order/preorder language helpers -> `rslh_*`
  - preorder helper/core functions -> `rwp_*`
  - preorder state helpers -> `rwp_*`
  - preorder site-reservation / restore helpers -> `rwp_*`
  - preorder Dotypos bridge helpers -> `rdsb_*`
  - preorder Dotypos bridge compatibility wrappers -> `rdsb_*`
  - frontend order-page language helpers -> `ropl_*`
  - preorder notification / transition helpers -> `rpn_*`
  - preorder admin helpers -> `rpa_*`

## Latest local-only hardening step

Completed:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-11-100512-weight-preorder-compat-owner-cut.md`

What changed:

- `mu-plugins/rusky-weight-preorder.php`
  now owns guarded compatibility wrappers for the `gastronom_*` weight/preorder helper surface
- the same file now also owns preorder site stock reservation / restore helpers through:
  - `rwp_reserve_site_stock()`
  - `rwp_restore_site_stock()`
- `plugins/gastronom-stock-fix/gastronom-stock-fix.php`
  now keeps that entire slice only as guarded fallback definitions

Result:

- local monolith ownership shrank again without any live storefront change
- the next residuals are mostly stock normalization / Dotypos state entry points rather than preorder helper duplication

Completed after that:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-11-100708-stock-normalization-compat-owner-cut.md`

What changed:

- `mu-plugins/rusky-stock-normalization.php`
  now owns guarded compatibility wrappers for the generic `gastronom_*` stock-normalization and catalog-policy surface
- `plugins/gastronom-stock-fix/gastronom-stock-fix.php`
  keeps those functions only as guarded fallback definitions

Result:

- local `gastronom-stock-fix.php` shrank again without touching live storefront behavior
- remaining residual ownership is now concentrated even more around Dotypos-order-state entry points and hook orchestration

## Latest verified live fix

Completed:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-11-100914-ru-sorting-label-live-fix.md`

What changed:

- deployed only:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/mu-plugins/rusky-product-sorting.php`
- fixed the category sorting dropdown so the RU alphabetical option is no longer hardcoded in Slovak

Verified:

- `?lang=ru` category path now renders `По алфавиту`
- `?lang=sk` category path still renders `Podľa abecedy`
- live browser snapshot on category RU confirms the combobox label visually

Completed after that:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-11-101034-dotypos-bridge-compat-owner-cut.md`

What changed:

- `mu-plugins/rusky-dotypos-stock-bridge.php`
  now owns guarded compatibility wrappers for the `gastronom_*` delayed Dotypos preorder bridge surface
- `plugins/gastronom-stock-fix/gastronom-stock-fix.php`
  keeps those bridge functions only as guarded fallback definitions

Result:

- local `gastronom-stock-fix.php` shrank again

Completed after that:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-11-104500-sellable-storefront-freeze-check.md`

What changed:

- no new live deploy
- performed a freeze-check on the current live RU storefront control paths

Verified:

- homepage `?lang=ru` -> `200`
- category `?lang=ru` -> `200`
- product `?lang=ru` -> `200`
- cart `?lang=ru` -> `200`
- checkout `?lang=ru` -> `302` back to `/cart/?lang=ru`
- browser verification confirms homepage, category, and product remain sellable in RU
- category sorting still shows `По алфавиту`

Residual:

- category and product snapshots still expose duplicated skip-link text
- this remains a non-blocking storefront residual and was not patched in the same cycle

Completed after that:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-11-104900-custom-runtime-inventory-refresh.md`

What changed:

- refreshed `/Users/alexandertartakovsky/Projects/gastronom-migration/inventory_custom_runtime.py`
- inventory discovery now includes the full current `mu-plugins/rusky-*.php` runtime owner set
- regenerated:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/.plans/2026-04-10-custom-runtime-inventory.json`
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/.plans/2026-04-10-custom-runtime-inventory.md`

Result:

- runtime inventory now covers `20` current runtime files
- the current owner split is now reflected in the audit surface instead of the old partial file list

Completed after that:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-11-105100-monolith-wrapper-coverage-audit.md`

Result:

- extracted `gastronom_*` wrapper names from the monolith and from all `rusky-*` owner files
- monolith wrappers: `80`
- owner wrappers: `82`
- uncovered monolith wrappers in owner files: `0`

Meaning:

- the residual `gastronom-stock-fix.php` shell is now fully owner-covered locally
- remaining uncertainty is no longer missing extraction, but retention/live-vendor validation

Completed after that:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-11-105500-dotypos-shim-vendor-boundary-audit.md`

Result:

- rechecked the two unresolved Dotypos shim wrappers against the full repo
- direct references still exist only in the owner bridge, the monolith fallback, and snapshots
- the workspace still does not contain Dotypos vendor/plugin source needed to prove live runtime wiring
- deletion remains unsafe locally

Meaning:

- the last unresolved shell residue is no longer an extraction problem
- it is now a live/vendor boundary proof problem only

Completed after that:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.plans/2026-04-11-integration-readiness-verdict.md`

Verdict:

- current local hardening state is ready for the next intentional integration step
- readiness is based on:
  - sellable storefront freeze-check
  - full owner coverage of the monolith wrapper surface
  - refreshed runtime inventory
  - explicit containment of the remaining Dotypos shim uncertainty

Open boundary:

- the only explicit unresolved technical boundary is still Dotypos vendor/live wiring proof for the two retained shim wrappers
- remaining residuals are now concentrated mostly in hook orchestration and status-transition entry points

Completed after that:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-11-101915-preorder-status-orchestration-owner-cut.md`

What changed:

- `mu-plugins/rusky-weight-preorder.php`
  now owns guarded compatibility wrappers for preorder status-transition and stock-control orchestration
- `plugins/gastronom-stock-fix/gastronom-stock-fix.php`
  keeps those entry points only as guarded fallback definitions with named hook callbacks

Result:

- one more large orchestration slice moved out of the monolith without any live storefront change
- `gastronom-stock-fix.php` is now much closer to minimal fallback wiring plus a small residual surface

Completed after that:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-11-102132-order-admin-hook-wiring-owner-cut.md`

What changed:

- anonymous order-page/admin hook callbacks in `plugins/gastronom-stock-fix/gastronom-stock-fix.php` were replaced with named guarded callbacks
- matching compatibility wrappers were added in:
  - `mu-plugins/rusky-order-page-language.php`
  - `mu-plugins/rusky-preorder-admin.php`

Result:

- the remaining monolith surface is now mostly policy/bootstrap residue rather than large embedded callback blocks

Completed after that:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-11-102417-bootstrap-setup-hook-cut.md`

What changed:

- the remaining anonymous bootstrap/setup hooks at the top of `plugins/gastronom-stock-fix/gastronom-stock-fix.php` were converted to named guarded callbacks
- `mu-plugins/rusky-dotypos-maintenance.php` REST route registration was also converted to named callback form

Result:

- the monolith is now largely free of anonymous hook blocks
- residual cleanup is reduced to a small named policy/bootstrap surface rather than mixed anonymous runtime code

Completed after that:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-11-102632-commerce-and-preorder-enable-compat-cut.md`

What changed:

- `mu-plugins/rusky-commerce-adjustments.php`
  now owns guarded compatibility wrappers for the COD fee / gateway description / checkout refresh surface
- `mu-plugins/rusky-weight-preorder.php`
  now owns the guarded compatibility wrapper for `gastronom_weight_preorder_enabled()`
- `plugins/gastronom-stock-fix/gastronom-stock-fix.php`
  keeps these functions only as guarded fallback definitions

Result:

- one more residual ownership slice moved out of the monolith
- remaining cleanup is now very small and mostly policy residue

Completed after that:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-11-102818-stock-policy-scaffold-owner-cut.md`

What changed:

- added new extraction-ready owner file:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/mu-plugins/rusky-stock-policy.php`
- moved the remaining shared stock-policy/bootstrap helper surface into `rsp_*` functions there
- added guarded compatibility wrappers for the matching `gastronom_*` setup surface

Result:

- even the policy/bootstrap residue now has a dedicated owner file
- `gastronom-stock-fix.php` is now close to a pure compatibility shell

Completed after that:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-11-103009-stock-policy-fallback-guard-cut.md`

What changed:

- converted the matching stock-policy/setup functions in `plugins/gastronom-stock-fix/gastronom-stock-fix.php` into guarded fallback definitions
- fixed a local duplicated-guard mistake during that conversion

Result:

- the stock-policy/bootstrap layer is now fallback-only in the monolith
- remaining cleanup is now extremely small

Completed after that:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-11-103509-dotypos-wrapper-reference-audit.md`

What changed:

- performed a repo-local reference audit for:
  - `gastronom_apply_dotypos_stock_to_wc_product()`
  - `gastronom_resolve_dotypos_order_sync_quantity()`

Result:

- no repo-local call sites were found outside the Dotypos bridge owner file and the monolith fallback
- these wrappers stay classified as shim-retention functions until a separate live/vendor wiring audit happens

## Immediate live rule

- do not re-enable `gastronom-lang-switcher` on live until the plugin failure path is isolated outside the emergency window
- keep live storefront stabilization limited to the lite MU surface and one verified visual/runtime fix at a time
- do not expand `rusky-language-switcher-lite.php` beyond emergency storefront continuity; root-cause work must move back to the original plugin / controlled replacement path
- if the storefront is already in an acceptable selling state, freeze live immediately and do not apply cosmetic polish on top of the emergency layer
- current live runtime has already been switched back to `gastronom-lang-switcher`
- emergency `rusky-language-switcher-lite.php` is currently renamed out of the active MU set
- site and `wp-login.php` remained on `200` immediately after the switch
- current unresolved post-switch item is front-page cleanup confirmation for:
  - duplicate skip-link structure
  - breadcrumb block
  - `vw-page-title`

## Latest verified live state

- live runtime remains on the original `gastronom-lang-switcher`
- emergency `rusky-language-switcher-lite.php` remains disabled by rename
- live `rusky-disable-far.php` still blocks only `real-time-find-and-replace`
- site and `wp-login.php` are still on `200`
- front-page breadcrumb block and `vw-page-title` were removed again after the post-reenable cleanup patch
- front-page duplicate skip-link structure still remains in raw HTML and is still unresolved
- the checkout empty-cart language mismatch was narrowed to redirect state loss, not to the cart translation layer itself
- a new freeze baseline was saved after exact storefront verification:
  - `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-10-192500-storefront-freeze-baseline.md`

## New completed live-safe block

Completed:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-10-191000-gls-language-persistence-and-checkout-redirect.md`

What changed in the main plugin:

- `?lang=ru|sk` now persists into the `gastronom_lang` cookie again
- same-site redirects now preserve the current `lang`

Immediate live verification after deployment:

- `https://ruskyobchod.sk/` -> `200`
- `https://ruskyobchod.sk/wp-login.php` -> `200`
- `https://ruskyobchod.sk/checkout/?lang=ru&gls_verify=15` -> `302`
  - `Set-Cookie: gastronom_lang=ru`
  - `Location: https://ruskyobchod.sk/cart/?lang=ru`
- final followed response now renders the empty cart in Russian again:
  - `Корзина`
  - `Ваша корзина пока пуста.`
  - `Вернуться в магазин`

Completed after that:

- empty cart / checkout shell cleanup in the original plugin
  - removes only markup wrappers for empty-state shell:
    - `bradcrumbs`
    - `vw-page-title`
  - does not touch non-empty checkout/cart forms

Immediate live verification after deployment:

- `https://ruskyobchod.sk/` -> `200`
- `https://ruskyobchod.sk/cart/?lang=ru&gls_verify=17`:
  - `wc-empty-cart-message` present
  - `cart-empty` present
  - `bradcrumbs` markup block gone
  - `vw-page-title` markup block gone
- `https://ruskyobchod.sk/checkout/?lang=ru&gls_verify=18`:
  - `302` to `https://ruskyobchod.sk/cart/?lang=ru`
  - final HTML keeps Russian empty-cart content
  - `bradcrumbs` markup block gone
  - `vw-page-title` markup block gone

Failed and reverted:

- one isolated front-page malformed `skip-link` cleanup attempt
  - result did not change output
  - reverted immediately
  - not part of the freeze baseline

## Updated next safe step

1. Do not switch runtime owners again.
2. Keep the emergency MU layer frozen.
3. Freeze live storefront work at the current verified selling baseline.
4. Resume the architecture-hardening track only in local code.
5. Next operational target returns to the planned low-risk extraction boundary in `gastronom-stock-fix.php`.

## New local-only hardening progress after storefront freeze

Completed locally only:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-10-193500-preorder-admin-ui-controlled-migration.md`
- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-10-194500-preorder-product-meta-controlled-migration.md`
- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-10-195500-order-page-language-wiring-controlled-migration.md`
- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-10-200500-preorder-storefront-wiring-controlled-migration.md`
- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-10-201500-preorder-notification-wrapper-collapse.md`
- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-10-202500-order-page-wrapper-collapse.md`
- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-10-203500-admin-wrapper-collapse.md`
- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-10-204500-order-page-hook-fallback-collapse.md`
- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-10-205500-admin-ajax-fallback-collapse.md`

Admin confirmation workflow ownership widened inside:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/mu-plugins/rusky-preorder-admin.php`

New helper surface there now includes:

- `rpa_render_product_preorder_fields()`
- `rpa_save_product_preorder_fields()`
- `rpa_hidden_meta_box_ids()`
- `rpa_render_order_admin_footer()`
- `rpa_handle_weight_confirmation_ajax()`

`gastronom-stock-fix.php` now keeps hook wiring but delegates these helper concerns to `rpa_*` when available.

This was a local hardening cut only:

- no live deploy
- no storefront runtime change
- no checkout/cart impact

Order-page language ownership also widened locally inside:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/mu-plugins/rusky-order-page-language.php`

New helper surface there now includes:

- `ropl_localize_order_item_name()`
- `ropl_render_forced_order_lang_marker()`
- `ropl_maybe_redirect_context_order_lang()`
- `ropl_start_order_page_buffer()`

`gastronom-stock-fix.php` now keeps the hook registration for that block, but delegates these runtime concerns to `ropl_*` when available.

Storefront preorder UX/runtime ownership also widened locally with a new owner file:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/mu-plugins/rusky-preorder-storefront.php`

That new local owner now contains helper bodies for:

- quantity input normalization
- add-to-cart normalization and validation
- preorder cart price recalculation
- preorder cart item metadata
- single-product preorder note
- cart preorder notice
- preorder payment-gateway shaping
- `/ kg` suffix
- checkout order language capture
- checkout preorder line-item metadata capture

`gastronom-stock-fix.php` still keeps the live Woo hook registration for now, but those hook bodies now delegate to `rpsf_*` when available.

Notification / transition duplication was also reduced locally:

- `gastronom_send_preorder_created_emails()`
- `gastronom_send_weight_confirmation_email()`
- `gastronom_mark_weight_confirmed_order_ready()`

inside `gastronom-stock-fix.php` are now thin wrappers only and delegate to the dedicated owner:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/mu-plugins/rusky-preorder-notifications.php`

Order-page duplication was reduced further:

- `gastronom_get_context_order_for_frontend_language()`
- `gastronom_normalize_order_page_html()`

inside `gastronom-stock-fix.php` are now also thin wrappers and rely on:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/mu-plugins/rusky-order-page-language.php`

Order-page hook callbacks were also collapsed further:

- `wp_head`
- early `template_redirect`
- later `template_redirect`

inside `gastronom-stock-fix.php` no longer carry a second inline fallback implementation when `ropl_*` owners exist.

Admin duplication was reduced further:

- `gastronom_render_weight_confirmation_box()`
- `gastronom_order_screen_ids()`

inside `gastronom-stock-fix.php` are now thin wrappers and rely on:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/mu-plugins/rusky-preorder-admin.php`

The inline fallback body inside `wp_ajax_gastronom_confirm_weight` was also removed from the monolith and now relies on:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/mu-plugins/rusky-preorder-admin.php`

## Current recommended next local cut

Continue in the same monolith area, one semantic block at a time:

1. keep collapsing duplicated fallback bodies where dedicated owners already exist
2. only after that, reassess whether `gastronom-stock-fix.php` can be reduced to mostly hook wiring plus a few residual stock/order state functions
3. keep live storefront frozen

## 2026-04-11 live storefront status

Following the `handoff.zip` quick-fix rules, the live focus shifted back to the actual broken contour: logged-in homepage in browser.

Live file changed deliberately over SSH:

- `/home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`

What was fixed and browser-verified on `https://ruskyobchod.sk/?lang=ru`:

- logged-in homepage no longer shows RU and SK hero/body blocks together
- switcher active state now works:
  - RU has `active`
  - SK is inactive
- malformed duplicated `skip-link` is gone

Server backups for this file now include:

- `gastronom-lang-switcher.php.pre-logged-in-homefix-2026-04-10-1958`
- `gastronom-lang-switcher.php.pre-active-state-2026-04-11-0006`
- `gastronom-lang-switcher.php.pre-skiplink-2026-04-11-0010`

Worklog:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-11-001200-homepage-live-verification-and-switcher-state.md`

Current storefront posture:

- homepage runtime is stable again in the user-visible logged-in contour
- next storefront defects must still be handled one at a time with immediate browser verification
- do not resume broad live hardening or multi-defect deploys

## 2026-04-11 additional live storefront fixes

After the homepage recovery, the next reproduced RU defects were handled one by one in browser on category, cart, and checkout.

Live files changed deliberately:

- `/home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/mu-plugins/rusky-storefront-messaging.php`
- `/home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/mu-plugins/rusky-commerce-adjustments.php`
- `/home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`

Browser-verified outcomes:

- RU category banner now shows `Бесплатная доставка при заказе от 100 €!`
- cart shipping labels now show RU:
  - `Самовывоз`
  - `GLS доставка на адрес`
  - `SK Packeta пункт выдачи (Z-Point, Z-Box)`
  - `GLS Баликомат`
- cart pickup-note lines are now RU
- checkout payment labels now show RU:
  - `Оплата картой`
  - `Оплата при получении`
  - `Банковский перевод`
- cart/checkout shell is cleaner:
  - single `skip-link`
  - footer heading `Гастроном`
  - cookie button `Ок`
  - checkout breadcrumb/title path `Оформление заказа`

Newest server backups now also include:

- `rusky-storefront-messaging.php.pre-banner-lang-2026-04-11-0015`
- `rusky-commerce-adjustments.php.pre-checkout-labels-2026-04-11-0025`
- `rusky-commerce-adjustments.php.pre-card-label-2026-04-11-0044`
- `gastronom-lang-switcher.php.pre-cart-checkout-shell-2026-04-11-0030`
- `gastronom-lang-switcher.php.pre-checkout-breadcrumb-2026-04-11-0035`
- `gastronom-lang-switcher.php.pre-checkout-breadcrumb-2026-04-11-0041`

Worklogs:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-11-001200-homepage-live-verification-and-switcher-state.md`
- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-11-004800-cart-checkout-ru-live-stabilization.md`

Storefront status at this point:

- homepage, category, cart, and checkout RU path are materially cleaner and browser-verified
- the live site is usable again for the reproduced RU sales path
- next work should either:
  1. reproduce another concrete storefront bug in browser and fix only that one
  2. or return to local-only architecture hardening without more live mixing

## 2026-04-11 local-only hardening resumed

After the storefront RU path was stabilized, local-only hardening resumed without live deploys.

Latest local owner cut:

- widened `/Users/alexandertartakovsky/Projects/gastronom-migration/mu-plugins/rusky-commerce-adjustments.php`
  to own the checkout commerce-adjustment block:
  - COD fee
  - checkout payment refresh script
  - shipping/payment label localization
  - gateway description localization

`/Users/alexandertartakovsky/Projects/gastronom-migration/plugins/gastronom-stock-fix/gastronom-stock-fix.php`
now keeps only named wrapper/fallback hooks in that slice and delegates to `rca_*` when available.

Worklog:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-11-010500-commerce-adjustments-owner-cut.md`

Additional local owner cut:

- widened `/Users/alexandertartakovsky/Projects/gastronom-migration/mu-plugins/rusky-order-language-helpers.php`
  so it now provides guarded compatibility wrappers for the `gastronom_*` order-language helper surface
- wrapped the same helper definitions in
  `/Users/alexandertartakovsky/Projects/gastronom-migration/plugins/gastronom-stock-fix/gastronom-stock-fix.php`
  with `if (!function_exists(...))`

New worklog:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-11-011500-order-language-compat-owner-cut.md`

Another local owner cut followed in the same pattern:

- widened `/Users/alexandertartakovsky/Projects/gastronom-migration/mu-plugins/rusky-order-page-language.php`
  with guarded compatibility wrappers for:
  - `gastronom_get_context_order_for_frontend_language()`
  - `gastronom_normalize_order_page_html()`
- wrapped the same definitions in
  `/Users/alexandertartakovsky/Projects/gastronom-migration/plugins/gastronom-stock-fix/gastronom-stock-fix.php`
  with `if (!function_exists(...))`

New worklog:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-11-012500-order-page-compat-owner-cut.md`

Latest local-only cut after that:

- converted preorder product-meta anonymous hooks in
  `/Users/alexandertartakovsky/Projects/gastronom-migration/plugins/gastronom-stock-fix/gastronom-stock-fix.php`
  into named wrappers:
  - `gastronom_render_product_preorder_fields()`
  - `gastronom_save_product_preorder_fields()`
- these now delegate to the preorder admin owner in
  `/Users/alexandertartakovsky/Projects/gastronom-migration/mu-plugins/rusky-preorder-admin.php`
  with fallback compatibility kept in the monolith

New worklog:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-11-013500-preorder-product-meta-wrapper-cut.md`

Latest local-only cut after rereading `handoff.zip`:

- converted the preorder storefront Woo hook block in
  `/Users/alexandertartakovsky/Projects/gastronom-migration/plugins/gastronom-stock-fix/gastronom-stock-fix.php`
  from anonymous callbacks to named wrappers
- those wrappers now delegate to the storefront owner in
  `/Users/alexandertartakovsky/Projects/gastronom-migration/mu-plugins/rusky-preorder-storefront.php`
  while preserving fallback compatibility

New worklog:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-11-014500-preorder-storefront-wrapper-cut.md`

Latest local-only cut after that:

- widened `/Users/alexandertartakovsky/Projects/gastronom-migration/mu-plugins/rusky-preorder-storefront.php`
  with guarded compatibility wrappers for the `gastronom_*` preorder storefront/runtime surface
- wrapped the same storefront functions in
  `/Users/alexandertartakovsky/Projects/gastronom-migration/plugins/gastronom-stock-fix/gastronom-stock-fix.php`
  with `if (!function_exists(...))`

New worklog:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-11-015500-preorder-storefront-compat-owner-cut.md`

Latest local-only cut after that:

- widened `/Users/alexandertartakovsky/Projects/gastronom-migration/mu-plugins/rusky-preorder-admin.php`
  with guarded compatibility wrappers for:
  - `gastronom_render_product_preorder_fields()`
  - `gastronom_save_product_preorder_fields()`
  - `gastronom_render_weight_confirmation_box()`
  - `gastronom_order_screen_ids()`
- wrapped the same admin functions in
  `/Users/alexandertartakovsky/Projects/gastronom-migration/plugins/gastronom-stock-fix/gastronom-stock-fix.php`
  with `if (!function_exists(...))`

New worklog:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-11-021000-preorder-admin-compat-owner-cut.md`

Latest local-only cut after that:

- widened `/Users/alexandertartakovsky/Projects/gastronom-migration/mu-plugins/rusky-preorder-notifications.php`
  with guarded compatibility wrappers for:
  - `gastronom_send_preorder_created_emails()`
  - `gastronom_send_weight_confirmation_email()`
  - `gastronom_mark_weight_confirmed_order_ready()`
- wrapped the same functions in
  `/Users/alexandertartakovsky/Projects/gastronom-migration/plugins/gastronom-stock-fix/gastronom-stock-fix.php`
  with `if (!function_exists(...))`

New worklog:

- `/Users/alexandertartakovsky/Projects/gastronom-migration/.worklogs/2026-04-11-022500-preorder-notifications-compat-owner-cut.md`
