# Pending live tail cleanup

Date: 2026-05-07

## Context

The working tree still contained live changes from earlier May 1 work that had not been committed. Per the handoff workflow, live changes must be synchronized back into the repo instead of left as a dirty local tail.

## Classification

- Language/checkout changes are live and should be retained:
  - checkout country/select preservation
  - extra RU/SK checkout phrases
  - stand-down guards for older language fallback MU plugins when the main runtime is available
- Homepage performance/theme changes are live and should be retained:
  - front-page performance MU plugin
  - front-page category CSS
  - homepage icon replacements in the active theme templates
  - narrow homepage asset dequeue and derivative-image mapping
- May 1 worklogs document completed live actions and should be committed.
- Image/report helper scripts were made CLI-safe with an explicit WordPress root argument instead of a hardcoded live `wp-load.php`.

## Verification

- Local file hashes match live for all pending code files.
- Live PHP syntax checks passed for the changed PHP files.
- Helper script PHP syntax checks passed on the live PHP runtime after copying to `/tmp`.
- Anonymous homepage returns `200`.
- `wp-login.php` returns `200`.
- RU homepage returns `200`.
