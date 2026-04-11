#!/bin/sh
set -eu

BASE_URL="${1:-https://ruskyobchod.sk}"

need_cmd() {
    command -v "$1" >/dev/null 2>&1 || {
        echo "Missing required command: $1" >&2
        exit 1
    }
}

need_cmd curl
need_cmd rg
need_cmd python3

failures=0

check_header_contains() {
    url="$1"
    pattern="$2"
    label="$3"
    if curl -ksI "$url" | rg -q "$pattern"; then
        echo "OK   $label"
    else
        echo "FAIL $label"
        failures=$((failures + 1))
    fi
}

check_body_contains() {
    url="$1"
    pattern="$2"
    label="$3"
    if curl -ks --compressed "$url" | python3 -c 'import html, sys; sys.stdout.write(html.unescape(sys.stdin.read()))' | rg -q "$pattern"; then
        echo "OK   $label"
    else
        echo "FAIL $label"
        failures=$((failures + 1))
    fi
}

cart_ru="$BASE_URL/cart/?lang=ru&verify_checkout_shell=1"
checkout_ru="$BASE_URL/checkout/?lang=ru&verify_checkout_shell=1"

check_header_contains "$cart_ru" '^HTTP/.* 200' 'cart RU status 200'
check_header_contains "$checkout_ru" '^HTTP/.* 302' 'checkout RU redirects'
check_header_contains "$checkout_ru" '^location: https://ruskyobchod\.sk/cart/\?lang=ru' 'checkout RU redirects to cart with lang'

check_body_contains "$cart_ru" 'Корзина' 'cart RU title present'
check_body_contains "$cart_ru" 'Ваша корзина пока пуста' 'cart RU empty message present'
check_body_contains "$cart_ru" 'href="https://ruskyobchod.sk/">Главная<' 'cart RU main menu home present'
check_body_contains "$cart_ru" 'href="https://ruskyobchod.sk/my-account/">Мой аккаунт<' 'cart RU main menu account present'
check_body_contains "$cart_ru" 'id="cn-accept-cookie"[^>]*>Ок<' 'cart RU cookie accept button present'
check_body_contains "$cart_ru" 'id="cn-close-notice"[^>]*aria-label="Нет"' 'cart RU cookie close aria present'

if [ "$failures" -gt 0 ]; then
    echo "Checkout shell verification complete with failures: $failures"
    exit 1
fi

echo "Checkout shell verification complete."
