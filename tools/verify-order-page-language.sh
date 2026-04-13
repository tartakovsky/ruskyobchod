#!/bin/sh
set -eu

REMOTE_HOST="${REMOTE_HOST:-u595644545@46.202.156.109}"
REMOTE_PORT="${REMOTE_PORT:-65002}"
REMOTE_ROOT="${REMOTE_ROOT:-/home/u595644545/domains/ruskyobchod.sk/public_html}"

PRODUCT_ID="${1:-10795}"

tmp_local="$(mktemp)"
tmp_remote="/tmp/rusky-order-page-language-verify-$$.php"
trap 'rm -f "$tmp_local"' EXIT

cat >"$tmp_local" <<'PHP'
<?php
$_SERVER['HTTP_HOST'] = 'ruskyobchod.sk';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_NAME'] = 'ruskyobchod.sk';
$_SERVER['SERVER_PORT'] = '443';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_URI'] = '/?verify_order_page_language=1';

require $argv[1] . '/wp-load.php';

$product_id = isset($argv[2]) ? (int) $argv[2] : 10795;
$product = wc_get_product($product_id);
if (!$product instanceof WC_Product) {
    fwrite(STDERR, "FAIL product missing\n");
    exit(1);
}

$created_orders = [];
register_shutdown_function(static function() use (&$created_orders) {
    foreach ($created_orders as $order_id) {
        $order_id = (int) $order_id;
        if ($order_id > 0 && wc_get_order($order_id)) {
            wp_delete_post($order_id, true);
        }
    }
});

function rusky_verify_order_page_contains(string $body, array $checks): void {
    foreach ($checks as $label => $needle) {
        if (strpos($body, $needle) === false) {
            fwrite(STDERR, "FAIL $label\n");
            exit(1);
        }
        echo "OK   $label\n";
    }
}

function rusky_verify_order_page_language(string $lang, WC_Product $product, array &$created_orders): void {
    $order = wc_create_order();
    $created_orders[] = $order->get_id();
    $order->set_payment_method('bacs');
    $order->set_payment_method_title($lang === 'ru' ? 'Банковский перевод' : 'Bankový prevod');
    $order->set_billing_email('proof-order-page@example.com');
    $order->set_currency(get_woocommerce_currency());
    $order->add_product($product, 1);
    $order->update_meta_data('_gastronom_lang', $lang);
    $order->calculate_totals(false);
    $order->save();

    $order_id = $order->get_id();
    $order_key = $order->get_order_key();

    $received_url = home_url('/checkout/order-received/' . $order_id . '/?key=' . $order_key . '&lang=' . $lang);
    $pay_url = home_url('/checkout/order-pay/' . $order_id . '/?pay_for_order=true&key=' . $order_key . '&lang=' . $lang);

    $received = wp_remote_get($received_url, ['timeout' => 30, 'sslverify' => false, 'user-agent' => 'rusky-order-page-verify/1.0']);
    if (is_wp_error($received)) {
        fwrite(STDERR, "FAIL {$lang} received request\n");
        exit(1);
    }
    $received_body = html_entity_decode(wp_strip_all_tags(wp_remote_retrieve_body($received)), ENT_QUOTES | ENT_HTML5, 'UTF-8');

    $pay = wp_remote_get($pay_url, ['timeout' => 30, 'sslverify' => false, 'user-agent' => 'rusky-order-page-verify/1.0']);
    if (is_wp_error($pay)) {
        fwrite(STDERR, "FAIL {$lang} pay request\n");
        exit(1);
    }
    $pay_body = html_entity_decode(wp_strip_all_tags(wp_remote_retrieve_body($pay)), ENT_QUOTES | ENT_HTML5, 'UTF-8');

    if ($lang === 'ru') {
        rusky_verify_order_page_contains($received_body, [
            'RU received title' => 'Заказ получен',
            'RU received notice' => 'Ваш заказ принят. Благодарим вас.',
            'RU received order number' => 'Номер заказа:',
            'RU received payment label' => 'Способ оплаты:',
            'RU received bank transfer' => 'Банковский перевод',
            'RU received subtotal' => 'Подытог',
            'RU received total' => 'Итого',
        ]);
        rusky_verify_order_page_contains($pay_body, [
            'RU pay title' => 'Оплатить заказ',
            'RU pay payment label' => 'Способ оплаты',
            'RU pay bank transfer' => 'Банковский перевод',
            'RU pay subtotal' => 'Подытог',
            'RU pay total' => 'Итого',
        ]);
        return;
    }

    rusky_verify_order_page_contains($received_body, [
        'SK received title' => 'Objednávka prijatá',
        'SK received notice' => 'Ďakujeme. Vaša objednávka bola prijatá.',
        'SK received order number' => 'Číslo objednávky:',
        'SK received payment label' => 'Spôsob platby:',
        'SK received bank transfer' => 'Bankový prevod',
        'SK received subtotal' => 'Medzisúčet',
    ]);
    rusky_verify_order_page_contains($pay_body, [
        'SK pay title' => 'Zaplatiť objednávku',
        'SK pay payment label' => 'Spôsob platby:',
        'SK pay bank transfer' => 'Bankový prevod',
        'SK pay subtotal' => 'Medzisúčet',
        'SK pay total column' => 'Spolu',
    ]);
}

rusky_verify_order_page_language('ru', $product, $created_orders);
rusky_verify_order_page_language('sk', $product, $created_orders);

echo "Order page language verification complete.\n";
PHP

scp -P "$REMOTE_PORT" "$tmp_local" "$REMOTE_HOST:$tmp_remote" >/dev/null
ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "php '$tmp_remote' '$REMOTE_ROOT' '$PRODUCT_ID'; rm -f '$tmp_remote'"
