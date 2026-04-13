<?php
/**
 * Plugin Name: Rusky Commerce Adjustments
 * Description: Isolates weighted-product and pickup-note WooCommerce logic from the language layer.
 */

if (!defined('ABSPATH')) {
    exit;
}

function rca_current_lang(): string {
    if (function_exists('gls_current_lang_code')) {
        $lang = gls_current_lang_code();
        if ($lang === 'ru' || $lang === 'sk') {
            return $lang;
        }
    }

    if (isset($_GET['lang'])) {
        $lang = sanitize_key(wp_unslash($_GET['lang']));
        if ($lang === 'ru' || $lang === 'sk') {
            return $lang;
        }
    }

    if (isset($_COOKIE['gastronom_lang'])) {
        $lang = sanitize_key(wp_unslash($_COOKIE['gastronom_lang']));
        if ($lang === 'ru' || $lang === 'sk') {
            return $lang;
        }
    }

    return 'sk';
}

function rca_localize_label(string $value): string {
    $lang = rca_current_lang();
    $map = [
        'Osobne vyzdvihnutie' => ['ru' => 'Самовывоз', 'sk' => 'Osobne vyzdvihnutie'],
        'GLS doručenie na adresu' => ['ru' => 'GLS доставка на адрес', 'sk' => 'GLS doručenie na adresu'],
        'SK Packeta Pick-up Point (Z-Point, Z-Box)' => ['ru' => 'SK Packeta пункт выдачи (Z-Point, Z-Box)', 'sk' => 'SK Packeta Pick-up Point (Z-Point, Z-Box)'],
        'GLS Balíkomat' => ['ru' => 'GLS Баликомат', 'sk' => 'GLS Balíkomat'],
        'Card' => ['ru' => 'Оплата картой', 'sk' => 'Card'],
        'Platba pri doručení' => ['ru' => 'Оплата при получении', 'sk' => 'Platba pri doručení'],
        'Bankový prevod' => ['ru' => 'Банковский перевод', 'sk' => 'Bankový prevod'],
    ];

    foreach ($map as $from => $translations) {
        if (strpos($value, $from) !== false) {
            $value = str_replace($from, $translations[$lang], $value);
        }
    }

    return $value;
}

function rca_cod_fee_label(): string {
    return rca_current_lang() === 'ru'
        ? 'Доплата за наложенный платёж'
        : 'Poplatok za dobierku';
}

function rca_gateway_description_text(string $gateway_id, string $description) {
    $lang = rca_current_lang();

    if ($gateway_id === 'cod') {
        return $lang === 'ru'
            ? 'К заказу будет добавлена доплата за наложенный платёж 2,00 €.'
            : 'K objednávke bude pripočítaný poplatok za dobierku vo výške 2,00 €.';
    }

    if ($gateway_id === 'bacs') {
        if (function_exists('rpsf_has_preorder_checkout_cart') && rpsf_has_preorder_checkout_cart()) {
            return function_exists('rpsf_preorder_bank_transfer_description')
                ? rpsf_preorder_bank_transfer_description()
                : ($lang === 'ru'
                    ? 'Сумма заказа предварительная. После подтверждения веса мы отправим ссылку на оплату банковским переводом.'
                    : 'Suma objednávky je predbežná. Po potvrdení hmotnosti vám pošleme odkaz na úhradu bankovým prevodom.');
        }

        return $lang === 'ru'
            ? 'Оплатите заказ прямым банковским переводом на наш счёт. Заказ будет обработан после поступления оплаты.'
            : 'Zaplaťte priamym prevodom na náš bankový účet. Objednávka bude spracovaná po prijatí platby.';
    }

    return $description;
}

function rca_fix_add_to_cart_qty($quantity, $product_id) {
    if (get_post_meta($product_id, '_gls_weighted', true) === 'yes' && isset($_REQUEST['quantity'])) {
        return floatval(wp_unslash($_REQUEST['quantity']));
    }

    return $quantity;
}
add_filter('woocommerce_add_to_cart_quantity', 'rca_fix_add_to_cart_qty', 10, 2);

add_filter('woocommerce_stock_amount', 'floatval');

add_filter('woocommerce_update_cart_validation', function($passed, $cart_item_key, $values, $quantity) {
    if ($quantity > 0) {
        return $passed;
    }

    if (!isset($_POST['cart'][$cart_item_key]['qty'])) {
        return $passed;
    }

    $raw_qty = floatval(str_replace(',', '.', wp_unslash($_POST['cart'][$cart_item_key]['qty'])));
    if ($raw_qty <= 0) {
        return $passed;
    }

    $product_id = $values['product_id'];
    if (get_post_meta($product_id, '_gls_weighted', true) !== 'yes') {
        return $passed;
    }

    WC()->cart->set_quantity($cart_item_key, $raw_qty, false);
    return false;
}, 10, 4);

