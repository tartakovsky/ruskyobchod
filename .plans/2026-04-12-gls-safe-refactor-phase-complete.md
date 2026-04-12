# 2026-04-12 10:05:00 CET

## Scope

Safe bounded refactor phase for:

- `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`

## Completion verdict

This phase is complete.

## Stable reference point

- Git commit: `00d840a7`
- Live state after verification:
  - storefront baseline green
  - checkout shell baseline green

## What was achieved

- Repeated replacement values, replacement maps, and regex patterns were extracted into named helpers.
- Refactor remained inside one runtime file.
- Risky deploy flow stayed constrained to one file at a time.
- Every risky deploy was checked with:
  - `tools/verify-storefront-baseline.sh`
  - `tools/verify-checkout-shell.sh`

## Failure handled inside the phase

- Attempted extraction of the empty-cart shell helper caused a live regression.
- That change was reverted immediately by single-step rollback.
- Conclusion: the retained empty-cart shell/output cleanup surface is not part of the safe refactor zone.

## Stop line

Do not continue helper extraction in this phase for:

- empty-cart shell orchestration
- retained fallback/output cleanup surface
- any step that requires proving exact output equivalence instead of local structural equivalence

## Next-phase rule

If work returns to `gastronom-lang-switcher.php`, it must be under a new mini-plan for risky residuals, not as continuation of the completed safe bounded-refactor phase.
