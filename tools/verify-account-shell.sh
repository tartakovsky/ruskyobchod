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

account_ru="$BASE_URL/my-account/?lang=ru&verify_account_shell=1"

check_header_contains "$account_ru" '^HTTP/.* 200' 'account RU status 200'
check_body_contains "$account_ru" '<title>Мой аккаунт — Гастроном</title>' 'account RU document title present'
check_body_contains "$account_ru" 'href="https://ruskyobchod.sk/my-account/" title="Вход / Регистрация"' 'account RU header icon title present'
check_body_contains "$account_ru" 'href="https://ruskyobchod.sk/">Главная<' 'account RU main menu home present'
check_body_contains "$account_ru" 'href="https://ruskyobchod.sk/my-account/">Мой аккаунт<' 'account RU main menu account present'
check_body_contains "$account_ru" '<h1 class="vw-page-title">Мой аккаунт</h1>' 'account RU page title present'
check_body_contains "$account_ru" 'Имя пользователя или Email' 'account RU username label present'
check_body_contains "$account_ru" '>Пароль' 'account RU password label present'
check_body_contains "$account_ru" 'Запомнить меня' 'account RU remember me present'
check_body_contains "$account_ru" 'name="login" value="Войти">Войти<' 'account RU login button present'
check_body_contains "$account_ru" 'Забыли пароль\?' 'account RU lost password present'
check_body_contains "$account_ru" '<h2>Регистрация</h2>' 'account RU register heading present'
check_body_contains "$account_ru" 'name="register" value="Регистрация">Регистрация<' 'account RU register button present'
check_body_contains "$account_ru" 'id="cn-accept-cookie"[^>]*>Ок<' 'account RU cookie accept button present'
check_body_contains "$account_ru" 'id="cn-close-notice"[^>]*aria-label="Нет"' 'account RU cookie close aria present'

if [ "$failures" -gt 0 ]; then
    echo "Account shell verification complete with failures: $failures"
    exit 1
fi

echo "Account shell verification complete."
