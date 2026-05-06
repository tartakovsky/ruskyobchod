# Delay Meta Pixel Load

## Context

Mobile Google PageSpeed dropped after adding Meta Pixel. Local Lighthouse confirmed the
site itself was still fast, but Meta's external browser script added about 140 KB of
third-party JavaScript and affected LCP/CLS in mobile throttled tests.

## Change

- Kept the Meta Pixel stub, `fbq('init')`, and queued `PageView` call in the head.
- Deferred loading `https://connect.facebook.net/en_US/fbevents.js` until browser idle,
  with a load-event fallback.
- Deployed only `wordpress/wp-content/mu-plugins/rusky-meta-pixel.php` to production.
- Cleared WP Super Cache after deployment.

## Verification

- Server `php -l` passed on the staged file before replacing production.
- Homepage returns `200`.
- `wp-login.php` returns `200`.
- Live homepage HTML contains the delayed `requestIdleCallback` loader and Pixel ID
  `988003717119707`.
- Mobile Lighthouse after deployment:
  - Performance: 97
  - FCP: 1.5 s
  - LCP: 2.4 s
  - TBT: 120 ms
  - CLS: 0
  - server response: 50 ms

## Notes

Meta events should still be queued before the external script finishes loading. This
keeps tracking active while reducing first-screen pressure for PageSpeed.
