#!/bin/sh
set -eu

REMOTE_HOST="${REMOTE_HOST:-u595644545@46.202.156.109}"
REMOTE_PORT="${REMOTE_PORT:-65002}"
REMOTE_ROOT="${REMOTE_ROOT:-/home/u595644545/domains/ruskyobchod.sk/public_html}"

PRODUCT_ID="${1:-10617}"
PAYMENT_METHOD="${2:-cod}"
ACTUAL_WEIGHT="${3:-0.44}"

tmp_local="$(mktemp)"
tmp_remote="/tmp/rusky-admin-weight-confirmation-proof-$$.php"
trap 'rm -f "$tmp_local"' EXIT

cat >"$tmp_local" <<'PHP'
<?php
declare(strict_types=1);

if (!defined('DOING_AJAX')) {
    define('DOING_AJAX', true);
}

$_SERVER['HTTP_HOST'] = 'ruskyobchod.sk';
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['SERVER_NAME'] = 'ruskyobchod.sk';
$_SERVER['SERVER_PORT'] = '443';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_URI'] = '/wp-admin/admin-ajax.php?action=gastronom_confirm_weight';

require $argv[1] . '/wp-load.php';

class RuskyAjaxProofDie extends Exception {}

function rusky_ajax_proof_die_handler($message = '', $title = '', $args = []): void {
    throw new RuskyAjaxProofDie(is_scalar($message) ? (string) $message : 'wp_die');
}

$GLOBALS['rusky_ajax_proof_state'] = [
    'cleaned' => false,
    'order_id' => 0,
    'product_id' => 0,
    'stock' => null,
    'cash_stock' => null,
    'stock_status' => null,
    'manage_stock' => null,
    'dotypos_settings_key' => null,
    'dotypos_settings' => null,
    'dotypos_instance' => null,
    'dotypos_service' => null,
];

function rusky_ajax_proof_cleanup(): void {
    $state = &$GLOBALS['rusky_ajax_proof_state'];
    if (!empty($state['cleaned'])) {
        return;
    }

    if (!empty($state['order_id'])) {
        $order_id = (int) $state['order_id'];
        if ($order_id > 0 && function_exists('wc_get_order') && wc_get_order($order_id)) {
            wp_delete_post($order_id, true);
        }
    }

    if (!empty($state['product_id'])) {
        $product_id = (int) $state['product_id'];
        if ($product_id > 0) {
            if (array_key_exists('stock', $state)) {
                update_post_meta($product_id, '_stock', $state['stock']);
            }
            if (array_key_exists('cash_stock', $state)) {
                update_post_meta($product_id, '_gastronom_cash_stock_kg', $state['cash_stock']);
            }
            if (array_key_exists('stock_status', $state)) {
                update_post_meta($product_id, '_stock_status', $state['stock_status']);
            }
            if (array_key_exists('manage_stock', $state)) {
                update_post_meta($product_id, '_manage_stock', $state['manage_stock']);
            }
            if (function_exists('gastronom_reconcile_decimal_stock')) {
                gastronom_reconcile_decimal_stock($product_id);
            }
            wc_delete_product_transients($product_id);
        }
    }

    if (!empty($state['dotypos_settings_key'])) {
        update_option($state['dotypos_settings_key'], $state['dotypos_settings']);
    }

    if (!empty($state['dotypos_instance']) && is_object($state['dotypos_instance'])) {
        $state['dotypos_instance']->dotyposService = $state['dotypos_service'];
    }

    $state['cleaned'] = true;
}

register_shutdown_function('rusky_ajax_proof_cleanup');
add_filter('wp_die_handler', static function () {
    return 'rusky_ajax_proof_die_handler';
});
add_filter('wp_die_ajax_handler', static function () {
    return 'rusky_ajax_proof_die_handler';
});

$product_id = isset($argv[2]) ? (int) $argv[2] : 10617;
$payment_method = isset($argv[3]) ? (string) $argv[3] : 'cod';
$actual_weight = isset($argv[4]) ? (float) $argv[4] : 0.44;

$admins = get_users([
    'role' => 'administrator',
    'number' => 1,
    'fields' => ['ID'],
]);

if (!$admins) {
    fwrite(STDERR, "No administrator user found.\n");
    exit(1);
}

wp_set_current_user((int) $admins[0]->ID);

$product = wc_get_product($product_id);
if (!$product instanceof WC_Product) {
    fwrite(STDERR, "Product not found.\n");
    exit(1);
}

$GLOBALS['rusky_ajax_proof_state']['product_id'] = $product_id;
$GLOBALS['rusky_ajax_proof_state']['stock'] = get_post_meta($product_id, '_stock', true);
$GLOBALS['rusky_ajax_proof_state']['cash_stock'] = get_post_meta($product_id, '_gastronom_cash_stock_kg', true);
$GLOBALS['rusky_ajax_proof_state']['stock_status'] = get_post_meta($product_id, '_stock_status', true);
$GLOBALS['rusky_ajax_proof_state']['manage_stock'] = get_post_meta($product_id, '_manage_stock', true);

if (!class_exists('Dotypos')) {
    fwrite(STDERR, "Dotypos class not found.\n");
    exit(1);
}

