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

function rca_store_pickup_method_ids(): array {
    return [
        'local_pickup',
    ];
}

function rca_is_store_pickup_rate_id(string $rate_id): bool {
    foreach (rca_store_pickup_method_ids() as $method_id) {
        if ($rate_id === $method_id || strpos($rate_id, $method_id . ':') === 0) {
            return true;
        }
    }

    return false;
}

function rca_cart_has_store_pickup(): bool {
    if (!function_exists('WC') || !WC()->session) {
        return false;
    }

    $chosen_methods = WC()->session->get('chosen_shipping_methods');
    if (!is_array($chosen_methods)) {
        return false;
    }

    foreach ($chosen_methods as $method_id) {
        if (rca_is_store_pickup_rate_id((string) $method_id)) {
            return true;
        }
    }

    return false;
}

function rca_posted_checkout_has_store_pickup(): bool {
    if (empty($_POST['shipping_method'])) {
        return false;
    }

    $shipping_methods = wp_unslash($_POST['shipping_method']);
    if (!is_array($shipping_methods)) {
        $shipping_methods = [$shipping_methods];
    }

    foreach ($shipping_methods as $method_id) {
        if (rca_is_store_pickup_rate_id(sanitize_text_field((string) $method_id))) {
            return true;
        }
    }

    return false;
}

function rca_checkout_has_store_pickup(): bool {
    return rca_posted_checkout_has_store_pickup() || rca_cart_has_store_pickup();
}

function rca_order_is_store_pickup($order): bool {
    if (!$order instanceof WC_Order) {
        return false;
    }

    foreach ($order->get_shipping_methods() as $shipping_item) {
        $method_id = (string) $shipping_item->get_method_id();
        $instance_id = (string) $shipping_item->get_instance_id();
        $rate_id = $instance_id !== '' ? $method_id . ':' . $instance_id : $method_id;
        if (rca_is_store_pickup_rate_id($rate_id)) {
            return true;
        }

        $title = (string) $shipping_item->get_method_title();
        if (stripos($title, 'Osobne vyzdvihnutie') !== false || stripos($title, 'Самовывоз') !== false) {
            return true;
        }
    }

    return false;
}

function rca_next_store_pickup_date(string $from = 'tomorrow'): string {
    $timestamp = strtotime($from, current_time('timestamp'));
    while ((int) wp_date('N', $timestamp) === 7) {
        $timestamp = strtotime('+1 day', $timestamp);
    }

    return wp_date('Y-m-d', $timestamp);
}

function rca_is_valid_store_pickup_date(string $date): bool {
    $date = trim($date);
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }

    $timestamp = strtotime($date . ' 12:00:00');
    if (!$timestamp) {
        return false;
    }

    if ((int) wp_date('N', $timestamp) === 7) {
        return false;
    }

    return $date >= rca_next_store_pickup_date();
}

