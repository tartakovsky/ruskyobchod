# Transparent Gastronom header logo

- Synced main to f0722844, preserving unrelated marketing edits.
- User selected the local GASTRONÓM-only badge and requested a circle without a black background.
- Saved the transparent PNG to assets/brand/gastronom-logo-circle-transparent.png and imported it as WordPress attachment 11456.
- Set food-grocery-store custom_logo to 11456. The original stored theme mods had no custom_logo key; cached pages previously showed attachment 8575.
- Added the new media URL to the existing performance maps, serving WordPress's 300x285 PNG derivative on storefront pages.
- Backed up the previous performance file outside the web root at /home/u595644545/backups/logo-2026-09-07/ and cleared WP Super Cache.
- Verified PHP syntax, all storefront baseline checks, and the ordinary homepage in Chrome. The image loaded, corner alpha was 0, and the screenshot showed the circular badge without a black square.
- Rollback: restore the backed-up performance file, select prior attachment 8575 as custom_logo if restoring the previously visible logo, and clear page cache.
