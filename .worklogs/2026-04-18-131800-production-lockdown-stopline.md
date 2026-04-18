# 2026-04-18 13:18:00 production lockdown stop-line

## why

- the main remaining stability risk was production drift from wp-admin:
  - auto-updates
  - Woo database auto-update
  - plugin/theme editors
- repo-first rules already forbid these paths operationally
- live still needed a technical stop-line, not just a policy note

## what changed

- added `wordpress/wp-content/mu-plugins/rusky-production-lockdown.php`
- it disables:
  - WordPress automatic updater
  - core/plugin/theme/translation auto-updates
  - WooCommerce DB auto-update
  - direct file modifications for update/install/delete/editor contexts
- it also:
  - removes plugin/theme editor menu entries
  - blocks direct access to `plugin-editor.php` and `theme-editor.php`
  - shows a repo-first warning notice on update/editor related admin screens

## intended outcome

- live cannot silently drift via dashboard update/editor paths
- emergency stability now depends less on operator memory and more on enforced runtime policy
