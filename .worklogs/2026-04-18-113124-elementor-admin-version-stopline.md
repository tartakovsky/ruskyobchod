# Elementor Admin Version Stop-Line

## Goal
- Restore a stable admin surface without introducing another blind plugin upgrade on live.

## What failed
- `wp-admin/` still crashed after the core/bootstrap recovery.
- The exact fatal came from an Elementor version mismatch:
  - `Elementor` live version `4.0.2`
  - `Elementor Pro` live version `3.18.3`
- Fatal message:
  - `Popups_Promotion_Menu_Item::get_parent_slug()` signature mismatch against the new Elementor base class.

## Stable stop-line
- Keep `Elementor` active.
- Keep `Elementor Pro` installed but deactivated until a compatible Pro package is available.
- Do not reactivate `elementor-pro` while core stays on `Elementor 4.x` unless Pro is upgraded to a compatible line.

## Recovery applied
- Temporarily disabled `elementor-pro` to recover admin access.
- Converted that emergency disable into a clean deactivated state instead of leaving the plugin directory renamed as a long-term hack.
- Removed the temporary admin fatal logger after diagnosis.

## Permanent repo guard
- Added `tools/verify-elementor-pair-compat.sh`.
- This check verifies:
  - Elementor core exists and is active
  - Elementor Pro package presence/activation
  - Pro is not active against an incompatible Elementor `4.x` core

## Root cause
- This was not a new `wp-content` code regression.
- It was a plugin-pair compatibility drift on live.

## Future rule
- Treat `Elementor` and `Elementor Pro` as one upgrade unit.
- Never update one side of that pair on live without verifying the other side first.
