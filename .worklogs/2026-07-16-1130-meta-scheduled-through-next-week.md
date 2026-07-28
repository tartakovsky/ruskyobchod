# Meta scheduling through next week

Date: 2026-07-16

Scope: Gastronom Facebook / Instagram scheduling in Meta Business Suite Planner.

Working paths used:

- Planner: `https://business.facebook.com/latest/content_calendar?business_id=883240858199545&asset_id=883209351536029`
- Existing scheduled plan source: `docs/meta-content-schedule-2026-07-16-through-2026-07-26.json`

## Result

Scheduled content from 2026-07-16 through 2026-07-26.

Discount announcements:

- Facebook text posts scheduled twice per day.
- Morning slot: SK copy at `10:30`.
- Evening slot: RU copy at `17:30`.
- 2026-07-16 SK slot was scheduled at `12:30` because the original `11:30` slot was too close after execution started.

Reels/media:

- 13:30 media slots scheduled using existing Meta video IDs from older uploads, avoiding a fresh local upload.
- Planner verification showed Facebook + Instagram reel cards for:
  - 2026-07-16 13:30
  - 2026-07-17 13:30
  - 2026-07-18 13:30
  - 2026-07-19 13:30
  - 2026-07-20 13:30
  - 2026-07-21 13:30
  - 2026-07-22 13:30
  - 2026-07-23 13:30
  - 2026-07-24 13:30
  - 2026-07-25 13:30
- 2026-07-26 13:30 showed Instagram reel in Planner. The Facebook video retry failed, so a Facebook text fallback with the same pelmeni caption was scheduled for 13:30.

## Media IDs reused

- `2042491066335235` - `20_sgushenka.mp4`
- `1497771241757201` - `31_pelmeni.mp4`
- `1517029786810307` - `13_caviar.mp4`
- `985007813989620` - `5_caviar.mp4`
- `1009737814781528` - `17_kvass.mp4`
- `1373010057976550` - `19_zefir.mp4`
- `998848216336740` - `35_pelmeni.mp4`
- `1002946955922092` - `38_caviar.mp4`
- `1024560230266345` - `27_smoked_fish.mp4`
- `1695444578574609` - `22_zefir.mp4`

## Verified in Planner

- Current week view verified for 2026-07-16 through 2026-07-18.
- Next week view verified for 2026-07-19 through 2026-07-25.
- Following week view verified for 2026-07-26.

## Notes for next run

Do not rediscover paths. Use the Planner URL above and the existing internal Composer mutation path if direct local upload is blocked.
