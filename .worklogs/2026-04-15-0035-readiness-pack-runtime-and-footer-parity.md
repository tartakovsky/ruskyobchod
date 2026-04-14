## Context

After the safe runtime audit, two additional control gaps remained:

1. the main readiness pack did not verify that critical language pages stay on the current server-first path
2. the live critical file hash verifier did not include the theme footer file, even though repo/live drift was found there

These are control-surface gaps, not production behavior changes.

## Change

Updated:
- [`tools/verify-tuesday-readiness.sh`](/Users/alexandertartakovsky/ruskyobchod/tools/verify-tuesday-readiness.sh)
- [`tools/verify-live-critical-file-hashes.sh`](/Users/alexandertartakovsky/ruskyobchod/tools/verify-live-critical-file-hashes.sh)

### `verify-tuesday-readiness.sh`

Added:
- `verify-language-runtime-surface.sh`

This means the main readiness pack now also checks:
- no `gls-script.js` on audited critical pages
- no legacy `MutationObserver` language runtime on those pages
- no legacy `translateAll(...)` path there
- server-rendered switcher and shell remain present

### `verify-live-critical-file-hashes.sh`

Added theme footer parity check:
- `wordpress/wp-content/themes/food-grocery-store/footer.php`

That closes the exact drift class that was discovered during the runtime/log audit.

## Verification

Green:
- `./tools/verify-live-critical-file-hashes.sh`
- `./tools/verify-tuesday-readiness.sh`

## Result

The repo now has a stronger morning-ready control loop:

- business-critical stock checks remain green
- storefront/account/checkout/preorder checks remain green
- server-first language runtime on audited critical pages is now part of readiness verification
- repo/live drift on the footer template is now part of critical hash verification
