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

fetch_decoded() {
    curl -ks --compressed "$1" | python3 -c 'import html, sys; sys.stdout.write(html.unescape(sys.stdin.read()))'
}

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
    content="$1"
    pattern="$2"
    label="$3"
    if printf '%s' "$content" | rg -q "$pattern"; then
        echo "OK   $label"
    else
        echo "FAIL $label"
        failures=$((failures + 1))
    fi
}

check_not_contains() {
    content="$1"
    pattern="$2"
    label="$3"
    if printf '%s' "$content" | rg -q "$pattern"; then
        echo "FAIL $label"
        failures=$((failures + 1))
    else
        echo "OK   $label"
    fi
}

home_ru="$BASE_URL/?lang=ru&runtime_surface=1"
account_ru="$BASE_URL/my-account/?lang=ru&runtime_surface=1"
cart_ru="$BASE_URL/cart/?lang=ru&runtime_surface=1"

check_status "$home_ru" "200"
check_status "$account_ru" "200"
check_status "$cart_ru" "200"

home_html="$(fetch_decoded "$home_ru")"
account_html="$(fetch_decoded "$account_ru")"
cart_html="$(fetch_decoded "$cart_ru")"

for content_name in home_html account_html cart_html; do
    eval "content=\${$content_name}"

    check_not_contains "$content" 'gls-script\.js' "$content_name has no legacy gls-script asset"
    check_not_contains "$content" 'translateAll\(' "$content_name has no legacy translateAll runtime"
    check_not_contains "$content" 'localStorage\.getItem\([\"'\'']gls-lang[\"'\'']\)' "$content_name has no legacy localStorage language runtime"
    check_not_contains "$content" 'new MutationObserver' "$content_name has no legacy mutation observer runtime"
done

check_contains "$home_html" 'id="gls-switcher"' 'home has server-rendered switcher container'
check_contains "$home_html" 'href="https://ruskyobchod\.sk/\?lang=ru&runtime_surface=1"' 'home RU switcher link is server-rendered'
check_contains "$home_html" 'href="https://ruskyobchod\.sk/\?lang=sk&runtime_surface=1"' 'home SK switcher link is server-rendered'

check_contains "$account_html" 'Мой аккаунт' 'account RU title is server-rendered'
check_contains "$cart_html" 'Ваша корзина пока пуста\.' 'cart RU empty shell is server-rendered'

if [ "$failures" -gt 0 ]; then
    echo "Language runtime surface verification complete with failures: $failures"
    exit 1
fi

echo "Language runtime surface verification complete."
