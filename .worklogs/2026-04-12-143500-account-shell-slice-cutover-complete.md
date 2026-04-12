# 2026-04-12 14:35:00 CET

## scope

Close the `account/login/cart labels` slice inside retained late server-rendered shell cleanup in:

- `wordpress/wp-content/plugins/gastronom-lang-switcher/gastronom-lang-switcher.php`

## completed cutovers

Removed late server-rendered shell ownership for these RU labels:

- `Мой аккаунт`
- `Корзина`
- `Вход / Регистрация`
- `Регистрация`
- `Забыли пароль?`
- cookie close `Нет`

## current owner paths

- `gls_translate_account_checkout_phrase()`
- `gls_translate_menu_label()`
- `gls_normalize_account_title_text()`
- `gls_title_parts()`
- `gls_cookie_notice_source_args()`

## verification

Added and ran:

- `tools/verify-account-shell.sh`

All validation contours remained green after each live deploy:

- `tools/verify-storefront-baseline.sh`
- `tools/verify-checkout-shell.sh`
- `tools/verify-account-shell.sh`

## result

The account/login/cart label slice is no longer owned by late shell `strtr()` cleanup.

This sub-zone should not need to be revisited unless a regression appears in one of the verified account/cart shell markers.
