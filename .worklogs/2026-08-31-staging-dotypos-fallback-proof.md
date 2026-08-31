# Staging Dotypos fallback proof

## Scope

Created an isolated, disposable staging environment at
`https://staging.ruskyobchod.sk` to prove the loaded Dotypos ownership and
hook contract without touching production.

The staging environment has its own empty database. No production orders,
customers, uploads, API credentials, or product catalogue were copied.

## Safety boundary

- `WP_ENVIRONMENT_TYPE` is `staging` and `RUSKY_STAGING_MODE` is enabled.
- Staging has its own HTTPS URL and `blog_public=0`; `robots.txt` disallows
  all crawling.
- The staging-only guard blocks `wp_mail()` and all external WordPress HTTP
  requests before a network request is made.
- WordPress file updates are disabled in staging.
- All staging scripts use the isolated root and database only.

## Proof performed

1. Installed only WordPress core, WooCommerce, the Dotypos vendor plugin,
   `gastronom-stock-fix`, and the versioned MU-plugin layer required to load
   the current ownership contract.
2. Confirmed the owner contract with all plugins active.
3. Deactivated only `gastronom-stock-fix` in staging.
4. Re-ran the owner contract successfully:
   - bridge compatibility functions remained owned by
     `rusky-dotypos-stock-bridge.php`;
   - fiscalization ownership and priorities 5, 10, and 20 remained correct;
   - the vendor direct sale callback remained absent from priority 10;
   - stock restore remained owned by the bridge.
5. Restored `gastronom-stock-fix` immediately after the proof.

## Verification

- staging HTTPS returns `200` on PHP 8.2 without a fatal error;
- the staging isolation verifier passes;
- a direct staging guard test confirms external HTTP is blocked before the
  network;
- staging contains zero `shop_order` posts;
- `gastronom-stock-fix` is active after the test.

## Boundary of this proof

This proves the loaded owner and hook surface is independent of the
stock-fix fallback plugin. It does not create a sale, change stock, or make a
real Dotypos request. Any removal from production still requires a separate,
small code review of each retained fallback function and the normal production
verification gate.
