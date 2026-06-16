# Close live tail sync

## Context

The working tree still contained older changes that had been left uncommitted while live files had already moved forward. The goal was to close those tasks without mixing in new behavior or unsafe one-off scripts.

## Safety review

Compared local hashes with live hashes for:

- `wp-content/mu-plugins/rusky-front-performance-tuning.php`
- `wp-content/mu-plugins/rusky-shipping-notice-email.php`
- `wp-content/mu-plugins/rusky-packeta-admin-assist.php`
- `wp-content/plugins/gls-shipping-for-woocommerce/includes/admin/class-gls-shipping-pickup.php`

All four local files matched live exactly, so these are repo catch-up changes, not new production changes.

Removed untracked local-only one-off scripts from the working tree instead of committing them:

- `tools/packeta-courier-pickup.php`
- `tools/send-packeta-pickup-request.php`
- `tools/verify-gls-printlabels-payload.php`

Reason: they contained specific operational contact/order/customer/parcel details and were not suitable as clean reusable repo tools.

## Synced changes

### Product page performance trim

`rusky-front-performance-tuning.php` already had a matching live change that extends the performance asset trim from homepage-only to safe storefront surfaces, while excluding cart, checkout, and account pages.

Existing related worklog:

- `.worklogs/2026-05-19-1825-product-page-performance-trim.md`

### Shipping notice and Packeta

`rusky-shipping-notice-email.php` already had matching live changes that:

- separate GLS tracking from Packeta tracking
- render Packeta tracking IDs as `Z...`
- build Packeta tracking URLs with `tracking.packeta.com`
- move the shipping notice controls into the Packeta admin box for Packeta orders
- change wording from courier-specific to carrier-specific

### Packeta admin assist

`rusky-packeta-admin-assist.php` existed on live and is now brought into the repo. It adds admin-only hints for the Packeta order metabox and does not run on the storefront.

### GLS Slovakia pickup guard

`class-gls-shipping-pickup.php` already had matching live changes that:

- show a warning for Slovakia accounts
- disable the legacy GLS pickup submit button for Slovakia
- return a `WP_Error` instead of submitting a pickup request for Slovakia

Reason: GLS Slovakia label generation sends parcel data / creates labels, but this legacy pickup form can produce misleading technical success for courier pickup.

## Verification

Passed:

- `sh tools/verify-live-php-syntax.sh`
- `./tools/verify-storefront-baseline.sh`
- `./tools/verify-admin-order-screen.sh`

Commerce shell checks:

- `./tools/verify-commerce-shell.sh` failed one pre-existing text expectation: RU checkout terms label
- `./tools/verify-commerce-shell-sk.sh` failed one pre-existing text expectation: SK optional marker

Those failures were not caused by the synced live-tail files in this cleanup pass. They are left as a separate checkout text verification follow-up rather than patched into this broad sync.
