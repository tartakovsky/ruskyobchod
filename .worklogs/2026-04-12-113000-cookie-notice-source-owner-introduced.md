# 2026-04-12 11:30:00 CET

- Introduced earlier server-side ownership for cookie notice labels through `cn_cookie_notice_args`
- Added `gls_cookie_notice_source_args()` in `gastronom-lang-switcher.php`
- New source-level ownership now sets:
  - `accept_text`
  - `refuse_text`
  - `aria_label`
- At this step the old residual output cleanup is still kept in place
- Goal:
  - verify exact-output equivalence before touching the old residual path
