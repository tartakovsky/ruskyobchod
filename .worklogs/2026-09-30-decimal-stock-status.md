# Fractional weighted stock: status lost on unrelated product saves

## Contract and scope

User requested a safe production correction following `docs/handoff.zip`.
Read all seven handoff documents. Main checkout has unrelated marketing and
fiscalization changes; preserved them and used a clean `fix/decimal-stock-20260930`
worktree, then `git pull --rebase origin main` (already current at d5a339a1).
Only `rusky-commerce-adjustments.php` is a runtime deployment target. Its
production and staging baseline SHA256 both match the repository:
`8d064377bef577832482aef84d94f30f922c979c9127a8dd00c2d886660e4a77`.

## Cause and alternatives

Production `WC_Product::validate_props()` casts quantity to integer before
checking availability. This runs on every save. The existing before-save
callback in commerce adjustments repairs positive quantity only when
`stock_quantity` is in the changeset. Status/image/price-only saves escape that
repair; even `wc_update_product_stock_status()` runs through validation again.

Options considered:

1. Manually force `_stock_status`: temporary and risks disagreeing visibility
   taxonomy/lookup state; rejected.
2. Patch WooCommerce core/vendor plugin: overwritten by upgrades and contrary
   to the handoff extension-boundary guidance; rejected.
3. Upgrade WooCommerce: staging already has native float validation, but a full
   production commerce upgrade needs its own compatibility rollout; deferred.
4. Extend the existing before-save callback: chosen; preserves one hook owner
   and lets WooCommerce persist status, taxonomy, lookup and caches together.

The existing quantity-change branch is retained. The added correction applies
only to simple, managed-stock products explicitly marked `_gls_weighted=yes`,
not portion-based preorders, with threshold zero and quantity between 0.000001
and 1. No new stock syncing, network calls, cron or mass repair on page loads.
No units, prices, photos, physical stock or integration settings are changed.

## Bounded repair

Fresh POS read at 2026-09-30T19:26:09Z agrees with Woo quantities:

| WC ID | Product | kg |
|---|---|---:|
| 10664 | Iris Zlaty Klucik | 0.500 |
| 10716 | Barbaris | 0.635 |
| 10717 | Brusnicove | 0.820 |
| 10721 | Snezok | 0.795 |

Product 10643 (salted garlic fat, .006) is deliberately excluded: its site
fractional flag is absent and POS has Piece/Kilogram configuration ambiguity.
Making a whole-piece product available with .006 pieces is not a safe fix.

The CLI repair defaults to plan-only, requires explicit IDs (max 20), validates
eligibility, and on apply writes an exclusive backup outside the web root.
External WP HTTP and mail are disabled for the repair. It checks quantities,
prices, images, publication, catalog visibility and backorders are unchanged.

## Verification before production

Local PHP unavailable; both candidate runtime and verifier linted with server
PHP outside the web root. `git diff --check` passed.
Staging isolation verifier passed (home/siteurl, routes, guard, minimal plugins).
Deployed the single candidate file to staging. The regression verifier passes
18 in-memory cases and persisted fixture creation, price-only save, status-only
save, transitions 0/.82/-.2/1.175/.5, and unchanged repeated save. Each persistence
check covers object stock/status, lookup and outofstock taxonomy. Fixture deleted.

Staging runs newer Woo with native float validation. Its test-only priority-9
callback emulates the production integer check on explicitly marked fixtures,
so staging cannot conceal the production regression. No core edits required.

## Backup and rollback

Outside web root: `/home/u595644545/backups/decimal-stock-20260930/`
contains `production-rusky-commerce-adjustments.php` and
`staging-rusky-commerce-adjustments.php` (original matching baselines).
If runtime regresses, restore just the corresponding file by SSH/rsync, verify
its baseline hash, homepage/login and affected products. Product metadata
snapshot is written before the bounded repair. Never restore stock quantities
from an old snapshot; for a full behavior rollback restore only the recorded
stock statuses via WooCommerce, after reverting code and rechecking current
stock. No database-wide restore is needed.

## Production result

Deployed the single runtime file by rsync after rechecking the baseline hash.
Candidate SHA256:
`ea3913a9c3dc61e3a26582b37ad17611e26eec28d69ca00bc2d3eed3e81c3e46`.
Live PHP lint and all 18 read-only cases passed against the actual production
WooCommerce version. Homepage and wp-login.php returned 200; accessible
wp-content/debug.log remained unchanged (5572 bytes, April 9 timestamp).

Bounded repair completed 2026-09-30T19:27:48Z with zero external HTTP attempts.
Backup: `product-status-before.json` in the directory above. All four products
now have instock in object/meta/lookup and no outofstock visibility term.
Browser verified all four product forms and availability; caramel category now
contains ten products including all three restored caramels. Added .1 kg
Barbaris to an initially empty browser cart: subtotal EUR1.50, no errors.
Checkout returned 200 with the same quantity/subtotal and card/COD methods.
No order/payment submitted; removed test item and verified empty cart.

Full before/after audit at 19:30:37Z covered 859 product records. Only the four
intended availability states changed. All audited quantities, prices, stock
management/preorder flags and publication states remained identical. Detailed
local evidence: `Projects/catalog-2026-09-29/decimal-stock-change-verification.json`.

Adjacent pre-existing UI limitation observed, not part of this stock-status
patch: weighted quantity input defaults to 1 even when max stock is below 1;
customer must choose a fractional quantity (the tested .1 kg works). A default
quantity UX correction should be scoped separately. Product 10643 remains
unavailable pending a unit/portion configuration decision.
