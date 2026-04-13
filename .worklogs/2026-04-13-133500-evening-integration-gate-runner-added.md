## evening integration gate runner added

### scope

Add one top-level go/no-go command for the evening integration window.

File added:

- `tools/verify-evening-integration-gate.sh`

### what it verifies

It runs and validates:

- `tools/verify-tuesday-readiness.sh`
- `tools/audit-live-mu-parity.sh`
- `tools/capture-live-active-plugins.sh`

Then it asserts:

- `real-time-find-and-replace` is not active
- `gastronom-lang-switcher` is active
- `woocommerce-extension-master/dotypos.php` is active
- there are no remote-only MU files
- the only local-only MU gap is `rusky-runtime-shim.php`

### result

The full gate runner was executed successfully end-to-end.

So there is now a single operational command that says whether the system is in the expected pre-integration state.
