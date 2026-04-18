# 2026-04-18 13:12:00 Elementor stop-line guard

## why

- current stable live state requires `Elementor 4.0.2` active and `Elementor Pro 3.18.3` inactive
- accidental reactivation of the old Pro package can crash `/wp-admin/`
- verification alone is not enough; live needs a hard runtime guard

## what changed

- added `wordpress/wp-content/mu-plugins/rusky-elementor-stopline.php`
- the mu-plugin:
  - checks the installed `Elementor` and `Elementor Pro` versions
  - treats `Elementor 4.x` + `Elementor Pro non-4.x` as a blocked pair
  - filters `active_plugins` early so incompatible `elementor-pro` does not load
  - persists the safe plugin set by updating `active_plugins`
  - shows a one-time admin notice explaining why Pro was deactivated

## intended outcome

- accidental reactivation of `Elementor Pro 3.18.3` should no longer be able to take down admin
- stable stop-line becomes enforced by runtime, not just by operator memory
