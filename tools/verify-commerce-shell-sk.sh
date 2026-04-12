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
        "$BASE_URL$PRODUCT_PATH?lang=sk&add-to-cart=$PRODUCT_ID" \
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

cart_sk="$BASE_URL/cart/?lang=sk&verify_commerce_shell=1"
checkout_sk="$BASE_URL/checkout/?lang=sk&verify_commerce_shell=1"

check_status "$cart_sk" "200" 'commerce cart SK status 200'
check_status "$checkout_sk" "200" 'commerce checkout SK status 200'

check_contains "$cart_sk" 'Košík' 'commerce cart SK title present'
check_contains "$cart_sk" 'Medzisúčet' 'commerce cart SK subtotal present'
check_contains "$cart_sk" 'Osobne vyzdvihnutie' 'commerce cart SK local pickup present'
check_contains "$cart_sk" 'GLS doručenie na adresu' 'commerce cart SK GLS shipping present'
check_contains "$cart_sk" 'SK Packeta Pick-up Point \(Z-Point, Z-Box\)' 'commerce cart SK Packeta shipping present'
check_contains "$checkout_sk" '<h1 class="vw-page-title">Objednávka</h1>' 'commerce checkout SK page title present'
check_contains "$checkout_sk" 'Krstné meno' 'commerce checkout SK first name present'
check_contains "$checkout_sk" 'Priezvisko' 'commerce checkout SK last name present'
check_contains "$checkout_sk" 'Krajina / región' 'commerce checkout SK country label present'
check_contains "$checkout_sk" 'PSČ' 'commerce checkout SK postcode label present'
check_contains "$checkout_sk" 'Mesto' 'commerce checkout SK city label present'
check_contains "$checkout_sk" 'Odoslať na inú adresu\?' 'commerce checkout SK ship to different address present'
check_contains "$checkout_sk" 'Poznámky k objednávke' 'commerce checkout SK order note present'
check_contains "$checkout_sk" 'Osobne vyzdvihnutie' 'commerce checkout SK local pickup present'
check_contains "$checkout_sk" 'GLS doručenie na adresu' 'commerce checkout SK GLS shipping present'
check_contains "$checkout_sk" 'SK Packeta Pick-up Point \(Z-Point, Z-Box\)' 'commerce checkout SK Packeta shipping present'
check_contains "$checkout_sk" 'Platba pri doručení' 'commerce checkout SK COD present'
check_contains "$checkout_sk" 'K objednávke bude pripočítaný poplatok za dobierku vo výške 2,00 €\.' 'commerce checkout SK COD description present'
check_contains "$checkout_sk" 'Bankový prevod' 'commerce checkout SK bank transfer present'
check_contains "$checkout_sk" 'Zaplaťte priamym prevodom na náš bankový účet\. Objednávka bude spracovaná po prijatí platby\.' 'commerce checkout SK bank transfer description present'
check_contains "$checkout_sk" 'Medzisúčet' 'commerce checkout SK subtotal present'

if [ "$failures" -gt 0 ]; then
    echo "Commerce shell SK verification complete with failures: $failures"
    exit 1
fi

echo "Commerce shell SK verification complete."
