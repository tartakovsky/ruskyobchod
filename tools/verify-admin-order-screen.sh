#!/bin/sh
set -eu

REMOTE_HOST="${REMOTE_HOST:-u595644545@46.202.156.109}"
REMOTE_PORT="${REMOTE_PORT:-65002}"
REMOTE_ROOT="${REMOTE_ROOT:-/home/u595644545/domains/ruskyobchod.sk/public_html}"

PRODUCT_ID="${1:-10617}"

tmp_local="$(mktemp)"
tmp_remote="/tmp/rusky-admin-order-screen-verify-$$.php"
trap 'rm -f "$tmp_local"' EXIT

cat >"$tmp_local" <<'PHP'
<?php
$_SERVER['HTTP_HOST'] = 'ruskyobchod.sk';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_NAME'] = 'ruskyobchod.sk';
$_SERVER['SERVER_PORT'] = '443';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_URI'] = '/wp-admin/admin.php?page=wc-orders';

require $argv[1] . '/wp-load.php';

$product_id = isset($argv[2]) ? (int) $argv[2] : 10617;
$product = wc_get_product($product_id);
if (!$product instanceof WC_Product) {
    fwrite(STDERR, "FAIL product missing\n");
    exit(1);
}

$admins = get_users([
    'role' => 'administrator',
    'number' => 1,
    'fields' => ['ID'],
]);
if (!$admins) {
    fwrite(STDERR, "FAIL no administrator user\n");
    exit(1);
}
wp_set_current_user((int) $admins[0]->ID);

$created_order_id = 0;
register_shutdown_function(static function() use (&$created_order_id) {
    if ($created_order_id > 0 && wc_get_order($created_order_id)) {
        wp_delete_post($created_order_id, true);
    }
});

$order = wc_create_order();
$created_order_id = $order->get_id();
$order->set_payment_method('cod');
$order->set_payment_method_title('Оплата при получении');
$order->set_billing_email('proof-admin-order@example.com');
$order->set_currency(get_woocommerce_currency());
$item_id = $order->add_product($product, 1);
$item = $order->get_item($item_id);
if (!$item instanceof WC_Order_Item_Product) {
    fwrite(STDERR, "FAIL order item missing\n");
    exit(1);
}

$price_per_kg = function_exists('gastronom_weight_preorder_price_per_kg')
    ? (float) gastronom_weight_preorder_price_per_kg($product_id)
    : (float) $product->get_price();
if ($price_per_kg <= 0) {
    $price_per_kg = 1.0;
}

$item->update_meta_data('_gastronom_weight_preorder', 'yes');
$item->update_meta_data('_gastronom_price_per_kg', $price_per_kg);
$item->update_meta_data('_gastronom_weight_min_kg', 0.30);
$item->update_meta_data('_gastronom_weight_max_kg', 0.60);
$item->update_meta_data('_gastronom_weight_confirmed', 'no');
$item->update_meta_data('_gastronom_weight_cash_synced', 'no');
$item->save();

$order->update_meta_data('_gastronom_requires_weight_confirmation', 'yes');
$order->calculate_totals(false);
$order->save();
$order->update_status('await-weight', 'Proof admin order screen.', true);

ob_start();
rpa_render_inline_weight_panel($order);
$panel_html = (string) ob_get_clean();

ob_start();
rpa_render_order_admin_footer();
$footer_html = (string) ob_get_clean();

$hidden_ids = function_exists('rpa_hidden_meta_box_ids') ? rpa_hidden_meta_box_ids() : [];
$checks = [
    'inline panel class' => strpos($panel_html, 'gastronom-inline-weight-box') !== false,
    'panel heading' => strpos($panel_html, 'Подтверждение фактического веса') !== false,
    'nonce field' => strpos($panel_html, 'gastronom_weight_confirmation_nonce') !== false,
    'order id field' => strpos($panel_html, 'gastronom_weight_order_id') !== false,
    'actual weight field' => strpos($panel_html, 'gastronom_actual_weight[') !== false,
    'confirm button' => strpos($panel_html, 'Подтвердить вес') !== false,
    'await weight status label' => strpos($panel_html, 'На уточнении веса') !== false,
    'footer ajax action' => strpos($footer_html, "params.set('action', 'gastronom_confirm_weight')") !== false,
    'footer reload handler' => strpos($footer_html, 'window.location.reload()') !== false,
    'hidden meta box id present' => in_array('gastronom-weight-confirmation', $hidden_ids, true),
];

foreach ($checks as $label => $ok) {
    if (!$ok) {
        fwrite(STDERR, "FAIL $label\n");
        exit(1);
    }
    echo "OK   $label\n";
}

echo "Admin order screen verification complete.\n";
PHP

scp -P "$REMOTE_PORT" "$tmp_local" "$REMOTE_HOST:$tmp_remote" >/dev/null
ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "php '$tmp_remote' '$REMOTE_ROOT' '$PRODUCT_ID'; rm -f '$tmp_remote'"
