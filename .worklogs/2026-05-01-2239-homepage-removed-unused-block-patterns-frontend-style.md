# 2026-05-01 22:39 CEST — Removed unused homepage block-patterns frontend stylesheet

## Context

Continuing the mobile performance plan in the narrowest possible way after re-reading `handoff.zip`.

Goal: remove only one confirmed-unused render-blocking stylesheet from the homepage without touching checkout, cart, payments, delivery, weight logic, or broader theme structure.

## Read-only audit

Audited the live RU homepage head and local theme/MU enqueue sources.

Confirmed live stylesheet chain included:

- `wp-block-library`
- `gls-style.css`
- `blocks.css`
- `block-frontend.css`
- `bootstrap.css`
- `style.css`

Checked actual homepage HTML and local file contents:

- `block-frontend.css` is only `2051` bytes
- its selectors are limited to `.banner-section`, `.products-outer-box`, related `wc-block-grid`, and a few media-query variants
- the live RU homepage does **not** render those classes

This made `food-grocery-store-block-patterns-style-frontend` the cleanest low-risk candidate.

## Change

Updated:

- `wordpress/wp-content/mu-plugins/rusky-front-performance-tuning.php`

Added homepage-only dequeue/deregister for:

- `food-grocery-store-block-patterns-style-frontend`

No other live logic changed.

## Deployment safety

Deployed only the MU file as `.new`, then:

1. ran remote `php -l`
2. backed up the live MU file with a timestamped `.bak-*`
3. moved the linted file into place
4. cleared only homepage cache artifacts:
   - `wp-content/cache/supercache/ruskyobchod.sk/index-https.html`
   - `wp-content/cache/supercache/ruskyobchod.sk/index-https.html.gz`

## Verification

### Live HTML

Fetched `https://ruskyobchod.sk/?lang=ru` after deploy.

Confirmed:

- `block-frontend.css` is gone from the homepage `<head>`
- these remain:
  - `gls-style.css`
  - `blocks.css`
  - `bootstrap.css`
  - `style.css`

### Visual smoke check

Reloaded the live RU homepage in Chrome DevTools and verified:

- header intact
- account/cart icons intact
- hero intact
- categories intact
- footer intact
- cookie notice intact

### Storefront boundaries

Verified:

- homepage `/?lang=ru` -> `200`
- cart `/cart/?lang=ru` -> `200`
- empty checkout `/checkout/?lang=ru` -> `302` to cart

## Performance result

Fresh mobile PSI:

- previous reference: `1 May 2026, 22:02:32`
  - `Performance 91`
  - `Render blocking requests ~1150 ms`
- fresh report: `1 May 2026, 22:34:35`
  - `Performance 97`
  - `FCP 1.4 s`
  - `LCP 2.3 s`
  - `TBT 0 ms`
  - `CLS 0`
  - `Speed Index 3.1 s`
  - `Render blocking requests ~880 ms`

PSI report URL:

- `https://pagespeed.web.dev/analysis/https-ruskyobchod-sk/6vcg9k1rkf?hl=en-GB&form_factor=mobile`

## Conclusion

This was a successful narrow change.

I stopped here because the next remaining CSS/JS items (`blocks.css`, `bootstrap.css`, `style.css`, `gls-style.css`, `jquery/jquery-migrate`) are not confirmed-unused and would require a deeper structural pass rather than another safe one-line dequeue.
