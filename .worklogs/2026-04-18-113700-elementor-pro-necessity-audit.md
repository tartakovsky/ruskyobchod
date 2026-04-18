# Elementor Pro Necessity Audit

## Goal
- Determine whether `elementor-pro` is currently required for the live site, not historically but for the present stable stop-line.

## Live findings
- Live `Elementor` current version: `4.0.2`
- Live `Elementor Pro` current package version: `3.18.3`
- `Elementor Pro` remains installed but deactivated because it is incompatible with the active Elementor core.

## Content audit
- Current non-revision Elementor documents on live: `5`
- Current non-revision Elementor documents with detected Pro signals: `0`
- Current Elementor-built documents:
  - page `27` `Gastronom`
  - page `147` `Kategórie produktov`
  - page `186` `Kontakt`
  - page `678` `Dodacie podmienky`
  - page `10860` `Dostavka a platba`
- Current Elementor library entries:
  - default kit `3839` `Набор по умолчанию`

## Conclusion
- There is no current evidence that the public live site requires Elementor Pro to render the active non-revision content.
- Historical revisions contain older Pro-like widget traces, but the currently live documents do not.
- Stable current policy:
  - keep `Elementor Pro` deactivated
  - do not reactivate it unless a compatible Pro package is available and a real business need is confirmed

## Repo follow-up
- `verify-tuesday-readiness.sh` should no longer require `verify-elementor-pro-readonly.sh` as part of the stable stop-line.
- `verify-elementor-pair-compat.sh` is the correct guard for the current live state.
