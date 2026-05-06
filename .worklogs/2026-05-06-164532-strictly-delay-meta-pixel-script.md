# Strictly Delay Meta Pixel Script

## Context

Google PageSpeed still reported a visible performance drop after the first Meta Pixel
delay. The idle callback version could still load Facebook's external script during
the audit window.

## Change

- Kept the immediate `fbq` queue and `PageView` call.
- Changed the external Facebook script loader to wait for the first user action
  (`pointerdown`, `keydown`, `touchstart`, or `scroll`).
- Added a seven-second post-load fallback for visitors who do not interact.
- Deployed only `wordpress/wp-content/mu-plugins/rusky-meta-pixel.php`.
- Cleared WP Super Cache.

## Verification

- Server `php -l` passed before deployment.
- Homepage returns `200`.
- `wp-login.php` returns `200`.
- Live HTML contains the strict delayed loader.
- Local Lighthouse runs after the change showed zero Facebook network requests during
  the no-interaction audit window.

## Notes

This keeps Meta tracking available for real users while preventing Facebook's external
JavaScript from loading during the first-screen no-interaction audit path.
