Summary

Corrected the handoff docs so the quick-fix path preserves visible site translation instead of treating it as optional polish. The rebuild plan was also tightened to require server-rendered coverage across the visible storefront and checkout UI.

Decisions made

- Rejected the earlier framing that only "essential" translated labels need to come back first because that would knowingly ship a degraded bilingual storefront.
- Kept the constraint against reviving the legacy global JS rewrite path because the stability issue is still real.
- Defined the quick-fix target as full visible translation coverage through PHP-rendered output and narrow hooks.

Key files for context

- `docs/handoff/05-quick-fix-plan.md`
- `docs/handoff/04-architecture-review-and-rebuild-plan.md`
- `docs/handoff/03-current-state-and-experiments.md`

Next steps

- Implement the replacement language path so the visible site UI remains translated without re-enabling `gls-script.js`.
- Verify translation coverage on homepage, archive, product, account, cart, and checkout.
- Keep logged-in `503` testing separate from translation work.
