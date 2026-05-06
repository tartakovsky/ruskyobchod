# Meta marketing start

Date: 2026-05-06

Goal:
- Start low-effort Facebook/Instagram marketing for Gastronom.
- Prepare Meta Pixel / Dataset setup for ecommerce tracking.

## Current state

- Facebook page and Instagram are connected in Meta Business Suite, confirmed by operator.
- Meta Events Manager dataset created:
  - `Gastronom Website`
  - Dataset / Pixel ID: `988003717119707`
- Existing Meta catalog found during setup:
  - `Gastronom Catalogue`
  - Catalog ID: `4415105648766131`
- Existing Meta ad account seen in Events Manager:
  - `Gastronom Ad Account`
  - Ad account ID: `1880568885862975`
- Official `Facebook for WooCommerce` plugin was installed and activated on the live site.
- The official WooCommerce OAuth flow currently blocks on `Select your ad account`: the OAuth dialog shows no selectable ad account even though Events Manager lists one.
- A source-controlled MU plugin now sends base Meta Pixel events while the official OAuth/catalog connection is unresolved:
  - `PageView`
  - `ViewContent`
  - `AddToCart`
  - `InitiateCheckout`
  - `Purchase`

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
- Verify `PageView`, `ViewContent`, and `AddToCart` in Meta Events Manager.
- Resolve the official WooCommerce OAuth ad-account selection issue before relying on catalog sync / Advantage+ catalog ads.
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
