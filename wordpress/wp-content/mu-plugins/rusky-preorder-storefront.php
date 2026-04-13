<?php
/**
 * Plugin Name: Rusky Preorder Storefront
 * Description: Extraction-ready storefront/runtime helpers for preorder-by-weight products.
 *
 * Scaffold status:
 * - local hardening surface only
 * - no live hook registration yet
 * - uses `rpsf_*` names to avoid collisions with current `gastronom_*` ownership
 */

if (!defined('ABSPATH')) {
    exit;
}

function rpsf_quantity_input_args($args, $product) {
    if (!gastronom_product_has_preorder_weight($product)) {
        return $args;
    }

    $args['min_value'] = 1;
    $args['step'] = 1;
    $args['pattern'] = '[0-9]*';
    $args['inputmode'] = 'numeric';
    $args['input_value'] = max(1, (int) ($args['input_value'] ?: 1));
    $args['max_value'] = max(0, gastronom_weight_preorder_piece_capacity($product->get_id()));

    return $args;
}

function rpsf_add_to_cart_quantity($quantity, $product_id) {
    if (!gastronom_weight_preorder_enabled($product_id)) {
        return $quantity;
    }

    return max(1, (int) round((float) $quantity));
}

function rpsf_add_to_cart_validation($passed, $product_id, $quantity) {
    if (!gastronom_weight_preorder_enabled($product_id)) {
        return $passed;
    }

    $qty = (int) round((float) $quantity);
    $max = gastronom_weight_preorder_piece_capacity($product_id);
    if ($qty <= 0) {
        wc_add_notice(gastronom_t('Для этого товара можно заказать только целое количество штук.', 'Tento tovar je možné objednať len po celých kusoch.'), 'error');
        return false;
    }
    if ($max > 0 && $qty > $max) {
        wc_add_notice(sprintf(gastronom_t('Для предварительного заказа доступно только %d шт.', 'Pre predobjednávku sú dostupné len %d ks.'), $max), 'error');
        return false;
    }

    return $passed;
}

function rpsf_before_calculate_totals($cart): void {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }
    if (!$cart || is_null($cart)) {
        return;
    }

    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        if (empty($cart_item['data']) || empty($cart_item['product_id'])) {
            continue;
        }
        $product_id = (int) $cart_item['product_id'];
        if (!gastronom_weight_preorder_enabled($product_id)) {
            continue;
        }

        $avg = gastronom_weight_preorder_avg_kg($product_id);
        $price_per_kg = gastronom_weight_preorder_price_per_kg($product_id);
        if ($avg <= 0 || $price_per_kg <= 0) {
            continue;
        }

        $cart_item['data']->set_price($avg * $price_per_kg);
    }
}

function rpsf_get_item_data($item_data, $cart_item) {
    $product_id = !empty($cart_item['product_id']) ? (int) $cart_item['product_id'] : 0;
    if (!$product_id || !gastronom_weight_preorder_enabled($product_id)) {
        return $item_data;
    }

    $min = gastronom_weight_preorder_min_kg($product_id);
    $max = gastronom_weight_preorder_max_kg($product_id);
    $note = trim((string) get_post_meta($product_id, '_gastronom_weight_preorder_note', true));
    if ($note === '') {
        $note = gastronom_t(
            'Предварительный заказ. Точный вес и итоговая сумма будут уточнены после сборки заказа.',
            'Predobjednávka. Presná hmotnosť a konečná suma budú upresnené po príprave objednávky.'
        );
    }

    $item_data[] = [
        'name'  => gastronom_t('Формат продажи', 'Forma predaja'),
        'value' => gastronom_t('Поштучно, с уточнением фактического веса', 'Po kusoch, s neskorším upresnením skutočnej hmotnosti'),
    ];
    $item_data[] = [
        'name'  => gastronom_t('Примерный вес 1 шт.', 'Približná hmotnosť 1 ks'),
        'value' => wc_format_localized_decimal($min, 2) . '–' . wc_format_localized_decimal($max, 2) . ' кг',
    ];
    $item_data[] = [
        'name'  => gastronom_t('Важно', 'Dôležité'),
        'value' => $note,
    ];

    return $item_data;
}

