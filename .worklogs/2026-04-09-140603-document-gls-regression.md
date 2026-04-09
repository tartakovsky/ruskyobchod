Summary

Added a dedicated handoff note for the `gls-script.js` regression. The note explains the likely cause of the input-focus bug in simple language and gives the next agent an exact exploration order instead of vague guidance.

Decisions made

- Wrote the regression note as a separate handoff document because the existing quick-fix and incident docs were starting to carry too much mixed context.
- Framed the main risk in practical terms: repeated whole-page DOM rewriting plus retries, not "translation" in general.
- Gave the next agent a narrow exploration sequence centered on `innerHTML` rewrites, retry timers, and the whole-page `MutationObserver`.

Key files for context

- `docs/handoff/06-gls-regression-findings.md`
- `docs/handoff/03-current-state-and-experiments.md`
- `docs/handoff/05-quick-fix-plan.md`
- `wordpress/wp-content/plugins/gastronom-lang-switcher/gls-script.js`

Next steps

- Compare the current live `gls` asset set against the imported repo copy and confirm whether `gls-script.js` is actually enqueued on production.
- Test the likely offenders in order: broad `innerHTML` rewrites first, then retry timers, then the `MutationObserver`.
- Keep any emergency fix inside the existing translation path unless the narrow regression hunt fails.
