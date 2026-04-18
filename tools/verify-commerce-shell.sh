#!/bin/sh
set -eu

BASE_URL="${1:-https://ruskyobchod.sk}"
PRODUCT_PATH="${2:-/produkt/krekry-kirie-ky-syr-unka-suhariki-kirieshki-syr-vetchina/}"
PRODUCT_ID="${3:-10795}"

need_cmd() {
    command -v "$1" >/dev/null 2>&1 || {
        echo "Missing required command: $1" >&2
        exit 1
    }
}

need_cmd curl
need_cmd rg
need_cmd python3
need_cmd mktemp

failures=0
cookie_jar="$(mktemp)"
trap 'rm -f "$cookie_jar"' EXIT

seed_cart() {
    curl -ks -c "$cookie_jar" -b "$cookie_jar" \
        "$BASE_URL$PRODUCT_PATH?lang=ru&add-to-cart=$PRODUCT_ID" \
        -o /dev/null
}

check_status() {
    url="$1"
    expected="$2"
    label="$3"
    status="$(curl -ksI -c "$cookie_jar" -b "$cookie_jar" "$url" | awk 'NR==1 {print $2}')"
    if [ "$status" = "$expected" ]; then
        echo "OK   $label"
    else
        echo "FAIL $label expected=$expected got=${status:-none}"
        failures=$((failures + 1))
    fi
}

check_contains() {
    url="$1"
    pattern="$2"
    label="$3"
    if curl -ks --compressed -c "$cookie_jar" -b "$cookie_jar" "$url" | python3 -c 'import html, sys; sys.stdout.write(html.unescape(sys.stdin.read()))' | rg -q "$pattern"; then
        echo "OK   $label"
    else
        echo "FAIL $label"
        failures=$((failures + 1))
    fi
}

seed_cart

cart_ru="$BASE_URL/cart/?lang=ru&verify_commerce_shell=1"
checkout_ru="$BASE_URL/checkout/?lang=ru&verify_commerce_shell=1"

check_status "$cart_ru" "200" 'commerce cart RU status 200'
check_status "$checkout_ru" "200" 'commerce checkout RU status 200'

check_contains "$cart_ru" 'Сумма корзины' 'commerce cart totals heading present'
check_contains "$cart_ru" '<h1 class="vw-page-title">Корзина</h1>' 'commerce cart page title present'
check_contains "$cart_ru" 'Подытог' 'commerce cart subtotal present'
check_contains "$cart_ru" 'Самовывоз' 'commerce cart local pickup present'
check_contains "$cart_ru" 'GLS доставка на адрес' 'commerce cart GLS shipping present'
check_contains "$cart_ru" 'SK Packeta пункт выдачи \(Z-Point, Z-Box\)' 'commerce cart Packeta shipping present'
check_contains "$cart_ru" 'Итого' 'commerce cart total present'
check_contains "$cart_ru" 'Оформить заказ' 'commerce cart place order link present'

check_contains "$checkout_ru" '<h1 class="vw-page-title">Оформление заказа</h1>' 'commerce checkout page title present'
check_contains "$checkout_ru" 'Ваш заказ' 'commerce checkout order heading present'
check_contains "$checkout_ru" 'Имя' 'commerce checkout first name present'
check_contains "$checkout_ru" 'Фамилия' 'commerce checkout last name present'
check_contains "$checkout_ru" 'Страна/регион' 'commerce checkout country label present'
check_contains "$checkout_ru" 'Почтовый индекс' 'commerce checkout postcode label present'
check_contains "$checkout_ru" 'Населённый пункт' 'commerce checkout city label present'
check_contains "$checkout_ru" 'Доставка по другому адресу\?' 'commerce checkout ship to different address present'
check_contains "$checkout_ru" 'Примечание к заказу' 'commerce checkout order note present'
check_contains "$checkout_ru" 'Самовывоз' 'commerce checkout local pickup present'
check_contains "$checkout_ru" 'GLS доставка на адрес' 'commerce checkout GLS shipping present'
check_contains "$checkout_ru" 'SK Packeta пункт выдачи \(Z-Point, Z-Box\)' 'commerce checkout Packeta shipping present'
check_contains "$checkout_ru" 'Оплата при получении' 'commerce checkout COD present'
check_contains "$checkout_ru" 'К заказу будет добавлена доплата за наложенный платёж 2,00 €\.' 'commerce checkout COD description present'
check_contains "$checkout_ru" 'Оплата картой' 'commerce checkout card payment present'
check_contains "$checkout_ru" 'Подытог' 'commerce checkout subtotal present'
check_contains "$checkout_ru" 'Итого' 'commerce checkout total present'
check_contains "$checkout_ru" 'правила и условия' 'commerce checkout terms label present'

if [ "$failures" -gt 0 ]; then
    echo "Commerce shell verification complete with failures: $failures"
    exit 1
fi

echo "Commerce shell verification complete."
