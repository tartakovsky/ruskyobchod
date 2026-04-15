# 2026-04-15 10:54 elementor-pro readiness guard

## Context

`elementor-pro` remains active on live and is still a separate runtime/security risk register item.

At this stage the goal was not to edit or replace it on production, but to stop relying on ad hoc manual inspection.

## Change

Added a readonly verifier:

- `tools/verify-elementor-pro-readonly.sh`

Integrated it into:

- `tools/verify-tuesday-readiness.sh`

The new guard checks on live:

- `elementor-pro` is still the active plugin we expect
- the current entrypoint has a standard plugin header
- expected version string is present
- standard bootstrap hook is present
- the entrypoint does not contain the previously suspicious nullware marker
- the entrypoint does not contain obvious direct `base64_decode`, `wp_remote_post`, or `curl_exec` calls

## Verification

Green:

- `./tools/verify-elementor-pro-readonly.sh`
- `./tools/verify-tuesday-readiness.sh`

## Result

This does not “clear” `elementor-pro` as safe.
It does make the risk observable in the same morning-readiness loop as the rest of the store, without touching production behavior.
