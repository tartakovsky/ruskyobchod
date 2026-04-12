# 2026-04-12 11:15:00 CET

- Second half of the footer slice migration
- Removed old late footer-shell normalization for:
  - brand heading
  - legal company lines
- Kept the new earlier server-side owner path through `render_block`
- Residual footer shell still keeps cookie notice normalization only
- Validation after deploy:
  - exact-output check on RU homepage footer markers
  - `tools/verify-storefront-baseline.sh`
