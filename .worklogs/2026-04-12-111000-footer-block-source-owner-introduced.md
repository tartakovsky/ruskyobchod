# 2026-04-12 11:10:00 CET

- Started slice migration from retained footer shell cleanup toward earlier server-side ownership
- Added narrow footer block normalization path in `gastronom-lang-switcher.php`:
  - `gls_footer_block_needs_brand_normalization()`
  - `gls_footer_block_needs_legal_normalization()`
  - `gls_normalize_footer_block_content()`
- Wired it through `render_block`
- At this step, old residual footer shell normalization is still kept in place
- Goal of this step:
  - introduce earlier owner path first
  - verify exact output remains identical before touching the old residual
