# Pin the live GLS asset surface

## Finding

The active production `gastronom-lang-switcher.php` matched Git, but its two companion assets did not:

- `gls-script.js` differed by 16 lines.
- `gls-style.css` differed in switcher, footer, and catalogue-grid rules.

The legacy JavaScript is intentionally not enqueued. The production CSS is active and defines the currently verified storefront appearance. Staging has neither a usable copy of this plugin nor matching storefront routes, so it cannot safely arbitrate the CSS variants.

## Resolution

- Captured the exact current production JS and CSS contents into Git without changing production.
- Preserved the previous repository variants in Git history.
- Added both companion assets to the existing live critical-file hash verifier so future drift is detected before a blind deployment.

## Verification

- Local SHA-256 now matches production for PHP, JS, and CSS.
- `verify-language-runtime-surface.sh` passed for the homepage, account, and cart.
- The verified pages contain no `gls-script.js`, `translateAll`, legacy localStorage language runtime, or legacy MutationObserver runtime.
- The server-rendered switcher and RU account/cart language shell remain present.
- No production or staging runtime file was changed as part of the reconciliation.
