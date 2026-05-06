# 2026-05-06 12:13:39 checkout guest flow

Goal: remove the checkout UX that made guest customers think registration or login was required before ordering.

## Change

- Added `wordpress/wp-content/mu-plugins/rusky-checkout-guest-flow.php`.
- On the public checkout only, the MU plugin:
  - removes the WooCommerce login prompt from `woocommerce_before_checkout_form`
  - disables checkout account creation
  - ensures checkout registration is not required
- The patch does not touch Dotypos, stock sync, shipping rates, payment gateways, or the legacy language switcher.

## Verification

- Server PHP syntax check passed before and after deploying the file:
  - `/home/u595644545/tmp-rusky-checkout-guest-flow.php`
  - `/home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/mu-plugins/rusky-checkout-guest-flow.php`
- Live homepage returned `200`.
- Live `wp-login.php` returned `200`.
- Created an anonymous HTTP cart session with product `10892`.
- Fetched live checkout with that cart and confirmed:
  - billing fields render
  - place-order button renders
  - `woocommerce-form-login` is absent
  - `name="username"` is absent
  - `id="createaccount"` is absent
  - `name="account_password"` is absent

## Notes

- Local `git pull --rebase origin main` was blocked by pre-existing unstaged and untracked changes in the working tree. Per handoff rules, those changes were not overwritten or stashed.
- Local `php -l` was unavailable because `php` is not installed in the local shell; syntax was checked on the Hostinger PHP runtime instead.
