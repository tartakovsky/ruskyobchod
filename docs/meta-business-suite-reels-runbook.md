# Meta Business Suite Reels runbook

Date: 2026-06-22

Purpose: schedule Gastronom Facebook + Instagram reels through the Business Suite UI when the Graph API Page token is not available.

## Working path

Use this path for vertical reels:

1. Open Planner:
   `https://business.facebook.com/latest/content_calendar?business_id=883240858199545&asset_id=883209351536029`
2. Click `Create`.
3. Click `Create reel`.
4. Confirm the URL changes to:
   `https://business.facebook.com/latest/reels_composer/?asset_id=883209351536029&business_id=883240858199545...`
5. Upload the MP4 with `Add video`.
6. Add the caption in `Reel details` -> `Text`.
7. Wait for upload to reach `100%`.
8. Wait for copyright check: `No copyright issues were found.`
9. Click `Next`.
10. Leave Audio/Crop/Text/Optimisations unchanged unless the user requested an edit.
11. Click `Next`.
12. Select `Schedule`.
13. Set the Facebook and Instagram date/time.
14. Click `Schedule`.
15. Verify the reel appears in Planner on the target date/time.

## Do not use for reels

Do not use:

`https://business.facebook.com/latest/composer?...&composer_action=schedule`

That route creates a regular post. It rejects 720x1280 vertical videos for Instagram feed with the warning:

`The selected video doesn't fit within Instagram's accepted aspect ratio range of 4:5 to 16:9.`

The correct route is `reels_composer`, where 720x1280 is accepted for Reels.

## Current campaign reel slots

- 2026-06-23 13:30: `36_pelmeni.mp4`
- 2026-06-25 13:30: `38_caviar.mp4`
- 2026-06-27 13:30: `17_kvass.mp4`
- 2026-06-29 13:30: `27_smoked_fish.mp4`
- 2026-07-01 13:30: `22_zefir.mp4`
- 2026-07-03 13:30: `15_pancakes.mp4`
- 2026-07-05 13:30: `18_herring.mp4`
- 2026-07-06 13:30: `20_sgushenka.mp4`
