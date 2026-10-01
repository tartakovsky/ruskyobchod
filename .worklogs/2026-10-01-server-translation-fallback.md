# Preserve native translations in the existing server language owner

User authorized a strictly safe correction under the existing translation
architecture. Reviewed handoff workflow/architecture policy and the August 31
output-buffer contract, legacy entrypoint removal, September 1 language-runtime
asset contract. Browser translation remains disabled; no new runtime owner,
hook, dictionary entry, HTML replacement or JavaScript is introduced.

Used clean isolated worktree, pulled origin/main, created
`fix/translation-fallback-20261001`. Main checkout's unrelated changes preserved.
Production/staging/repository baseline for rusky-theme-chrome-language.php:
`3212f795616322ab06916b2e4ea1e1b6c6eee8c98e453938c82248a82c98611a`.

## Root cause and change

The existing priority-120 gettext/ngettext closures returned source English
when their custom map had no entry, discarding language-pack translations.
Both closures now retain incoming translation unless the source map supplies
an explicit replacement. Existing translated-phrase normalization, overrides,
priorities, admin guards and language resolution are preserved. Plural fallback
retains the language pack's chosen form (important for Russian 1/2/5/21/22).
Only this one MU file is a runtime deployment target; no stock/product writes.

## Predeployment verification

The new read-only verifier fails on the original staging file at the native
translation preservation assertion, then passes with the candidate for RU/SK.
It covers unknown source, absent translations, explicit overrides, normalized
incoming translations, five plural counts, and the complete filter chain for
three stock phrases. The minimal staging installation has no Woo language packs;
the verifier explicitly reads production .mo catalogues into that isolated CLI
process. It does not copy files or change WordPress options. Production checks
use the real installed locale/catalogue without this test-only argument.

PHP lint on server outside web root and local git diff --check passed.
Backups created outside web root:
`/home/u595644545/backups/translation-fallback-20261001/`
with `production-rusky-theme-chrome-language.php` and
`staging-rusky-theme-chrome-language.php` at the baseline hash.

## Deployment and rollback

Commit and push the candidate before production deployment; rsync only the
changed MU file after checking the production baseline still matches. Verify
live RU/SK filter chains, actual browser availability/cart notices, existing
commerce/account/home surfaces and output-buffer ownership. No order submission.
If any regression occurs, restore only the matching backed-up MU file, verify
baseline hash and affected pages. No database rollback is necessary.

## Production verification

Candidate committed/pushed as 573dbc66 before deployment. Rechecked baseline,
then rsynced only the MU file. Production SHA256 equals repository candidate:
`cafe2aacf2436d4021e6168f401fdf72bf1a1ee69741ac07a86bd2eb5d973978`.
Live lint, RU/SK read-only regression verifier and output-buffer policy passed.
Accessible debug.log remains unchanged (5572 bytes, April 9 timestamp).

In an isolated browser context, verified:

- RU/SK homepage and account pages plus login return 200;
- server switcher remains present; no gls-script.js/translateAll runtime;
- zero-stock product shows Нет в наличии / Nie je na sklade;
- adding .1 kg Barbaris succeeds; attempted 1 kg and additional .6 kg are
  blocked, with both native stock notices correctly translated in RU/SK;
- cart quantity remains .1, checkout displays EUR1.50, card/COD methods and
  no error in each language; no order/payment submitted;
- test item removed and empty cart verified.

No stock, product, order, language option or catalogue file was changed.
