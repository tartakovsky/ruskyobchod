# Meta schedule archives

## Context

Several Meta content schedule JSON files were left untracked after prior content planning and scheduling work.

## Change

Archived the schedule records under `docs/`:

- `docs/meta-content-schedule-2026-05-15.json`
- `docs/meta-content-schedule-2026-05-18.json`
- `docs/meta-content-schedule-2026-05-28.json`
- `docs/meta-content-schedule-2026-06-08.json`

These files are documentation/operations records only. They do not affect the WordPress runtime.

## Verification

Validated all four files with:

- `python3 -m json.tool`

Security scan:

- no Meta tokens or passwords were found
- files do contain historical Meta object IDs, captions, UTM links, and local asset paths as operational records