function rpsf_render_single_product_note(): void {
    global $product;
    if (!$product instanceof WC_Product || !gastronom_weight_preorder_enabled($product->get_id())) {
        return;
    }

    $min = gastronom_weight_preorder_min_kg($product->get_id());
    $max = gastronom_weight_preorder_max_kg($product->get_id());
    $note = trim((string) get_post_meta($product->get_id(), '_gastronom_weight_preorder_note', true));
    if ($note === '') {
        $note = gastronom_t(
            'Точный вес и итоговая сумма будут подтверждены после сборки заказа.',
            'Presná hmotnosť a konečná suma budú potvrdené po príprave objednávky.'
        );
    }

    echo '<div class="gastronom-preorder-note" style="margin:16px 0;padding:14px 16px;border:1px solid #d8c38a;border-left:4px solid #c7921b;border-radius:10px;background:#fff8e7;color:#5f4a12;">';
    echo '<strong style="display:block;margin-bottom:6px;">' . esc_html(gastronom_t('Предзаказ по весу', 'Predobjednávka podľa hmotnosti')) . '</strong>';
    echo '<div style="margin-bottom:6px;">' . esc_html(gastronom_t('Продаётся поштучно. Примерный вес одной штуки:', 'Predáva sa po kusoch. Približná hmotnosť jedného kusa:')) . ' <strong>' . esc_html(wc_format_localized_decimal($min, 2) . '–' . wc_format_localized_decimal($max, 2)) . ' кг</strong>.</div>';
    echo '<div>' . esc_html($note) . '</div>';
    echo '</div>';
}

function rpsf_render_cart_notice(): void {
    if (!WC()->cart) {
        return;
    }

    if (rpsf_cart_has_preorder_items()) {
        wc_print_notice(gastronom_t('В корзине есть товары с предварительным расчётом по весу. Итоговая сумма будет уточнена после сборки заказа.', 'V košíku máte tovary s predbežným výpočtom podľa hmotnosti. Konečná suma bude upresnená po príprave objednávky.'), 'notice');
    }
}

function rpsf_cart_has_preorder_items(): bool {
    if (!WC()->cart) {
        return false;
    }

    foreach (WC()->cart->get_cart() as $item) {
        if (!empty($item['product_id']) && gastronom_weight_preorder_enabled((int) $item['product_id'])) {
            return true;
        }
    }

    return false;
}

function rpsf_has_preorder_checkout_cart(): bool {
    return !is_checkout_pay_page() && rpsf_cart_has_preorder_items();
}

function rpsf_has_preorder_context(): bool {
    if (is_checkout_pay_page()) {
        $order = rpsf_checkout_pay_order();

        return $order instanceof WC_Order && gastronom_order_has_preorder_weight($order);
    }

    return rpsf_cart_has_preorder_items();
}

function rpsf_preorder_checkout_notice_text(): string {
    return gastronom_t(
        'В корзине есть товары с уточнением веса. Итоговая сумма будет подтверждена после сборки заказа. После подтверждения веса мы отправим письмо со следующим шагом по выбранному способу оплаты.',
        'V košíku sú tovary s upresnením hmotnosti. Konečná suma bude potvrdená po príprave objednávky. Po potvrdení hmotnosti vám pošleme e-mail s ďalším krokom podľa zvoleného spôsobu platby.'
    );
}

function rpsf_preorder_bank_transfer_description(): string {
    return gastronom_t(
        'Сумма заказа предварительная. После подтверждения веса мы отправим ссылку на оплату банковским переводом.',
        'Suma objednávky je predbežná. Po potvrdení hmotnosti vám pošleme odkaz na úhradu bankovým prevodom.'
    );
}

function rpsf_render_checkout_payment_notice(): void {
    if (!is_checkout() || is_checkout_pay_page() || !rpsf_has_preorder_checkout_cart()) {
        return;
    }

    wc_print_notice(rpsf_preorder_checkout_notice_text(), 'notice');
}

function rpsf_checkout_pay_order() {
    if (!function_exists('is_checkout_pay_page') || !is_checkout_pay_page()) {
        return null;
    }

    global $wp;
    $order_id = isset($wp->query_vars['order-pay']) ? (int) $wp->query_vars['order-pay'] : 0;
    if ($order_id <= 0 && isset($_GET['order-pay'])) {
        $order_id = (int) wp_unslash($_GET['order-pay']);
    }

    if ($order_id <= 0) {
        return null;
    }

    $order = wc_get_order($order_id);
    return $order instanceof WC_Order ? $order : null;
}

