## Summary

Added a cleanup-safe live verifier for the admin order screen weight-confirmation UI and wired it into the shared readiness path.

## Changes

- added `tools/verify-admin-order-screen.sh`
- confirmed live admin order screen markers:
  - inline weight panel
  - heading
  - nonce
  - order id field
  - actual weight field
  - confirm button
  - await-weight status label
  - footer AJAX action
  - footer reload handler
  - hidden meta box id
- updated `tools/verify-tuesday-readiness.sh` to include admin order screen verification
- kept `tools/verify-evening-integration-gate.sh` anchored on `verify-tuesday-readiness.sh`, so the new admin screen proof is now part of the evening gate automatically

## Result

Admin order screen verification is now part of the standard readiness and evening integration gate, not a one-off manual proof.
