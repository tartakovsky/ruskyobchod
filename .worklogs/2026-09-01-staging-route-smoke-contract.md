# Pin the staging route smoke contract

## Finding

The apparent staging `404` was caused by testing the intentionally blocked production path `/staging-gastronom/`. The actual WordPress staging URL is the isolated host `https://staging.ruskyobchod.sk`.

The staging WordPress options already correctly declare that host. Its homepage, login, REST index, shop, cart, and account routes return HTTP 200. An empty checkout correctly redirects to the cart.

## Change

- Extended `verify-staging-isolation.sh` with explicit smoke checks for the login, REST, shop, cart, account, and empty-checkout routes.
- Kept the deliberately minimal staging plugin inventory and Twenty Twenty-Five theme unchanged.
- Did not copy the production database, orders, customers, GLS runtime, theme, or uploads into staging.
- Removed the two temporary WebP test files previously created in staging uploads; production copies remain intact.

## Verification

- Staging route smoke checks: passed.
- Staging isolation, no-index, external HTTP/mail guard, and minimal plugin inventory: passed.
- Production and staging theme inventory: passed.
- Production and staging WordPress core checksums: passed.

No production runtime file or setting was changed.
