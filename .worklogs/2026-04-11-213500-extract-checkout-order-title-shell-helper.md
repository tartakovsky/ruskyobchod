## Summary

Extracted checkout/order title-shell normalization into:

- `gls_normalize_checkout_order_title_shell_html()`

## Scope

Moved the localized handling for:

- breadcrumb `Objednávka` / `Оформление заказа`
- page title `vw-page-title`
- one retained checkout title shell replacement near the brand link

## Behavior goal

No behavior change.

The same replacements stay active inside `gls_normalize_server_rendered_html()`, now via one helper.

## Verification plan

- `git diff --check`
- `tools/verify-storefront-baseline.sh`
- one-file deploy
- `tools/verify-storefront-baseline.sh` after deploy
