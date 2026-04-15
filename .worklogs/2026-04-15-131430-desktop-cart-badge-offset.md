## Context

The cart quantity badge in the desktop header sat too high over the cart icon and reduced click comfort.

## Goal

Move the badge slightly downward on desktop only, without touching the theme files directly.

## Changes

- added a desktop-only override in `rusky-mobile-header-polish.css`
- set `span.cart-value` to `bottom: -8px` for `min-width: 992px`

## Safety

- CSS-only
- desktop-only
- isolated override layer
- no impact on checkout, cart logic, cash, or Dotypos
