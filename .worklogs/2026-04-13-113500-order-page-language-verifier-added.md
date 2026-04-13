## order page language verifier added

### scope

Add a cleanup-safe verifier for customer-visible WooCommerce order pages in both supported languages.

File added:

- `tools/verify-order-page-language.sh`

### what it verifies

The verifier creates temporary orders with guaranteed cleanup and checks:

- RU `order-received`
- RU `order-pay`
- SK `order-received`
- SK `order-pay`

### markers covered

RU:

- `Заказ получен`
- `Ваш заказ принят. Благодарим вас.`
- `Номер заказа:`
- `Способ оплаты:`
- `Банковский перевод`
- `Подытог`
- `Итого`
- `Оплатить заказ`

SK:

- `Objednávka prijatá`
- `Ďakujeme. Vaša objednávka bola prijatá.`
- `Číslo objednávky:`
- `Spôsob platby:`
- `Bankový prevod`
- `Medzisúčet`
- `Zaplatiť objednávku`
- `Spolu`

### cleanup proof

After running the verifier:

- no `proof-order-page@example.com` orders remained on live

### verification after proof

All six existing baselines remained green:

- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
- `tools/verify-account-shell.sh`
- `tools/verify-commerce-shell.sh`
- `tools/verify-commerce-shell-sk.sh`
- `tools/verify-preorder-shell.sh`

### result

Customer-visible order language is no longer an unproven side path.

It is now part of the controlled Tuesday-readiness proof surface.
