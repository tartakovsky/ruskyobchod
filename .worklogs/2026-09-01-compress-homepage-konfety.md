# Compress homepage Konfety image

## Scope

- Regenerated only `2026/02/Konfety-home-560.webp` at WebP quality 68.
- Updated the existing derivative generator so the production asset is reproducible.
- Did not change markup, PHP runtime behavior, layout, checkout, language, Dotypos, or database content.

## Safety and deployment

- Generated the candidate with the repository's GD-based derivative script in an isolated remote `/tmp` tree.
- Compared the current and candidate images visually at the full 560x560 output size before deployment.
- Verified the candidate on the staging uploads URL first.
- Backed up the production image to `/home/u595644545/backups/codex-konfety-compression-20260901/production-Konfety-home-560.webp`.
- Deployed the single image behind an automatic rollback trap and verified both the remote file and the publicly served bytes by SHA-256.

## Result

- File size: 81,570 -> 60,062 bytes (26% smaller).
- Lighthouse transfer size: about 60.2 KB.
- Lighthouse estimated remaining image-delivery waste: about 7.8 KB, down from 29.3 KB before compression.
- Storefront RU/SK baseline: passed.

Two post-deploy Lighthouse runs scored 86 and 84. The second run's 440 ms TBT came from JavaScript long tasks in Cookie Notice and jQuery; the changed lazy-loaded image has no script or execution path. FCP/LCP remained 1.7/2.4 seconds in that run. The asset improvement is therefore retained based on its verified byte reduction and unchanged storefront behavior rather than the volatile aggregate score.
