# Shipping notice email button

Date: 2026-05-07

## Context

The store needs a manual order-page action for notifying a customer about the planned parcel dispatch date. The notification must be short and bilingual in Slovak and Russian, and it should reflect the carrier used by the order.

## Changes

- Added MU plugin `rusky-shipping-notice-email.php`.
- Added an order admin metabox named `Уведомление об отправке`.
- The metabox shows customer email, detected carrier, tracking numbers when available, and last sent status.
- Added a dispatch-date field and a manual `Отправить клиенту` action.
- The email contains Slovak and Russian sections in one message.
- GLS tracking numbers are detected from the order metadata and linked to GLS tracking when available.
- Successful sends are saved to order meta and recorded as an order note.

## Deployment

- Deployed the MU plugin to live:
  `/home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/mu-plugins/rusky-shipping-notice-email.php`
- Live PHP syntax check passed.
- WordPress bootstrap confirmed the MU plugin function is loaded.
- No customer notification email was sent during deployment.