function rpsf_available_payment_gateways($gateways) {
    if (is_admin()) {
        return $gateways;
    }

    unset($gateways['bacs']);

    $pay_order = rpsf_checkout_pay_order();
    if ($pay_order instanceof WC_Order && gastronom_order_has_preorder_weight($pay_order)) {
        $selected_gateway = (string) $pay_order->get_payment_method();
        if ($selected_gateway !== '') {
            foreach (array_keys($gateways) as $gateway_id) {
                if ($gateway_id !== $selected_gateway) {
                    unset($gateways[$gateway_id]);
                }
            }
        }
    }

    if (isset($gateways['cod'])) {
        $gateways['cod']->title = gastronom_t(
            'Оплата при получении',
            'Platba pri doručení'
        );
    }

    return $gateways;
}

function rpsf_price_html($price, $product) {
    if (!gastronom_product_has_preorder_weight($product)) {
        return $price;
    }
    if (strpos($price, '/ kg') !== false) {
        return $price;
    }

    return $price . ' <span class="gls-price-unit">/ kg</span>';
}

function rpsf_checkout_create_order($order): void {
    if (!$order instanceof WC_Order) {
        return;
    }

    $order->update_meta_data('_gastronom_lang', gastronom_current_lang());
}

function rpsf_checkout_create_order_line_item($item, $cart_item_key, $values, $order): void {
    $product_id = !empty($values['product_id']) ? (int) $values['product_id'] : 0;
    if (!$product_id || !gastronom_weight_preorder_enabled($product_id)) {
        return;
    }

    $item->add_meta_data('_gastronom_weight_preorder', 'yes', true);
    $item->add_meta_data('_gastronom_weight_min_kg', gastronom_weight_preorder_min_kg($product_id), true);
    $item->add_meta_data('_gastronom_weight_max_kg', gastronom_weight_preorder_max_kg($product_id), true);
    $item->add_meta_data('_gastronom_price_per_kg', gastronom_weight_preorder_price_per_kg($product_id), true);
    $item->add_meta_data('_gastronom_weight_confirmed', 'no', true);
}

if (!function_exists('gastronom_quantity_input_args')) {
    function gastronom_quantity_input_args($args, $product) {
        return rpsf_quantity_input_args($args, $product);
    }
}

if (!function_exists('gastronom_add_to_cart_quantity')) {
    function gastronom_add_to_cart_quantity($quantity, $product_id) {
        return rpsf_add_to_cart_quantity($quantity, $product_id);
    }
}

if (!function_exists('gastronom_add_to_cart_validation')) {
    function gastronom_add_to_cart_validation($passed, $product_id, $quantity) {
        return rpsf_add_to_cart_validation($passed, $product_id, $quantity);
    }
}

if (!function_exists('gastronom_before_calculate_totals')) {
    function gastronom_before_calculate_totals($cart): void {
        rpsf_before_calculate_totals($cart);
    }
}

if (!function_exists('gastronom_get_item_data')) {
    function gastronom_get_item_data($item_data, $cart_item) {
        return rpsf_get_item_data($item_data, $cart_item);
    }
}

if (!function_exists('gastronom_render_single_product_note')) {
    function gastronom_render_single_product_note(): void {
        rpsf_render_single_product_note();
    }
}

if (!function_exists('gastronom_render_cart_notice')) {
    function gastronom_render_cart_notice(): void {
        rpsf_render_cart_notice();
    }
}

if (!function_exists('gastronom_render_checkout_payment_notice')) {
    function gastronom_render_checkout_payment_notice(): void {
        rpsf_render_checkout_payment_notice();
    }
}

if (!function_exists('gastronom_available_payment_gateways')) {
    function gastronom_available_payment_gateways($gateways) {
        return rpsf_available_payment_gateways($gateways);
    }
}

if (!function_exists('gastronom_price_html')) {
    function gastronom_price_html($price, $product) {
        return rpsf_price_html($price, $product);
    }
}

if (!function_exists('gastronom_checkout_create_order')) {
    function gastronom_checkout_create_order($order): void {
        rpsf_checkout_create_order($order);
    }
}

if (!function_exists('gastronom_checkout_create_order_line_item')) {
    function gastronom_checkout_create_order_line_item($item, $cart_item_key, $values, $order): void {
        rpsf_checkout_create_order_line_item($item, $cart_item_key, $values, $order);
    }
}
