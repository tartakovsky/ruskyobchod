# Full chain and unfinished-tail audit

## Scope

Closed the operational cycle after Packeta order `#11387`, stock deduction and
WooPayments fiscalization fixes.

Audited:

- storefront, account, cart and checkout in RU/SK;
- classic WooCommerce order admin and order-page language;
- Packeta packet creation, label and tracking notice;
- WooCommerce and Dotypos stock state;
- Dotypos fiscal receipts and payment-method routing;
- Dotypos Action Scheduler;
- WordPress bootstrap, Elementor compatibility and security surface;
- live/repository MU-plugin names and file hashes.

## Order 11387 final state

- paid and processing;
- Packeta packet `Z2067420203` created;
- weight `1.8` kg saved;
- label marked printed;
- Packeta API error empty;
- shipping notice sent with tracking;
- WooCommerce stock reduced;
- Dotypos warehouse sale marker present;
- fiscal receipt `2733160058614173` paid and not canceled;
- guarded stock offset complete;
- final Dotypos stocks: `2`, `20`, `10`.

## Production tails returned to Git

The corrected full hash audit exposed three live files that were legitimate
runtime owners but were still stale in Git:

- `rusky-commerce-adjustments.php`
  - current store-pickup date flow and admin/email order signals;
- `rusky-disable-far.php`
  - active runtime exclusion of the high-risk FAR plugin;
- `rusky-dotypos-stock-bridge.php`
  - current `/sales` and `/stockups` fallback bridge used by fiscalization.

The live versions were reviewed for embedded secrets and copied back into the
repository. No credentials are hardcoded; API values are read from WordPress
settings at runtime.

## Verification-tool defects fixed

`audit-live-mu-parity.sh` used locale-dependent sorting, producing the same two
files as both local-only and remote-only. It now uses `LC_ALL=C` consistently.

`verify-live-critical-file-hashes.sh` previously:

- allowed nested SSH to consume the loop stdin;
- opened one SSH connection per MU file;
- consequently checked only the first MU file or stopped under Hostinger
  connection throttling.

It now fetches one remote SHA-256 manifest and compares every shared MU file
locally. The final run verified every critical plugin/theme file and all 34
`rusky-*.php` MU files.

`verify-dotypos-fiscalization.sh` now also audits every eligible live
Stripe/WooPayments/COD order since `AUDIT_FROM` (default `2026-07-01`) against
the remote Dotypos paid receipt. Final result: eligible `3`, verified `3`,
missing `0`.

## Final gates

Passed:

- full Tuesday readiness suite;
- RU/SK commerce and preorder shells;
- admin order screen regression;
- Dotypos read-only stock checks;
- Dotypos scheduler: overdue `0`, recent failed `0`;
- fiscal gateway and live receipt audit;
- security surface;
- live bootstrap;
- Elementor compatibility;
- deterministic MU filename parity: no local-only or remote-only files;
- full live critical hash parity.

## Unrelated worktree classification

The completed Meta schedule source and its July 16 worklog are valid historical
records and are included in the closing commit.

The remaining modified June campaign drafts, brand assets, renderer and
temporary Composer screenshot form a separate incomplete creative workspace.
They are deliberately not included in this operational commit.

An older handoff note still calls for Hostinger/password rotation by the owner.
That is an external credential-maintenance action, not a code or runtime tail,
and was not changed during this audit.
