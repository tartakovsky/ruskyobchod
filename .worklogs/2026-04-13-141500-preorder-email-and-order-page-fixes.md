Preorder confirmation and order-page fixes.

Changes:
- `rusky-preorder-admin.php`: replaced per-item confirmation buttons with a single order-level confirmation action.
- `rusky-preorder-notifications.php`: added idempotency guards for weight-confirmation processing and email sending; appended `lang` to order view/payment links in the confirmation email.
- `rusky-order-page-language.php`: disabled forced `lang` redirect on `view-order` and `order-pay` paths to prevent redirect loops.

Live proof after deploy:
- `view-order/11051?lang=sk` -> `200`
- `view-order/11051?lang=ru` -> `200`
- `order-pay/11051?...` -> `200`
- preorder-only confirmation proof on 3 weighted items sent exactly 1 confirmation email for the order
- generated email links now include `?lang=ru`

Verification:
- `verify-preorder-shell.sh`
- `verify-commerce-shell.sh`
- `verify-checkout-shell.sh`
- `verify-live-php-syntax.sh`