function rca_format_store_pickup_date(string $date): string {
    if ($date === '') {
        return '';
    }

    $timestamp = strtotime($date . ' 12:00:00');
    if (!$timestamp) {
        return $date;
    }

    return wp_date('d.m.Y', $timestamp);
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
        WC()->cart->add_fee('Poplatok za dobierku', 2.00, false);
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
    $lang = rca_current_lang();

    if ($gateway_id === 'cod') {
        return $lang === 'ru'
            ? 'К заказу будет добавлена доплата за наложенный платёж 2,00 €.'
            : 'K objednávke bude pripočítaný poplatok za dobierku vo výške 2,00 €.';
    }

    if ($gateway_id === 'bacs') {
        return $lang === 'ru'
            ? 'Оплатите заказ прямым банковским переводом на наш счёт. Заказ будет обработан после поступления оплаты.'
            : 'Zaplaťte priamym prevodom na náš bankový účet. Objednávka bude spracovaná po prijatí platby.';
    }

    return $description;
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

function rca_render_store_pickup_date_checkout_field($checkout): void {
    if (!function_exists('is_checkout') || !is_checkout() || (function_exists('is_order_received_page') && is_order_received_page())) {
        return;
    }

    $lang = rca_current_lang();
    $min_date = rca_next_store_pickup_date();
    $label = $lang === 'ru' ? 'Дата самовывоза' : 'Dátum osobného odberu';
    $description = $lang === 'ru'
        ? 'Выберите день работы магазина, начиная со следующего дня после заказа.'
        : 'Vyberte pracovný deň predajne, najskôr nasledujúci deň po objednávke.';

    echo '<div id="rca-store-pickup-date-wrap">';
    woocommerce_form_field('rca_store_pickup_date', [
        'type' => 'date',
        'class' => ['form-row-wide'],
        'label' => $label,
        'required' => true,
        'description' => $description,
        'custom_attributes' => [
            'min' => $min_date,
            'data-min-pickup-date' => $min_date,
        ],
    ], $checkout->get_value('rca_store_pickup_date'));
    echo '</div>';
}
add_action('woocommerce_after_order_notes', 'rca_render_store_pickup_date_checkout_field', 20);

function rca_render_store_pickup_date_script(): void {
    if (!function_exists('is_checkout') || !is_checkout() || (function_exists('is_order_received_page') && is_order_received_page())) {
        return;
    }

    $message = rca_current_lang() === 'ru'
        ? 'Магазин закрыт по воскресеньям. Выберите другой день самовывоза.'
        : 'Predajňa je v nedeľu zatvorená. Vyberte iný deň osobného odberu.';
    ?>
    <script>
    (function() {
        function isStorePickupSelected() {
            var selected = document.querySelectorAll('input[name^="shipping_method"]:checked, select[name^="shipping_method"]');
            for (var i = 0; i < selected.length; i++) {
                if ((selected[i].value || '').indexOf('local_pickup') === 0) {
                    return true;
                }
            }
            return false;
        }

        function syncPickupDateField() {
            var wrap = document.getElementById('rca-store-pickup-date-wrap');
            var input = document.getElementById('rca_store_pickup_date');
            if (!wrap || !input) return;

            var active = isStorePickupSelected();
            wrap.style.display = active ? '' : 'none';
            input.required = active;
            if (!active) {
                input.value = '';
                input.setCustomValidity('');
            }
        }

        document.addEventListener('change', function(event) {
            if (event.target && event.target.name && event.target.name.indexOf('shipping_method') === 0) {
                syncPickupDateField();
            }
            if (event.target && event.target.id === 'rca_store_pickup_date' && event.target.value) {
                var date = new Date(event.target.value + 'T12:00:00');
                if (date.getDay() === 0) {
                    event.target.setCustomValidity(<?php echo wp_json_encode($message); ?>);
                    event.target.reportValidity();
                } else {
                    event.target.setCustomValidity('');
                }
            }
        });

        document.body.addEventListener('updated_checkout', syncPickupDateField);
        document.addEventListener('DOMContentLoaded', syncPickupDateField);
        setTimeout(syncPickupDateField, 250);
    })();
    </script>
    <?php
}
add_action('wp_footer', 'rca_render_store_pickup_date_script', 30);

function rca_validate_store_pickup_date(): void {
    if (!rca_checkout_has_store_pickup()) {
        return;
    }

    $date = isset($_POST['rca_store_pickup_date']) ? sanitize_text_field(wp_unslash($_POST['rca_store_pickup_date'])) : '';
    if (!rca_is_valid_store_pickup_date($date)) {
        $message = rca_current_lang() === 'ru'
            ? 'Выберите дату самовывоза: не раньше следующего дня и не воскресенье.'
            : 'Vyberte dátum osobného odberu: najskôr nasledujúci deň a nie nedeľu.';
        wc_add_notice($message, 'error');
    }
}
add_action('woocommerce_checkout_process', 'rca_validate_store_pickup_date');

function rca_save_store_pickup_date_to_order($order): void {
    if (!$order instanceof WC_Order) {
        return;
    }

    if (!rca_checkout_has_store_pickup()) {
        return;
    }

    $date = isset($_POST['rca_store_pickup_date']) ? sanitize_text_field(wp_unslash($_POST['rca_store_pickup_date'])) : '';
    if ($date !== '' && rca_is_valid_store_pickup_date($date)) {
        $order->update_meta_data('_gastronom_store_pickup_date', $date);
        $order->update_meta_data('Дата самовывоза', rca_format_store_pickup_date($date));
    }
}
add_action('woocommerce_checkout_create_order', 'rca_save_store_pickup_date_to_order', 30);

function rca_admin_order_signal_rows($order): array {
    if (!$order instanceof WC_Order) {
        return [];
    }

    $is_pickup = rca_order_is_store_pickup($order);
    $pickup_date = rca_format_store_pickup_date((string) $order->get_meta('_gastronom_store_pickup_date', true));
    $paid = $order->is_paid();

    return [
        'Способ получения' => $is_pickup ? 'САМОВЫВОЗ ИЗ МАГАЗИНА' : 'Доставка',
        'Дата самовывоза' => $is_pickup ? ($pickup_date !== '' ? $pickup_date : 'НЕ УКАЗАНА') : '',
        'Оплата' => $paid ? 'ОПЛАЧЕН' : 'НЕ ОПЛАЧЕН / ожидает оплаты',
        'Способ оплаты' => (string) $order->get_payment_method_title(),
        'Статус заказа' => wc_get_order_status_name($order->get_status()),
    ];
}

function rca_render_admin_order_signal_email($order, $sent_to_admin, $plain_text, $email = null): void {
    if (!$sent_to_admin || !$order instanceof WC_Order) {
        return;
    }

    $rows = array_filter(rca_admin_order_signal_rows($order), static function($value) {
        return $value !== '';
    });
    if (!$rows) {
        return;
    }

    if ($plain_text) {
        echo "\n=== ВАЖНО ПО ЗАКАЗУ ===\n";
        foreach ($rows as $label => $value) {
            echo $label . ': ' . $value . "\n";
        }
        echo "\n";
        return;
    }

    $is_pickup = rca_order_is_store_pickup($order);
    $border = $is_pickup ? '#b42318' : '#175cd3';
    $background = $is_pickup ? '#fff4ed' : '#eff8ff';
    echo '<div style="margin:0 0 18px;padding:14px 16px;border:2px solid ' . esc_attr($border) . ';background:' . esc_attr($background) . ';border-radius:8px;">';
    echo '<p style="margin:0 0 8px;font-size:16px;"><strong>ВАЖНО ПО ЗАКАЗУ</strong></p>';
    echo '<table style="width:100%;border-collapse:collapse;">';
    foreach ($rows as $label => $value) {
        echo '<tr>';
        echo '<td style="padding:5px 10px 5px 0;width:38%;"><strong>' . esc_html($label) . '</strong></td>';
        echo '<td style="padding:5px 0;">' . esc_html($value) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '</div>';
}
add_action('woocommerce_email_before_order_table', 'rca_render_admin_order_signal_email', 8, 4);

function rca_add_pickup_date_email_meta($fields, $sent_to_admin, $order) {
    if (!$order instanceof WC_Order || !rca_order_is_store_pickup($order)) {
        return $fields;
    }

    $pickup_date = rca_format_store_pickup_date((string) $order->get_meta('_gastronom_store_pickup_date', true));
    if ($pickup_date !== '') {
        $fields['gastronom_store_pickup_date'] = [
            'label' => 'Дата самовывоза',
            'value' => $pickup_date,
        ];
    }

    return $fields;
}
add_filter('woocommerce_email_order_meta_fields', 'rca_add_pickup_date_email_meta', 20, 3);

function rca_render_pickup_date_admin_order($order): void {
    if (!$order instanceof WC_Order || !rca_order_is_store_pickup($order)) {
        return;
    }

    $pickup_date = rca_format_store_pickup_date((string) $order->get_meta('_gastronom_store_pickup_date', true));
    echo '<p><strong>Самовывоз:</strong> ' . esc_html($pickup_date !== '' ? $pickup_date : 'дата не указана') . '</p>';
}
add_action('woocommerce_admin_order_data_after_shipping_address', 'rca_render_pickup_date_admin_order');

if (!function_exists('gastronom_add_cod_fee')) {
    function gastronom_add_cod_fee(): void {
        rca_add_cod_fee();
    }
}

if (!function_exists('gastronom_gateway_description')) {
    function gastronom_gateway_description($description, $id) {
        $lang = rca_current_lang();

        if ($id === 'cod') {
            return $lang === 'ru'
                ? 'К заказу будет добавлена доплата за наложенный платёж 2,00 €.'
                : 'K objednávke bude pripočítaný poplatok za dobierku vo výške 2,00 €.';
        }

        if ($id === 'bacs') {
            return $lang === 'ru'
                ? 'Оплатите заказ прямым банковским переводом на наш счёт. Заказ будет обработан после поступления оплаты.'
                : 'Zaplaťte priamym prevodom na náš bankový účet. Objednávka bude spracovaná po prijatí platby.';
        }

        return $description;
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
