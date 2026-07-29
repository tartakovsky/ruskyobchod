# Order 11398 delayed fiscalization and automatic recovery

## Incident

WooCommerce order `#11398` for `109.40 EUR` arrived while the Dotypos POS was
unavailable. WooCommerce and the Dotypos warehouse fallback both completed,
but the POS action returned HTTP 404 and no fiscal receipt was created.

## Guarded recovery

After `order/hello` confirmed that branch `120313449` was online, the order was
fiscalized once. Receipt `3241671301645581` is paid and not canceled, and the
operator confirmed that the physical receipt printed.

Creating the delayed POS receipt repeated the stock movement already made by
the warehouse fallback. Exact pre-receipt quantities were captured for all 25
lines, every second movement was offset, and read-back confirmed that all 25
stocks returned to their captured values.

The issued receipt totals `109.44 EUR`, four cents above the WooCommerce total.
It was not canceled or reissued automatically because the physical receipt had
already printed. The cause was independent per-line POS rounding.

## Permanent fix

`rusky-dotypos-fiscalization.php` now:

- schedules retryable POS 404, 5xx, timeout, DNS and connection failures;
- delays retries outside opening hours until 08:15;
- checks `order/hello` before retrying;
- finds an existing `WC-<order id>` receipt before creating one;
- captures every Dotypos stock immediately before delayed fiscalization;
- compensates only the second POS stock movement with one bulk stockup;
- verifies that every stock is either wholly deducted or wholly restored before
  any write, and stops on mixed state;
- makes uncertain-response retries idempotent by checking current stocks first;
- limits automatic retries to 24 attempts;
- corrects the final POS-line rounding residual so future receipt totals match
  the WooCommerce order total exactly.

## Verification

Production PHP lint passed. The live fiscalization verification confirmed:

- WooPayments, Stripe variants and COD are eligible;
- transient errors are classified as retryable;
- the retry action hook is registered;
- order `#11398` now calculates an exact `109.40 EUR` POS-line total;
- fiscal audit since 2026-07-01: eligible `4`, verified `4`, missing `0`.

The unrelated June campaign drafts, assets, renderer and temporary screenshot
remain outside this operational change.
