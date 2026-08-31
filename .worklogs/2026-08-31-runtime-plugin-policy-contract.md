# Runtime plugin policy contract

## Scope

Added a read-only verifier for the existing `option_active_plugins` filters.
The verifier uploads a temporary PHP file, loads WordPress for each synthetic
request context, reads the filtered active-plugin list, and removes the file.
It does not dispatch a page handler, change WordPress options, create orders,
or call external APIs.

## Contract captured

The check makes the current request-scoped rules explicit:

- Elementor Pro and the retired FAR plugin remain excluded.
- A logged-in frontend request does not load the Dotypos vendor plugin.
- A logged-in order page also omits the language switcher and page cache.
- Admin, REST, and cron keep the Dotypos plugin available.
- The WooCommerce analytics proxy keeps only its intentionally small plugin
  set, including WooCommerce and excluding Dotypos and Packeta.

Production and the isolated staging environment have distinct, intentional
active-plugin profiles. The verifier identifies the staging flag and checks
the staging profile without requiring production-only plugins.

## Verification

- shell syntax passed;
- all seven contexts passed on isolated staging;
- all seven contexts passed on production;
- no production code or settings were changed.

## Integration

The verifier is part of `verify-architecture-contract.sh`, so future
architecture work cannot silently change this dynamic loading policy.
