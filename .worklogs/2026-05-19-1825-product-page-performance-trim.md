# Product Page Performance Trim

## Context

After adding the new weighted squid product, live checks showed server response times were healthy, but Lighthouse on the product page was poor because product pages loaded global payment, tracking, font, and oversized logo assets.

## Change

- Extended `rusky-front-performance-tuning.php` beyond the homepage to safe storefront surfaces.
- Reused the existing small `gastronom-logo-home-200.webp` custom-logo derivative on shop/product/search/category pages.
- Removed the large theme Google Fonts URL on storefront performance pages.
- Dequeued WooPayments express checkout, Stripe payment-request, sourcebuster, and Woo order-attribution scripts on product pages only.
- Left cart, checkout, and account pages untouched so payment and checkout behavior remains available where needed.

## Live Verification

- Product page still returns `200`.
- New weighted product still shows `/ kg` and quantity input `min="0.01"`, `step="0.01"`, `max="2"`.
- Checkout still loads Stripe/WooPayments assets and terms text.
- Storefront baseline passed.
- Checkout shell passed after rerun with external network access.
- Live PHP syntax passed.

## Lighthouse Result

Product page desktop:

- before: score `49`, LCP `2.6s`, TBT `710ms`, transfer `4722 KB`
- after: score `91`, LCP `0.9s`, TBT `210ms`, transfer `532 KB`

Product page mobile:

- before: score `44`, LCP `7.0s`, TBT `1210ms`, transfer `3840 KB`
- after: score `75`, LCP `3.3s`, TBT `570ms`, transfer `546 KB`
