# Meta scheduler and storefront baseline cleanup

Date: 2026-05-11

## Context

After the Search Console fix, the working tree still had untracked Meta scheduler files and the storefront baseline verifier was failing because its default product URL returned `404`.

These were treated as unfinished local work and cleaned up instead of being left as loose files.

## Changes

- Added the Meta scheduler runbook, env example, dated content plan, and scheduler CLI.
- Updated `tools/meta-content-scheduler.mjs` so dry-run mode does not fail on already-recorded past posts that have Meta IDs.
- Kept execute mode strict: past timestamps are still not accepted for new API execution.
- Updated `docs/meta-api-scheduler-runbook.md` to explain that the checked-in May 2026 plan is a recorded plan and new campaigns should use a copied future-dated plan.
- Updated `tools/verify-storefront-baseline.sh` default product path to a currently live product:
  - `/produkt/su-ienky-ovsen-300g-pechene-ovsyanoe-300g/`

## Verification

- `node --check tools/meta-content-scheduler.mjs`: passed.
- `node tools/meta-content-scheduler.mjs --plan docs/meta-content-schedule-2026-05-09.json`: passed.
  - already-recorded past Meta IDs are reported as blocked/recorded
  - future Facebook photo operations are printed as dry-run operations
  - future Instagram/Reels remain blocked with an explicit reason
- `./tools/verify-storefront-baseline.sh`: passed.
- `git diff --check`: passed.
