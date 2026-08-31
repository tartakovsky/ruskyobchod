# Dotypos product-state verifier correction

## Issue

The verifier treated every negative `stockQuantityStatus` value as a missing
remote response. Dotypos can return a tiny negative floating-point remainder
for a zero stock value, for example `-8.32667268e-16`. The field was present
and matched the local cash-stock value, so this was a false failure.

The shell wrapper also ran remote cleanup in the same command after PHP. A
successful cleanup could mask a failed verifier process.

## Change

- A response is now considered missing only when the stock field itself is
  absent.
- The numeric value is compared using the existing tolerance.
- Cleanup is handled by a trap, preserving the actual PHP exit status.

## Verification

- Shell syntax passed.
- Product `10617` and control product `10781` both matched their Dotypos
  quantities after the correction.
- No product, stock value, order, or external Dotypos state was changed.
