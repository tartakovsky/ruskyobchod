# Structured Data Return Policy Fix

## Context
- Google Search Console reported an unparsable structured data issue for `https://ruskyobchod.sk/`: invalid value type.
- The live homepage JSON-LD had a global `OnlineStore` object with `hasMerchantReturnPolicy`.
- The policy used arrays for fields that Google's merchant listing documentation treats as scalar values in this context:
  - `applicableCountry`: `["SK","AT"]`
  - `returnMethod`: `["https://schema.org/ReturnByMail","https://schema.org/ReturnInStore"]`

## Changes
- Updated `wordpress/wp-content/mu-plugins/rusky-product-structured-data.php`.
- Changed the global policy owner type from `OnlineStore` to `Organization`, matching Google's recommendation for global merchant policies.
- Split the return policy into two `MerchantReturnPolicy` entries:
  - `#return-policy-sk` with `applicableCountry: "SK"`
  - `#return-policy-at` with `applicableCountry: "AT"`
- Changed `returnMethod` to the scalar enum value `https://schema.org/ReturnByMail`.
- Kept product offers referencing the SK default policy by `@id`.

## Deployment
- Uploaded candidate file to `/tmp/rusky-product-structured-data.php` on live Hostinger.
- Ran remote PHP lint before replacement:
  - `php -l /tmp/rusky-product-structured-data.php`: OK.
- Deployed only:
  - `wp-content/mu-plugins/rusky-product-structured-data.php`
- Cleared WP Super Cache through WordPress bootstrap with `wp_cache_clear_cache()` after WP-CLI cache flush segfaulted on the host.

## Verification
- `./tools/verify-live-php-syntax.sh`: OK.
- Public homepage and `wp-login.php` both return `200`.
- `./tools/verify-storefront-baseline.sh`: OK.
- `./tools/verify-commerce-shell.sh`: OK.
- `./tools/verify-commerce-shell-sk.sh`: OK.
- `./tools/verify-live-bootstrap-surface.sh`: OK.
- `./tools/verify-live-critical-file-hashes.sh`: OK for the audited critical files.
- Parsed public homepage JSON-LD after cache clear:
  - one JSON-LD block
  - valid JSON
  - `applicableCountry` is a string for both SK and AT
  - `returnMethod` is a string for both SK and AT
- Checked a live product page:
  - global `Organization` return policy is present
  - `Product` offer still has shipping details
  - `Product` offer references the return policy by `@id`
- No new fatal, parse, critical, or structured-data related entries were found in the current `wp-content/debug.log` tail.

## Notes
- Existing unrelated local changes in Meta schedule docs and `assets/` were not touched.
- `wordpress/wp-content/mu-plugins/rusky-product-structured-data.php` was untracked locally before this fix but was already present on live, so the file should be committed with this worklog to keep the repo as source of truth.
