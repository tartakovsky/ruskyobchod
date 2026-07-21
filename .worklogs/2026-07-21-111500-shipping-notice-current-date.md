# Shipping notice calendar current date

## problem

The dispatch-date picker in the WooCommerce order shipping-notice panel repeatedly opened on an old month, forcing the operator to page forward several months.

## cause

The form default preferred either:

- the dispatch date stored by the previous notice for the order, or
- for GLS, the date from the latest successful historical pickup request

That made a historical date behave like a static calendar default.

## fix

The form now uses `wp_date('Y-m-d')` every time the order panel is rendered. This respects the WordPress/Bratislava timezone.

Previously sent dates remain preserved in order metadata, notes and the visible sent-notice history. Only the editable field default changed.
