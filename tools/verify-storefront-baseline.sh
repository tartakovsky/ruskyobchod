#!/bin/sh
set -eu

BASE_URL="${1:-https://ruskyobchod.sk}"
CATEGORY_PATH="${2:-/kategoria-produktu/sneki-snacky/}"
PRODUCT_PATH="${3:-/produkt/krekry-kirie-ky-syr-unka-suhariki-kirieshki-syr-vetchina/}"

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

check_status() {
    url="$1"
    expected="$2"
    status="$(curl -ksI "$url" | awk 'NR==1 {print $2}')"
    if [ "$status" != "$expected" ]; then
        echo "FAIL status $url expected=$expected got=${status:-none}"
        failures=$((failures + 1))
        return 0
    fi
    echo "OK   status $url -> $status"
}

check_contains() {
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

home_ru="$BASE_URL/?lang=ru&verify_baseline=1"
category_ru="$BASE_URL$CATEGORY_PATH?lang=ru&verify_baseline=1"
product_ru="$BASE_URL$PRODUCT_PATH?lang=ru&verify_baseline=1"
login_url="$BASE_URL/wp-login.php"

check_status "$BASE_URL/" "200"
check_status "$login_url" "200"
check_status "$category_ru" "200"
check_status "$product_ru" "200"

check_contains "$home_ru" 'screen-reader-text skip-link' 'home skip-link marker present'
check_contains "$home_ru" 'href="https://ruskyobchod.sk/">Главная<' 'home RU main menu home present'
check_contains "$home_ru" 'href="https://ruskyobchod.sk/dostavka/">Доставка<' 'home RU main menu delivery present'
check_contains "$home_ru" 'href="https://ruskyobchod.sk/kontakty/">Контакты<' 'home RU main menu contacts present'
check_contains "$home_ru" 'href="https://ruskyobchod.sk/my-account/">Мой аккаунт<' 'home RU main menu account present'
check_contains "$home_ru" 'screen-reader-text">Кнопка Открыть<' 'home RU mobile menu open label present'
check_contains "$home_ru" 'aria-label="Верхнее меню"' 'home RU nav aria label present'
check_contains "$home_ru" 'screen-reader-text">Кнопка Закрыть<' 'home RU mobile menu close label present'
check_contains "$home_ru" 'aria-label="Подвал"' 'home RU footer aria label present'
check_contains "$home_ru" 'screen-reader-text">Прокрутить вверх<' 'home RU scroll up label present'
check_contains "$category_ru" 'По алфавиту' 'category RU sorting label present'
check_contains "$category_ru" 'Добавить в корзину' 'category RU add-to-cart present'
check_contains "$product_ru" 'В наличии' 'product RU stock label present'
check_contains "$product_ru" 'Количество товара' 'product RU quantity label present'
check_contains "$product_ru" 'Артикул:' 'product RU SKU label present'
check_contains "$product_ru" 'Категория:' 'product RU category label present'
check_contains "$product_ru" 'Похожие товары' 'product RU related products heading present'
check_contains "$home_ru" 'id="cn-accept-cookie"[^>]*>Ок<' 'home RU cookie accept button present'
check_contains "$home_ru" 'id="cn-close-notice"[^>]*aria-label="Нет"' 'home RU cookie close aria present'
check_contains "$home_ru" 'Словацкая Республика' 'home RU legal country present'
check_contains "$home_ru" 'Зарегистрирована в торговом реестре окружного суда Братислава( | )I,' 'home RU legal registry line present'
check_contains "$home_ru" 'Раздел s\.r\.o\., № записи 182562/B' 'home RU legal company line present'

if [ "$failures" -gt 0 ]; then
    echo "Baseline verification complete with failures: $failures"
    exit 1
fi

echo "Baseline verification complete."
