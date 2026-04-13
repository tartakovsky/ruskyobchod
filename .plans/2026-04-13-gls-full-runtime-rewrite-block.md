## Goal

Rewrite `gastronom-lang-switcher.php` as one coherent runtime block instead of continuing partial symptom fixes.

The live failures already proved that the current file mixes too many responsibilities:

- request language context
- locale/gettext mutation
- redirect mutation
- frontend HTML normalization
- WooPayments locale mutation
- switcher rendering
- title and brand localization

That monolith is the source of both:

- logged-in frontend `503`
- logged-in/public regressions when one responsibility is disabled together with another

## Current block boundaries

### Inputs

- `?lang=ru|sk`
- `gastronom_lang` cookie
- logged-in state
- operator capabilities:
  - `manage_options`
  - `edit_shop_orders`
- request class:
  - public frontend
  - logged-in frontend
  - `wp-login.php`
  - `wp-admin`
  - AJAX
  - REST / XMLRPC
  - Elementor preview/action
- page type:
  - front page
  - storefront page
  - cart / checkout / account
  - `order-pay`
  - `view-order`
  - `order-received`

### Outputs

- selected frontend language
- RU/SK switcher HTML
- frontend titles and brand strings
- theme/WooCommerce phrase localization
- removal of inactive bilingual blocks
- checkout/cart/account shell normalization
- WooPayments locale mutations
- redirect language propagation

### Neighboring runtimes that can conflict

- `rusky-language-switcher-lite.php`
  - own `template_redirect` + `ob_start` on front page
- `rusky-order-page-language.php`
  - own `template_redirect` + `ob_start` for order pages
- `rusky-runtime-shim.php`
  - own `template_redirect` + `ob_start` for FAR rules on anonymous frontend
- `gastronom-stock-fix.php`
  - order-page redirect/buffer wrappers and checkout/business logic
- `wp-super-cache`
  - output buffering and cache pipeline

## Proven failure mode

The original `GLS` used one broad sensitivity gate for all responsibilities.

That caused a bad tradeoff:

- if logged-in operator frontend is NOT excluded:
  - heavy locale/gettext/redirect/WooPayments runtime can crash to `503`
- if logged-in operator frontend IS excluded too broadly:
  - output buffer and bilingual block stripping stop as well
  - homepage shows duplicated RU/SK block content

## Target architecture

Split the runtime into two explicit boundaries.

### 1. Render boundary

Purpose:

- protect auth/admin/system requests from frontend rendering mutations
- still allow logged-in frontend to be normalized like public frontend

Blocked here:

- `wp-login.php`
- `wp-admin` non-AJAX
- AJAX
- REST
- XMLRPC
- Elementor preview/action

Allowed here:

- anonymous frontend
- logged-in frontend
- storefront
- cart / checkout / account
- order pages where `GLS` still has ownership

Responsibilities using this boundary:

- `render_block` footer normalization
- template output buffer
- inactive bilingual block stripping

### 2. Runtime mutation boundary

Purpose:

- prevent heavy locale/gettext/redirect/payment-script mutation on operator frontend

Blocked here:

- everything blocked by render boundary
- logged-in operator frontend

Responsibilities using this boundary:

- `wp_redirect`
- `locale`
- `determine_locale`
- `gettext`
- `wcpay_locale`
- `script_loader_tag` WooPayments mutation
- footer WooPayments script-extra mutation

## Rules for this rewrite

1. Do not change live until the local rewrite is syntax-clean and documented.
2. Deploy one file only:
   - `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`
3. Verify after deploy:
   - public homepage RU/SK
   - logged-in homepage RU/SK
   - direct frontend from admin session
   - `wp-login.php`
   - `order-pay`
   - storefront/account/preorder baselines
4. If logged-in frontend returns `200` but bilingual blocks remain, rollback and continue locally.
5. Do not touch `lite`, `order-page`, `runtime-shim`, or theme files in the same deploy.

## Expected outcome

- no `503` on logged-in frontend
- no duplicated RU/SK blocks on homepage
- auth/admin/system requests excluded cleanly
- frontend HTML normalization preserved for both anonymous and logged-in frontend
- one explicit, understandable runtime model instead of one overloaded sensitivity flag
