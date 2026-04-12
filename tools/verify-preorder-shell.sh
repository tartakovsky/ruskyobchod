#!/usr/bin/env python3
import html
import ssl
import sys
import urllib.request


PRODUCTS = [
    (
        "slanina-mercur-salo-mercur",
        "Предзаказ по весу",
        "Примерный вес одной штуки",
        "Predobjednávka podľa hmotnosti",
        "Približná hmotnosť jedného kusa",
    ),
    (
        "sle-vedro-seld-vedro",
        "Предзаказ по весу",
        "Примерный вес одной штуки",
        "Predobjednávka podľa hmotnosti",
        "Približná hmotnosť jedného kusa",
    ),
    (
        "ryba-makrela-den-ho-chladenia-ryba-skumbriya-holodnogo-kopcheniya",
        "Предзаказ по весу",
        "Примерный вес одной штуки",
        "Predobjednávka podľa hmotnosti",
        "Približná hmotnosť jedného kusa",
    ),
]


def fetch(url: str):
    req = urllib.request.Request(url, headers={"User-Agent": "rusky-preorder-baseline/1.0"})
    context = ssl.create_default_context()
    context.check_hostname = False
    context.verify_mode = ssl.CERT_NONE
    with urllib.request.urlopen(req, timeout=30, context=context) as resp:
        body = resp.read().decode("utf-8", errors="replace")
        return resp.getcode(), html.unescape(body)


def ok(message: str):
    print(f"OK   {message}")


def fail(message: str):
    print(f"FAIL {message}")
    sys.exit(1)


for slug, ru_title, ru_body, sk_title, sk_body in PRODUCTS:
    for lang, title, body in (("ru", ru_title, ru_body), ("sk", sk_title, sk_body)):
        url = f"https://ruskyobchod.sk/produkt/{slug}/?lang={lang}"
        status, text = fetch(url)
        if status != 200:
            fail(f"preorder {lang.upper()} status {url} -> {status}")
        ok(f"preorder {lang.upper()} status {url} -> {status}")

        if title not in text:
            fail(f"preorder {lang.upper()} note title present for {slug}")
        ok(f"preorder {lang.upper()} note title present for {slug}")

        if body not in text:
            fail(f"preorder {lang.upper()} note body present for {slug}")
        ok(f"preorder {lang.upper()} note body present for {slug}")

        if "/ kg" not in text:
            fail(f"preorder {lang.upper()} price unit present for {slug}")
        ok(f"preorder {lang.upper()} price unit present for {slug}")

print("Preorder shell verification complete.")
