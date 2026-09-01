#!/bin/sh
set -eu

REMOTE_HOST="${REMOTE_HOST:-u595644545@46.202.156.109}"
REMOTE_PORT="${REMOTE_PORT:-65002}"
PRODUCTION_ROOT="${PRODUCTION_ROOT:-/home/u595644545/domains/ruskyobchod.sk/public_html}"
STAGING_ROOT="${STAGING_ROOT:-$PRODUCTION_ROOT/staging-gastronom}"
EXPECTED_VERSION="${EXPECTED_VERSION:-6.9.7}"

ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "set -eu
cd '$PRODUCTION_ROOT'
test \"\$(wp core version --skip-plugins --skip-themes)\" = '$EXPECTED_VERSION'
wp core verify-checksums --version='$EXPECTED_VERSION' --locale=ru_RU --skip-plugins --skip-themes
echo 'OK   production WordPress core version and ru_RU checksums match'

cd '$STAGING_ROOT'
test \"\$(wp core version --skip-plugins --skip-themes)\" = '$EXPECTED_VERSION'
wp core verify-checksums --version='$EXPECTED_VERSION' --locale=en_US --skip-plugins --skip-themes
echo 'OK   staging WordPress core version and en_US checksums match'"

echo 'WordPress core integrity verification complete.'
