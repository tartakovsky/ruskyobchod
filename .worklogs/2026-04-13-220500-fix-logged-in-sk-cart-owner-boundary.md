# Fix Logged-In SK Cart Owner Boundary

- scope: owner-layer fix for mixed-language logged-in SK cart and preorder item weight unit
- files:
  - `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`
  - `wordpress/wp-content/mu-plugins/rusky-preorder-storefront.php`
  - `wordpress/wp-content/plugins/gastronom-stock-fix/gastronom-stock-fix.php`
- change:
  - keep heavy runtime mutation blocked for logged-in operator frontend
  - allow safe translation layer (`locale` / `gettext`) on logged-in storefront pages
  - normalize preorder weight unit display to `kg` in both owner and fallback display paths
- expected outcome:
  - logged-in `cart/?lang=sk` keeps storefront stable and stops mixing Russian chrome with Slovak preorder item text
  - weighted products share one display logic across `Ryba Makrela Údeného Chladenia`, `Sleď . Vedro`, and `Slanina "Mercur"`
