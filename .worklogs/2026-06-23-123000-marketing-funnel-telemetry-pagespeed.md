# Marketing Links, Funnel Telemetry, And PageSpeed Follow-up

## Context
- Continued the June 23 Gastronom storefront investigation from point 3 onward after checkout shell regressions were fixed in `fa6b9d0e`.
- Goal: verify marketing entry points, add lightweight checkout funnel visibility, and make a low-risk PageSpeed improvement without touching Dotypos or checkout payment/order creation flows.

## Changes
- Added live footer links for the public social profiles:
  - Facebook: `https://www.facebook.com/gastronom.bratislava/`
  - Instagram: `https://www.instagram.com/gastronombratislava/`
- Added lightweight Woo funnel telemetry to `rusky-visit-counter.php`:
  - `product_view`
  - `add_to_cart`
  - `cart_view`
  - `checkout_view`
  - `order_pay_view`
  - `order_received`
  - UTM buckets for source, medium, campaign, and content
- Added `tools/report-funnel-visits.sh` to read the new `rusky_daily_funnel_counts` option from live WordPress.
- Removed the homepage `food-grocery-store-block-style` stylesheet handle from the existing front performance tuning mu-plugin.

## Live Link Checks
- Google Maps shortlink resolved to the Rusky Gastronom Google Maps place and returned `200`.
- Facebook numeric asset URL redirected to `https://www.facebook.com/gastronom.bratislava/` and returned `200`.
- Instagram profile `https://www.instagram.com/gastronombratislava/` returned `200`.
- Meta campaign UTM landing URLs from the schedule docs returned `200`.

## Deployment
- Deployed these files to live Hostinger WordPress:
  - `wp-content/mu-plugins/rusky-visit-counter.php`
  - `wp-content/mu-plugins/rusky-front-performance-tuning.php`
  - `wp-content/themes/food-grocery-store/footer.php`
- Ran remote `php -l` on all three files before replacing live files. All passed.

## Verification
- `./tools/verify-storefront-baseline.sh`: OK.
- `./tools/verify-commerce-shell.sh`: OK.
- `./tools/verify-commerce-shell-sk.sh`: OK.
- Live homepage HTML includes the Facebook and Instagram footer links.
- Live homepage no longer includes the `food-grocery-store-block-style` stylesheet.
- Controlled browser-like funnel probe recorded:
  - `product_view: 1`
  - `add_to_cart: 1`
  - `cart_view: 1`
  - `checkout_view: 1`
  - UTM source `codex`, medium `test`, campaign `funnel_probe`
- No real order was created by the probe.

## PageSpeed After
- Mobile Lighthouse:
  - Performance `83`
  - Accessibility `100`
  - Best Practices `100`
  - SEO `100`
  - FCP `2.0s`, LCP `2.4s`, TBT `510ms`, CLS `0`, Speed Index `2.6s`
- Desktop Lighthouse:
  - Performance `99`
  - Accessibility `100`
  - Best Practices `100`
  - SEO `100`
  - FCP `0.5s`, LCP `0.6s`, TBT `40ms`, CLS `0`, Speed Index `1.2s`

## Notes
- Mobile improved from `81` to `83`; desktop improved from `97` to `99`.
- The larger unused-CSS warning remains because Bootstrap, the theme stylesheet, and WordPress block library are still loaded. They were not removed in this pass because their visual blast radius is higher than the unused block-pattern stylesheet.
