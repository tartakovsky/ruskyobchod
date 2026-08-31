# Output-buffer policy contract

## Scope

Added a read-only verifier for the intentional `template_redirect` output
buffer ownership. It loads WordPress through a temporary server-side script,
inspects registered callbacks, and removes that script afterwards.

## Contract captured

- Production uses the primary language plugin buffer at priority `5`.
- On production, the two legacy front-page fallbacks are removed after the
  primary language plugin is available.
- The catalogue language buffer at priority `20` remains owned by
  `rusky-theme-chrome-language.php`.
- The isolated staging profile deliberately has no primary language plugin;
  its local fallbacks at priorities `1` and `130` therefore remain active.

The older order-page buffer function has no registered hook. It is retained as
a compatibility wrapper rather than removed, because its external callers are
not proven absent.

## Verification

- shell syntax passed;
- staging policy passed;
- production policy passed;
- production storefront baseline passed after the static audit.
