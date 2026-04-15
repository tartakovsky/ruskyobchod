# 2026-04-15 10:32 mobile header polish

## Context

Mobile homepage header on live was visually broken:

- logo sat on a red square
- account/cart/language controls looked scattered
- menu toggle still carried a red inner icon background

## Change

Added an isolated mobile-only header polish layer in `mu-plugins`:

- `wordpress/wp-content/mu-plugins/rusky-mobile-header-polish.php`
- `wordpress/wp-content/mu-plugins/rusky-mobile-header-polish.css`

This keeps the fix out of the dirty theme and `gls-style.css` files.

## Result

- logo background is transparent on mobile
- search, account, cart and language controls are visually aligned
- menu toggle is blue without the red inner square
- no cash register / Dotypos / checkout logic changed

## Verification

- deployed only the two new `mu-plugin` files over SSH
- homepage returns `200`
- `wp-login.php` returns `200`
- anonymous mobile homepage checked in DevTools after deploy
