# Configure GLS pickup sender address

## Context

Creating a GLS pickup request returned `GLS API returned error code: 400`.

The pickup form was using the WooCommerce store fallback address, which produced incomplete API sender data:

- phone empty
- street contained `Zamocka 5`
- house number empty

## Change

Updated live `woocommerce_gls_shipping_method_settings` with a configured sender address:

- name: `Gastronom - Bratislava`
- contact name present
- street: `Zamocka`
- house number: `5`
- city: `Bratislava`
- postcode: `811 01`
- country: `SK`
- phone present
- email present
- marked as default

Also set the GLS plugin `phone_number` value.

The phone value is not repeated in this worklog.

## Verification

Read back the live option and confirmed the configured sender address is present.

## Notes

No pickup request was created by the agent during this step.
