# commerce tail stop-line

## summary

The late-shell SK commerce tail inside `gastronom-lang-switcher.php` was reduced further by removing dead residual pairs one at a time under the existing five-check verification contract.

## completed in this block

Removed dead late-shell pairs for:

- SK terms label tail
- SK order-note placeholder tail
- SK ship-to-different-address tail
- SK `i18n_required_text`
- SK `i18n_optional_text`
- SK site-suffix tail
- SK order-note label tail
- SK optional-marker tail

## execution contract used

Every step used the same contract:

1. edit one file
2. `git diff --check`
3. run:
   - `tools/verify-storefront-baseline.sh`
   - `tools/verify-checkout-shell.sh`
   - `tools/verify-account-shell.sh`
   - `tools/verify-commerce-shell.sh`
   - `tools/verify-commerce-shell-sk.sh`
4. `commit`
5. `push`
6. deploy one file:
   - `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`
7. rerun the same five checks against live

## result

Live remained stable throughout this block.

Current state at block close:

- storefront baseline green
- checkout shell baseline green
- account shell baseline green
- commerce shell RU green
- commerce shell SK green

## stop line

This commerce-tail phase is now at a rational stop-line.

Do not continue removing residual pairs in this area blindly.

Next work in this zone should happen only if:

- a remaining pair has clear exact-output proof value, or
- the work is part of a separate owner-migration mini-plan

