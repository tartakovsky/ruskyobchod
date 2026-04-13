Preorder order-pay hardening.

Context:
- Guest preorder payment page intermittently showed `503` to the user from the email payment link.
- `view-order` guest UX was also wrong because email pointed to the account endpoint.

Changes:
- `rusky-preorder-storefront.php`
  - on preorder `order-pay` keep only `bacs`
  - remove card/WooPayments payment choices from this path
  - keep preorder-specific bank transfer description
- `rusky-preorder-notifications.php`
  - guest "view order" email button now uses public order URL (`order-received` + key), not `my-account/view-order`
  - payment and view links keep `lang`

Proof after deploy:
- `order-pay/11057?...&lang=ru` returns `200`
- guest email confirmation on `order-pay` succeeds
- resulting payment page shows only `Банковский перевод`
- no card payment block on preorder `order-pay`
- storefront / checkout / preorder baselines remain green

Note:
- a separate accidental deploy mistake briefly copied `gastronom-lang-switcher.php` into `mu-plugins` and triggered a recovery email; the extra file was removed immediately and current live state is healthy.
