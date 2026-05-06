# Meta marketing start

Date: 2026-05-06

Goal:
- Start low-effort Facebook/Instagram marketing for Gastronom.
- Prepare Meta Pixel / Dataset setup for ecommerce tracking.

## Current state

- Facebook page and Instagram are connected in Meta Business Suite, confirmed by operator.
- Public site HTML does not currently include Meta Pixel signals:
  - no `fbq`
  - no `fbevents.js`
  - no `connect.facebook.net`
- Installed plugin directories do not include the official `facebook-for-woocommerce` plugin.

## Priority 1: Facebook and Instagram posting

Publish 1 post per week through Meta Business Suite, cross-posted to Facebook and Instagram.

Use this URL with tracking:

```txt
https://ruskyobchod.sk/?utm_source=meta&utm_medium=social&utm_campaign=online_start
```

Use this coupon in all first-month Meta posts:

```txt
PALISADY5
```

Suggested post 1:

```txt
Gastronom v Bratislave je teraz aj online.

Objednajte si oblubene potraviny z nasho obchodu cez web:
https://ruskyobchod.sk/?utm_source=meta&utm_medium=social&utm_campaign=online_start

Mozny je osobny odber aj dorucenie po Slovensku.
Kod pre prvy online nakup: PALISADY5
```

Suggested post 2:

```txt
Novy sposob nakupu v Gastronom.

Vyberte si produkty online, objednajte z domu a vyzdvihnite v predajni na Zamockej 5 alebo si nechajte poslat objednavku kurierom.

https://ruskyobchod.sk/?utm_source=meta&utm_medium=social&utm_campaign=pickup_delivery
Kod: PALISADY5
```

Suggested post 3:

```txt
Dorucenie potravin po Slovensku.

V eshope Gastronom najdete produkty, ktore poznate z nasej predajne v Bratislave.
Objednavka online, osobny odber alebo dorucenie.

https://ruskyobchod.sk/?utm_source=meta&utm_medium=social&utm_campaign=delivery_sk
```

Suggested RU post:

```txt
Gastronom в Братиславе теперь и онлайн.

Можно заказать продукты на сайте, выбрать самовывоз или доставку по Словакии.

https://ruskyobchod.sk/?utm_source=meta&utm_medium=social&utm_campaign=ru_online_start
Код для первого онлайн-заказа: PALISADY5
```

## Priority 2: Meta Pixel / Dataset

Preferred implementation:
- Official `Facebook for WooCommerce` plugin.
- Connect it to the Meta business, page, ad account, catalog, and dataset.
- Track ecommerce events:
  - `PageView`
  - `ViewContent`
  - `AddToCart`
  - `InitiateCheckout`
  - `Purchase`

Operator steps in Meta:
1. Open Meta Business Suite.
2. Go to `All tools` -> `Events Manager`.
3. Check whether a Dataset / Pixel already exists for `ruskyobchod.sk` or Gastronom.
4. If it exists, copy the Dataset ID / Pixel ID.
5. If it does not exist, create a new Dataset for the website.
6. Use website URL:

```txt
https://ruskyobchod.sk/
```

Technical next step:
- After Pixel/Dataset ID is known, connect through the official WooCommerce integration or install/configure the official plugin.
- Do not run conversion/sales campaigns before `Purchase` and `AddToCart` events are visible in Events Manager.

## First paid test, after Pixel works

Budget:
- 3-5 EUR/day
- 7 days

Audience:
- Bratislava + 25 km
- Languages: Slovak, Czech, Russian, Ukrainian
- Keep targeting broad initially; do not over-narrow.

Creative:
- Real store/photo/product shelves, not generic graphics.
- One clear message: online ordering + pickup/delivery.
- Coupon: `PALISADY5`.

Success metric:
- at least add-to-cart and checkout events visible
- coupon use
- actual orders, not only clicks
