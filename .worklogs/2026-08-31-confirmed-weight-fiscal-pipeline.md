# Confirmed-weight fiscal pipeline

## Problem

Confirming the actual weight of a preorder sent a standalone Dotypos warehouse
sale and then moved the COD order to `on-hold`. The new fiscalization module
was never called from that branch, so no fiscal receipt or local print was
created. The confirmation email was sent after the stock movement but before
any fiscal receipt could exist.

## Design

The confirmed-weight flow has one POS stock owner:

1. recalculate and persist the confirmed weight;
2. transition the order to its payment-ready state without sending mail;
3. create and pay the fiscal POS action, which also owns the POS stock
   movement and requests `print-type: local`;
4. only when the POS action cannot be created, retain the existing warehouse
   sale as the stock fallback and let the fiscal retry path compensate any
   later duplicate POS movement;
5. send exactly one updated-total email to the customer.

This keeps the receipt and stock movement atomic in the successful case and
does not add a second sale beside it.

## Verification

`tools/prove-admin-weight-confirmation.sh` now intercepts all outbound HTTP and
mail calls for a temporary order. It asserts one fiscal POS action, local print
request, actual-weight quantity, fiscal state, no duplicate warehouse sale, and
one customer email. The temporary order and product state are restored.

## Production result

- deployed only `rusky-preorder-admin.php` and `rusky-preorder-notifications.php`;
- both production files passed `php -l`;
- the controlled COD proof with product `10310` and weight `0.38 kg` passed all
  assertions: one `order/create-issue-pay` request, `print-type: local`, one
  fiscal receipt state, zero fallback warehouse-sales calls, and one intercepted
  customer email;
- the proof order was deleted and its product stock/meta were restored.
