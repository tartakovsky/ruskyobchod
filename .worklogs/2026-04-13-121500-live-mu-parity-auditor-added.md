## live mu parity auditor added

### scope

Add a read-only tool to compare local and live `rusky-*.php` MU files without relying on memory or old notes.

File added:

- `tools/audit-live-mu-parity.sh`

### current result

The auditor currently reports:

- local-only:
  - `rusky-runtime-shim.php`
- remote-only:
  - none

So the remaining `mu-plugins` drift is now explicit and small.

### value

This removes another source of operational ambiguity before Tuesday:

- no hidden MU file drift
- no need to reconstruct parity status from handoff prose
- the last remaining gap can now be treated as an intentional defer, not an unknown
