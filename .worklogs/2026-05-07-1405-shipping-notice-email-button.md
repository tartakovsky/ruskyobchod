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

## Follow-up

- Updated the email copy to say the order is ready and the date is the planned handoff to the courier service.
- For GLS orders, the default handoff date now comes from the latest successful GLS Pickup History entry instead of today's date.
- Verified order `11277` resolves carrier `GLS` and default date `2026-05-11` on live.
- Sent a corrected test email to `gastronom@trtk.me`; no customer email was sent.
- Replaced the nested metabox form with a nonce-protected admin-post link button. The WooCommerce order edit screen already contains a form, so the nested form could redirect to the wrong admin section instead of sending the notice.
- Restored the separate `Уведомление об отправке` block and replaced the subtle last-sent text with a visible green confirmation panel inside the block.
- Verified the final UI with order `11277`: the customer notification was sent to `golovenkoigor52@gmail.com`, and the block displays the sent confirmation.
