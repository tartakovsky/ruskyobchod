## order-page logged-in boundary stop-line

date: 2026-04-14 13:05 Europe/Bratislava

### scope

Close the remaining `503` on customer order-page links that reproduced only after opening Woo admin in the same browser profile.

### confirmed cause

The failing path was still request-owned by too much logged-in frontend runtime.

Order pages were already separated from late `GLS` buffering, but they were still sensitive to:

- `GLS` loaded as an active plugin in the same request
- logged-in frontend admin overlays/plugin stack in the same browser profile

The stable stop-line is to keep customer order pages under a single owner:

- `rusky-order-page-language.php`

and to exclude the extra logged-in frontend stack from that request path.

### live actions completed

1. `gastronom-lang-switcher.php`
   - block `GLS` output buffering on order pages
   - block `GLS` locale/gettext translation ownership on order pages

2. `rusky-order-page-language.php`
   - disable logged-in frontend overlays on order pages
   - disable logged-in frontend plugin stack on order pages
   - trim active plugins for logged-in order-page requests:
     - `gastronom-lang-switcher/gastronom-lang-switcher.php`
     - `wp-super-cache/wp-cache.php`
     - `google-analytics-dashboard-for-wp/gadwp.php`

3. `rusky-preorder-admin.php`
   - remove heavy global admin JS observer that was hanging the order edit screen

### verification

- exact order state:
  - `11124` -> `on-hold`, `cod`, `18.20`, `lang=ru`, `requires=no`
  - `11126` -> `on-hold`, `cod`, `20.60`, `lang=sk`, `requires=no`
- `verify-order-page-language.sh` green after the order-page owner split
- `verify-admin-order-screen.sh` green after removing the heavy admin JS
- fresh server `error_log` / `debug.log` contain no new fatal/critical entries for this path

### stop-line

Customer order pages are now treated as a closed block.

Do not reopen them for cleanup unless a new exact reproducible defect appears.
