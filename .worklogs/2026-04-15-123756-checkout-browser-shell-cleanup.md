## Context

Browser screenshots still showed English checkout/cart artifacts on live runtime paths, especially on `/checkout/?lang=ru` with a seeded cart.

## Goal

Remove the remaining English wording server-side, without touching cash, Dotypos, stock logic, or broad legacy language files.

## Changes

- extended `rusky-theme-chrome-language.php` with narrow checkout/cart wording coverage
- localized checkout field labels/placeholders for RU/SK
- localized country placeholder for Woo/select2 checkout field
- localized no-shipping message in checkout/cart
- localized saved-payment checkbox text
- localized terms checkbox text through Woo filter output
- localized tax suffix `(includes ... VAT)` in checkout totals
- kept all changes inside the existing isolated `mu-plugin` language layer

## Live Verification

- reloaded live browser checkout `https://ruskyobchod.sk/checkout/?lang=ru` without cache
- verified no remaining English strings from the reported set:
  - `Street address`
  - `Select a country / region…`
  - `Phone`
  - `(optional)`
  - `There are no shipping options available...`
  - `I have read and agree to the website`
  - `(includes ... VAT)`
- reloaded live cart runtime and verified no leftover English shipping/update/order shell strings

## Readiness

- updated `tools/verify-commerce-shell.sh` to assert the current RU terms wording
- rerun readiness after the verifier update
