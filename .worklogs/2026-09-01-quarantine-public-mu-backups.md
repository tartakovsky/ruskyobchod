# Quarantine publicly readable MU-plugin backup sources

## Finding

The production `wp-content/mu-plugins` directory contained 21 inactive
`.bak-*` and `.off` source files. Representative files returned HTTP 200 with
`Content-Type: text/plain`, exposing historical PHP source through the public
web root.

## Change

All 21 inactive artifacts were moved, not deleted, to:

`/home/u595644545/backups/codex-mu-artifacts-20260901`

The live security-surface verifier now fails if backup-style `.off`, `.bak`,
`.bak-*`, `.old`, or editor-backup files reappear at the top level of the
production MU-plugin directory.

## Verification

- three representative former URLs now return 404 instead of 200;
- all active production PHP files passed syntax checks;
- all critical production file hashes match Git;
- the storefront baseline and live security checks passed;
- active MU plugins and runtime code were not changed.