add_filter('woocommerce_rest_product_schema', function($schema) {
    if (isset($schema['properties']['stock_quantity'])) {
        $schema['properties']['stock_quantity']['type'] = 'number';
    }

    return $schema;
});

add_action('woocommerce_before_product_object_save', function($product) {
    $changes = $product->get_changes();
    if (isset($changes['stock_quantity'])) {
        $product->set_stock_quantity(floatval($changes['stock_quantity']));
        if (floatval($changes['stock_quantity']) > 0) {
            $product->set_stock_status('instock');
        }
    }
}, 10, 1);

function rca_weighted_product_field() {
    woocommerce_wp_checkbox([
        'id'          => '_gls_weighted',
        'label'       => 'Весовой товар',
        'description' => 'Позволяет покупателю выбирать дробное количество (0.1 кг)',
    ]);
}
add_action('woocommerce_product_options_general_product_data', 'rca_weighted_product_field');

function rca_save_weighted_field($post_id) {
    $value = isset($_POST['_gls_weighted']) ? 'yes' : 'no';
    update_post_meta($post_id, '_gls_weighted', $value);
}
add_action('woocommerce_process_product_meta', 'rca_save_weighted_field');

function rca_weighted_qty_args($args, $product) {
    if (get_post_meta($product->get_id(), '_gls_weighted', true) === 'yes') {
        $args['min_value'] = 0.01;
        $args['step'] = 0.01;
        $args['input_value'] = max($args['input_value'], 0.01);
    }

    return $args;
}
add_filter('woocommerce_quantity_input_args', 'rca_weighted_qty_args', 10, 2);

function rca_weighted_cart_qty($valid, $product_id, $quantity) {
    if (get_post_meta($product_id, '_gls_weighted', true) === 'yes') {
        return true;
    }

    return $valid;
}
add_filter('woocommerce_add_to_cart_validation', 'rca_weighted_cart_qty', 10, 3);

function rca_weighted_price_suffix($price, $product) {
    if (get_post_meta($product->get_id(), '_gls_weighted', true) === 'yes') {
        $price .= ' <span class="gls-price-unit">/ kg</span>';
    }

    return $price;
}
add_filter('woocommerce_get_price_html', 'rca_weighted_price_suffix', 10, 2);

function rca_add_cod_fee(): void {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    if (!is_checkout()) {
        return;
    }

    $chosen_payment = WC()->session->get('chosen_payment_method');
    if ($chosen_payment === 'cod') {
        WC()->cart->add_fee(rca_cod_fee_label(), 2.00, false);
    }
}
add_action('woocommerce_cart_calculate_fees', 'rca_add_cod_fee');

add_filter('woocommerce_cart_shipping_method_full_label', function($label, $method) {
    return rca_localize_label((string) $label);
}, 10, 2);

add_filter('woocommerce_gateway_title', function($title, $gateway_id) {
    return rca_localize_label((string) $title);
}, 10, 2);

add_filter('woocommerce_gateway_description', function($description, $gateway_id) {
    return rca_gateway_description_text((string) $gateway_id, $description);
}, 20, 2);

function rca_render_checkout_payment_refresh_script(): void {
    if (!is_checkout()) {
        return;
    }
    ?>
    <script>
    jQuery(document.body).on('payment_method_selected', function() {
        jQuery('body').trigger('update_checkout');
    });
    </script>
    <?php
}
add_action('wp_footer', 'rca_render_checkout_payment_refresh_script');

if (!function_exists('gastronom_add_cod_fee')) {
    function gastronom_add_cod_fee(): void {
        rca_add_cod_fee();
    }
}

if (!function_exists('gastronom_gateway_description')) {
    function gastronom_gateway_description($description, $id) {
        return rca_gateway_description_text((string) $id, $description);
    }
}

if (!function_exists('gastronom_render_checkout_payment_refresh_script')) {
    function gastronom_render_checkout_payment_refresh_script(): void {
        rca_render_checkout_payment_refresh_script();
    }
}

add_action('woocommerce_after_shipping_rate', function($method) {
    if (!function_exists('is_cart') || !is_cart()) {
        return;
    }

    $rate_id = $method->get_id();
    $pickup_methods = [
        'gls_shipping_method_parcel_shop_zones',
        'gls_shipping_method_parcel_locker',
    ];

    $is_pickup = in_array($rate_id, $pickup_methods, true) || strpos($rate_id, 'packeta_method') === 0;
    if (!$is_pickup) {
        return;
    }

    $note = rca_current_lang() === 'ru'
        ? '⟶ Пункт выдачи выберете на следующем шаге'
        : '⟶ Výdajné miesto vyberiete v ďalšom kroku';

    echo '<p class="gls-pickup-note"><small>';
    echo '<span>' . esc_html($note) . '</span>';
    echo '</small></p>';
}, 10, 1);
