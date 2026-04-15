# 2026-04-15 10:49 storefront language shell cleanup

## Context

After stabilizing the mobile header and shop archive shell, public storefront language still had visible SK mixed-language leftovers:

- shop archive title shell used Russian `Магазин`
- footer contact/legal block still showed Russian address lines on SK pages
- theme chrome still exposed small English strings like `shopping cart` and `Open Button`

## Changes

Added small server-side `mu-plugin` layers instead of touching the dirty language/theme files:

- `wordpress/wp-content/mu-plugins/rusky-shop-display.php`
  - normalize shop archive breadcrumb/document title
  - hide redundant visual shop `h1`
- `wordpress/wp-content/mu-plugins/rusky-footer-language.php`
  - normalize footer contact/legal address lines per current storefront language
- `wordpress/wp-content/mu-plugins/rusky-theme-chrome-language.php`
  - localize small theme chrome strings through a narrow frontend `gettext` map

## Result

- SK `/shop` now uses `Obchod`
- redundant shop heading removed
- SK footer now shows `Zámocká 5, 811 01 Bratislava`
- SK header chrome now shows `košík` and `Otvoriť menu`
- readiness pack remains green

## Verification

- deployed only changed `mu-plugin` files over SSH
- verified live SK shop/home/account in mobile snapshots
- ran `./tools/verify-tuesday-readiness.sh` successfully after the changes