$settings_key = Dotypos::$keys['settings'];
$settings = get_option($settings_key, []);
$GLOBALS['rusky_ajax_proof_state']['dotypos_settings_key'] = $settings_key;
$GLOBALS['rusky_ajax_proof_state']['dotypos_settings'] = $settings;

if (!is_array($settings)) {
    $settings = [];
}
if (!isset($settings['product']) || !is_array($settings['product'])) {
    $settings['product'] = [];
}
if (!isset($settings['product']['movement']) || !is_array($settings['product']['movement'])) {
    $settings['product']['movement'] = [];
}
if (!isset($settings['dotypos']) || !is_array($settings['dotypos'])) {
    $settings['dotypos'] = [];
}
$settings['product']['movement']['syncToDotypos'] = 1;
$settings['dotypos']['warehouseId'] = $settings['dotypos']['warehouseId'] ?? 1;
update_option($settings_key, $settings);

$dotypos = Dotypos::instance();
if (!$dotypos || !is_object($dotypos)) {
    fwrite(STDERR, "Dotypos instance not available.\n");
    exit(1);
}

$GLOBALS['rusky_ajax_proof_state']['dotypos_instance'] = $dotypos;
$GLOBALS['rusky_ajax_proof_state']['dotypos_service'] = $dotypos->dotyposService ?? null;

$stub_calls = [];
$dotypos->dotyposService = new class($stub_calls) {
    public array $calls = [];

    public function __construct(array &$calls) {
        $this->calls = &$calls;
    }

    public function updateProductStock($warehouseId, $dotyposId, $delta, $invoiceNumber): void {
        $this->calls[] = [
            'method' => 'updateProductStock',
            'warehouseId' => $warehouseId,
            'dotyposId' => $dotyposId,
            'delta' => $delta,
            'invoiceNumber' => $invoiceNumber,
        ];
    }

    public function getProductOnWarehouse($warehouseId, $dotyposId): array {
        $this->calls[] = [
            'method' => 'getProductOnWarehouse',
            'warehouseId' => $warehouseId,
            'dotyposId' => $dotyposId,
        ];
        return ['stockQuantityStatus' => 3.64];
    }
};

$mail_calls = [];
add_filter('pre_wp_mail', static function ($null, $atts) use (&$mail_calls) {
    $mail_calls[] = $atts;
    return true;
}, 10, 2);

$order = wc_create_order();
$GLOBALS['rusky_ajax_proof_state']['order_id'] = $order->get_id();
$order->set_payment_method($payment_method);
$order->set_payment_method_title($payment_method === 'cod' ? 'Оплата при получении' : 'Банковский перевод');
$order->set_billing_email('proof@example.com');
$order->set_currency(get_woocommerce_currency());
$order->save();

$item_id = $order->add_product($product, 1);
$item = $order->get_item($item_id);
if (!$item instanceof WC_Order_Item_Product) {
    fwrite(STDERR, "Order item not created.\n");
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
$item->update_meta_data('_gastronom_weight_confirmed', 'no');
$item->update_meta_data('_gastronom_weight_cash_synced', 'no');
$item->delete_meta_data('_gastronom_weight_cash_restored');
$item->delete_meta_data('_gastronom_actual_weight_kg');
$item->save();

$order->update_meta_data('_gastronom_requires_weight_confirmation', 'yes');
$order->save();
$order->update_status('await-weight', 'Proof harness temporary order.', true);

$_POST = [
    'nonce' => wp_create_nonce('gastronom_weight_confirmation'),
    'order_id' => (string) $order->get_id(),
    'gastronom_actual_weight' => [
        $item_id => (string) $actual_weight,
    ],
];

$json = '';
try {
    ob_start();
    rpa_handle_weight_confirmation_ajax();
    $json = (string) ob_get_clean();
} catch (RuskyAjaxProofDie $e) {
    $json = (string) ob_get_clean();
}

$result = json_decode($json, true);
$post_order = wc_get_order($order->get_id());
$post_item = $post_order ? $post_order->get_item($item_id) : null;

$summary = [
    'json' => $result,
    'order_id' => $order->get_id(),
    'payment_method' => $payment_method,
    'status_after' => $post_order ? $post_order->get_status() : null,
    'requires_confirmation_after' => $post_order ? $post_order->get_meta('_gastronom_requires_weight_confirmation', true) : null,
    'actual_weight_after' => $post_item instanceof WC_Order_Item_Product ? $post_item->get_meta('_gastronom_actual_weight_kg', true) : null,
    'weight_confirmed_after' => $post_item instanceof WC_Order_Item_Product ? $post_item->get_meta('_gastronom_weight_confirmed', true) : null,
    'cash_synced_after' => $post_item instanceof WC_Order_Item_Product ? $post_item->get_meta('_gastronom_weight_cash_synced', true) : null,
    'mail_calls' => count($mail_calls),
    'dotypos_calls' => $stub_calls,
];

rusky_ajax_proof_cleanup();

echo wp_json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;
PHP

scp -P "$REMOTE_PORT" "$tmp_local" "$REMOTE_HOST:$tmp_remote" >/dev/null
ssh -p "$REMOTE_PORT" "$REMOTE_HOST" "php '$tmp_remote' '$REMOTE_ROOT' '$PRODUCT_ID' '$PAYMENT_METHOD' '$ACTUAL_WEIGHT'; rm -f '$tmp_remote'"
