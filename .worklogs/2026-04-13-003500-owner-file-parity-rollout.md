## Owner-File Parity Rollout

Date: 2026-04-13

### Scope

Controlled live parity rollout for the missing Dotypos/preorder owner files:

- `wordpress/wp-content/mu-plugins/rusky-dotypos-stock-bridge.php`
- `wordpress/wp-content/mu-plugins/rusky-weight-preorder.php`

### Preconditions

- live `gastronom-stock-fix.php` parity had already been completed
- all five live verification contours were green before this step
- the parity constraint was respected: owner files were not deployed before the monolith parity file

### Live action

Deployed only:

- `wordpress/wp-content/mu-plugins/rusky-dotypos-stock-bridge.php`
- `wordpress/wp-content/mu-plugins/rusky-weight-preorder.php`

### Verification

Post-deploy verification stayed green for:

- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
- `tools/verify-account-shell.sh`
- `tools/verify-commerce-shell.sh`
- `tools/verify-commerce-shell-sk.sh`

Read-only file presence check on live confirmed both files are now present in `wp-content/mu-plugins/`.

### Result

The live file set for the Dotypos/preorder owner boundary now matches the repo closely enough to begin runtime proof work intentionally.

This was a parity step only.

It did not yet prove business-flow correctness for:

- preorder checkout state transitions
- actual-weight confirmation flow
- Dotypos stock mutation on confirmed preorder items
- restore flow on cancelled/refunded orders
