## tuesday readiness runner added

### scope

Add one top-level runner so Tuesday-morning verification does not depend on remembering a long list of separate commands.

File added:

- `tools/verify-tuesday-readiness.sh`

### what it runs

- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
- `tools/verify-account-shell.sh`
- `tools/verify-commerce-shell.sh`
- `tools/verify-commerce-shell-sk.sh`
- `tools/verify-preorder-shell.sh`
- `tools/verify-order-page-language.sh`
- `tools/verify-dotypos-readonly.sh`

### result

The full runner was executed successfully end-to-end.

This means there is now a single command that covers:

- storefront
- checkout
- account
- commerce RU/SK
- preorder product shell
- customer-visible order pages RU/SK
- read-only Dotypos connectivity and mapping

### value

This reduces operational risk for Tuesday morning:

- less manual command drift
- less chance of skipping an important contour
- more consistent verification after future targeted changes
