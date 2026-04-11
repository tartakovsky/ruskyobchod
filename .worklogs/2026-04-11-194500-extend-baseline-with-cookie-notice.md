## Summary

Extended `tools/verify-storefront-baseline.sh` with cookie notice checks on the RU homepage.

## Added checks

- accept button text `Ок`
- close button aria-label `Нет`

## Reason

Cookie notice is still partly covered by retained fallback logic, so it should stay in the repeatable live baseline instead of being checked only manually in the browser.
