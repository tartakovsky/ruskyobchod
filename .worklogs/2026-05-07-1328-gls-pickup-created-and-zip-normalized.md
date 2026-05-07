# GLS pickup created and pickup postcode normalized

## Context

The WooCommerce GLS Pickup form kept returning a MyGLS server-side `Object reference not set` HTTP 400.

To isolate whether the credentials and pickup endpoint worked, a direct MyGLS API request was sent from the live WordPress runtime with the same account and sender data, using:

- 1 package
- pickup window `2026-05-11 08:00-17:00`
- sender `Gastronom - Bratislava`
- postcode normalized as `81101`

## Result

MyGLS returned HTTP 200 with:

`{"PickupRequestErrors":[]}`

Operationally this means the pickup request was accepted by GLS. No further pickup submissions should be made for the same parcel unless a duplicate pickup is intended.

## Change

- Added an order note to order `#11277` recording the successful direct MyGLS pickup request.
- Normalized the saved GLS sender postcode to `81101`.
- Updated the GLS Pickup form code to remove whitespace from sender postcode before sending pickup requests.

## Deploy

Deployed the single changed file:

- `wp-content/plugins/gls-shipping-for-woocommerce/includes/admin/class-gls-shipping-pickup.php`

`php -l` passed before deployment.
