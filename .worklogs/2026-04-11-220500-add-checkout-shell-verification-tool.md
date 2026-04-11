## Summary

Added a dedicated repo-local verification tool for the RU cart/checkout shell:

- `tools/verify-checkout-shell.sh`

## What it checks

- `cart/?lang=ru` returns `200`
- `checkout/?lang=ru` returns `302`
- checkout redirects to `/cart/?lang=ru`
- RU cart title
- RU empty-cart message
- RU main menu shell on cart
- RU cookie notice shell on cart

## Reason

Order/checkout cleanup should use an explicit shell verification path instead of relying on memory or ad hoc curl checks.
