# 2026-05-01 22:02 - Rolled back ineffective homepage CSS inline attempt

## Context
- Goal remained mobile homepage performance improvement on live `ruskyobchod.sk` without touching commerce logic.
- A previous safe-looking attempt tried to inline homepage copies of:
  - `gls-style.css`
  - `blocks.css`
  - `block-frontend.css`
- The intention was to remove three blocking CSS requests from the critical path on the homepage.

## What I verified
- Fresh mobile PSI at `2026-05-01 21:59:57` still reported all three external CSS files under `Render blocking requests`.
- That meant the attempted dequeue did not actually remove those requests from the live critical path.
- Because the MU plugin still had the inline bundle logic, the site could end up with duplicated CSS: inline copies plus original linked styles.

## Action taken
- Removed only the ineffective inline/dequeue block from:
  - `wordpress/wp-content/mu-plugins/rusky-front-performance-tuning.php`
- Kept all earlier accepted homepage optimizations intact.
- Uploaded the corrected MU file to live as `.new`.
- Server-side PHP lint passed.
- Backed up the previous live MU file to:
  - `rusky-front-performance-tuning.php.bak-20260501-2206`

## Cache handling
- Because WP Super Cache had already stored homepage HTML, I invalidated only homepage cache artifacts and moved them to:
  - `/home/u595644545/homepage-cache-backup-20260501-2208`
- Then I warmed the RU homepage again.
- Confirmed a new cache page was generated at `2026-05-01 22:02:03`.

## Safety verification
- Homepage `/?lang=ru` -> `200`
- Cart `/cart/?lang=ru` -> `200`
- Empty checkout `/checkout/?lang=ru` -> `302` to cart

## Conclusion
- The ineffective CSS inlining experiment has been removed cleanly.
- Live is back to a simpler and safer state.
- Next measurements should reflect the pre-experiment baseline rather than duplicated homepage CSS.
