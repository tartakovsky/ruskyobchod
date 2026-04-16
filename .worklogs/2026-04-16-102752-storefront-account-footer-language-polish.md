# Storefront Account Footer Language Polish

## Goal
- Save the remaining localization and storefront polish changes that were left unstaged after the 2026-04-15 audit sequence.
- Normalize account labels and footer wording in RU and SK without widening the existing runtime surface beyond the touched `gls` and theme CSS paths.

## Changes
- Block `gastronom-lang-switcher` translation runtime for logged-in operator requests.
- Normalize RU and SK storefront account labels to `Мой аккаунт` and `Môj účet`.
- Normalize RU footer legal/address wording, including `Палисады` and non-breaking spacing for the address line and `Братислава I`.
- Add `.gls-footer-address-line` styling to keep the injected footer address line stable.
- Remove forced uppercase transformation from the affected theme navigation text in both LTR and RTL stylesheets.
- Bump `gls` asset version from `6.25` to `6.26`.

## Verification
- `git diff --check`
- `git diff --stat`

## Constraints
- Local `php` binary is not available in this environment on 2026-04-16, so `php -l` could not be run from this machine before commit.
- No live deploy or browser verification was performed in this step.
