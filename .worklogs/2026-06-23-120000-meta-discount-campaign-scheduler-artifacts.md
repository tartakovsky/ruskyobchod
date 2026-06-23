# Meta discount campaign scheduler artifacts

## Context
- Follow-up cleanup of local repo state after the 2026-06-22 Meta scheduling work.
- These files were created by the agent and were left uncommitted.
- Temporary `tmp-*` diagnostic files from the Meta Business Suite probing were removed from the working tree.

## Changes
- `tools/meta-content-scheduler.mjs`
  - Added support for Facebook Page text/feed scheduled posts through the Graph `/feed` endpoint.
  - Updated usage text to describe feed/photo/video scheduling.
- `docs/meta-content-schedule-2026-06-22-discount-campaign-draft.json`
  - Preserves the approved discount campaign draft for 2026-06-23 through 2026-07-06.
- `docs/meta-content-schedule-2026-06-22-discount-campaign-facebook-graph.json`
  - Captures the Graph API-ready version of the campaign plan with 49 posts.
- `docs/meta-business-suite-reels-runbook.md`
  - Documents the Business Suite Reels Composer path and current reel slots.

## Verification
- `node --check tools/meta-content-scheduler.mjs`
- JSON parse check for both campaign plan files.

## Notes
- No Meta API execution was performed in this cleanup step.
- Network request/response probe artifacts were intentionally not committed.
