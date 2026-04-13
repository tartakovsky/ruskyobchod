## Summary

Confirmed a single live-vs-repo critical-file drift and promoted the clean live PHP syntax verifier into the evening integration gate.

## Findings

- `tools/verify-live-php-syntax.sh` passes on:
  - `gastronom-lang-switcher.php`
  - `gastronom-stock-fix.php`
  - all live `rusky-*.php` MU files
- `tools/verify-live-critical-file-hashes.sh` found one real drift:
  - `rusky-commerce-adjustments.php`

## Interpretation

- the drift is not a broad live parity failure
- runtime audit shows commerce behavior on live is still owned by `gastronom_*` fallback hooks in `gastronom-stock-fix.php`
- this makes the drift real but not a Tuesday integration blocker

## Changes

- added `live php syntax` to `tools/verify-evening-integration-gate.sh`
- added `live-php-syntax.txt` to evening evidence bundles
- added `live-php-syntax.txt` to evidence comparison

## Deferred item

- `rusky-commerce-adjustments.php` parity deploy remains deferred until after the integration window or until a commerce-specific blocker appears
