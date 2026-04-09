Summary

Corrected the handoff so the emergency path is a regression fix inside the existing `gastronom-lang-switcher` behavior, not a destructive translation-system replacement. The current `gls-script.js` disable is now documented as a diagnostic experiment only.

Decisions made

- Rejected the earlier quick-fix direction that pushed toward a server-side translation replacement during the incident.
- Treated "inputs worked yesterday" as the key operational constraint, which means the target state already existed recently and should be recovered with a narrow regression fix.
- Reframed the next step as isolating and removing only the focus-breaking DOM rewrite paths while preserving translation and switcher behavior.

Key files for context

- `docs/handoff/03-current-state-and-experiments.md`
- `docs/handoff/05-quick-fix-plan.md`
- `docs/handoff/04-architecture-review-and-rebuild-plan.md`

Next steps

- Recover the current live `gastronom-lang-switcher` asset set and compare it to the most recent known-good translated behavior.
- Re-enable `gls-script.js` only in a controlled copy and strip the form-breaking mutation/retry logic.
- Keep larger translation architecture changes in the rebuild track, not the emergency fix.
