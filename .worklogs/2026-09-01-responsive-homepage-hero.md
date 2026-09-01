# Responsive homepage hero

## Scope

- Added an 800x400 WebP derivative for the existing homepage hero.
- Extended the existing homepage performance MU-plugin to emit an 800w/1600w `srcset` for that hero.
- Kept the existing 1600x800 image as the desktop and high-density fallback.

## Deployment

- Generated `wp-content/uploads/2025/09/image_2400x1200-home-800.webp` from the original 2400x1200 PNG at WebP quality 82.
- Deployed the derivative to production and staging uploads.
- Backed up the production and staging MU-plugin files under `/home/u595644545/backups/codex-responsive-hero-20260901`.
- Staging uses different homepage content, so its MU-plugin was restored after syntax validation rather than being treated as a false functional proof.
- Production was deployed behind an automatic single-file rollback trap and retained only after live verification passed.

## Verification

- Remote PHP syntax: clean.
- Homepage HTML contains the 800w/1600w responsive candidates.
- Homepage and login return HTTP 200.
- Storefront RU/SK baseline: passed.
- Live critical-file hash parity: passed.
- Mobile Lighthouse after deployment:
  - performance: 88
  - LCP: 2252 ms
  - responsive-image failure for the hero: removed
  - hero transfer: 47,801 bytes, down from 145,502 bytes in the before audit

No checkout, language, Dotypos, product, theme, or database behavior was changed.
