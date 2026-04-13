<?php
/**
 * Plugin Name: Rusky Order Page Language
 * Description: Extraction-ready frontend order-language helpers.
 *
 * Scaffold status:
 * - local hardening surface only
 * - no live hook registration yet
 * - uses `ropl_*` names to avoid collisions with current `gastronom_*` ownership
 */

if (!defined('ABSPATH')) {
    exit;
}

function ropl_context_order() {
    if (is_admin() || !function_exists('wc_get_order')) {
        return null;
    }

    $order_id = 0;
    global $wp;

    if (isset($_GET['order-pay'])) {
        $order_id = (int) wp_unslash($_GET['order-pay']);
    } elseif (function_exists('is_checkout_pay_page') && is_checkout_pay_page()) {
        $order_id = isset($wp->query_vars['order-pay']) ? (int) $wp->query_vars['order-pay'] : 0;
    } elseif (isset($_GET['order-received'])) {
        $order_id = (int) wp_unslash($_GET['order-received']);
    } elseif (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-received')) {
        $order_id = isset($wp->query_vars['order-received']) ? (int) $wp->query_vars['order-received'] : 0;
    } elseif (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('view-order')) {
        $order_id = isset($wp->query_vars['view-order']) ? (int) $wp->query_vars['view-order'] : 0;
    }

    if ($order_id <= 0) {
        return null;
    }

    $order = wc_get_order($order_id);
    return $order instanceof WC_Order ? $order : null;
}

function ropl_normalize_order_page_html(string $html, string $lang): string {
    $is_pay = function_exists('is_checkout_pay_page') && is_checkout_pay_page();
    $is_view_order = function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('view-order');
    $is_order_received = function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-received');

    if ($lang === 'ru') {
        $replace = [
            'Môj Účet' => 'Мой аккаунт',
            'Môj účet' => 'Мой аккаунт',
            'My account' => 'Мой аккаунт',
            'Home' => 'Главная',
            'Doprava' => 'Доставка',
            'Shipping' => 'Доставка',
            'Kontakt' => 'Контакты',
            'Kontakty' => 'Контакты',
            'Contacts' => 'Контакты',
            'Prihláste sa do svojho účtu, aby ste mohli pokračovať na platbu.' => 'Пожалуйста, войдите в свою учётную запись, чтобы перейти к оплате.',
            'Používateľské meno alebo e-mail' => 'Имя пользователя или Email',
            'Username or email address' => 'Имя пользователя или Email',
            'Heslo' => 'Пароль',
            'Password' => 'Пароль',
            'Zapamätať si ma' => 'Запомнить меня',
            'Remember me' => 'Запомнить меня',
            'Prihlásiť sa' => 'Войти',
            'Log in' => 'Войти',
            'Zabudli ste heslo?' => 'Забыли пароль?',
            'Lost your password?' => 'Забыли пароль?',
            'Fakturačná adresa' => 'Платёжный адрес',
            'Billing address' => 'Платёжный адрес',
            'Informácie o objednávke' => 'Информация о заказе',
            'Order details' => 'Информация о заказе',
            'Skutočná hmotnosť' => 'Фактический вес',
            'Osobné vyzdvihnutie' => 'Самовывоз',
            'Platba kartou' => 'Оплата картой',
            'Card' => 'Оплата картой',
            'Apple Pay' => 'Apple Pay',
            'Google Pay' => 'Google Pay',
            'Medzisúčet' => 'Подытог',
            'Medzisúčet:' => 'Подытог:',
            'Subtotal' => 'Подытог',
            'Subtotal:' => 'Подытог:',
            'Doprava:' => 'Доставка:',
            'Shipping:' => 'Доставка:',
            'Spôsob platby:' => 'Способ оплаты:',
            'Payment method:' => 'Способ оплаты:',
            'Celkom:' => 'Итого:',
            'Celkom' => 'Итого',
            'Zaplatiť' => 'Оплатить',
            'Pay' => 'Оплатить',
            'Pay for order' => 'Оплатить заказ',
            'Zrušiť' => 'Отмена',
            'Cancel' => 'Отмена',
            '— ALEBO —' => '— ИЛИ —',
            '&mdash; ALEBO &mdash;' => '&mdash; ИЛИ &mdash;',
            'Prečítal(a) som si a súhlasím s' => 'Я прочитал(а) и принимаю',
            'obchodné podmienky' => 'правила и условия',
            'Objednávka #' => 'Заказ №',
            'Order #' => 'Заказ №',
            'Objednávka prijatá' => 'Заказ принят',
            'Order received' => 'Заказ принят',
            'Vaša objednávka bola prijatá. Ďakujeme.' => 'Ваш заказ принят. Благодарим вас.',
            'Thank you. Your order has been received.' => 'Ваш заказ принят. Благодарим вас.',
            'Prihláste sa do svojho účtu pre zobrazenie tejto objednávky.' => 'Войдите в свою учётную запись для просмотра этого заказа.',
            'Please log in to your account to view this order.' => 'Войдите в свою учётную запись для просмотра этого заказа.',
            'Status:' => 'Статус:',
            'Payment:' => 'Оплата:',
        ];

        if ($is_pay) {
            $replace['Objednávka'] = 'Оплатить заказ';
        }

        if ($is_view_order || $is_order_received) {
            $replace['Objednávka'] = 'Заказ';
        }

        return strtr($html, $replace);
    }

    $replace = [
        'Мой Аккаунт' => 'Môj účet',
        'Мой аккаунт' => 'Môj účet',
        'Моя учётная запись' => 'Môj účet',
        'Главная' => 'Domov',
        'Home' => 'Domov',
        'Доставка' => 'Doprava',
        'Shipping' => 'Doprava',
        'Контакты' => 'Kontakt',
        'Contacts' => 'Kontakt',
        'Пожалуйста, войдите в свою учётную запись, чтобы перейти к оплате.' => 'Prihláste sa do svojho účtu, aby ste mohli pokračovať na platbu.',
        'Пожалуйста войдите в вашу учетную запись ниже чтобы продожить к платежной форме.' => 'Prihláste sa do svojho účtu, aby ste mohli pokračovať na platbu.',
        'Имя пользователя или Email' => 'Používateľské meno alebo e-mail',
        'Username or email address' => 'Používateľské meno alebo e-mail',
        'Пароль' => 'Heslo',
        'Password' => 'Heslo',
        'Запомнить меня' => 'Zapamätať si ma',
        'Remember me' => 'Zapamätať si ma',
        'Войти' => 'Prihlásiť sa',
        'Log in' => 'Prihlásiť sa',
        'Забыли пароль?' => 'Zabudli ste heslo?',
        'Lost your password?' => 'Zabudli ste heslo?',
        'Платёжный адрес' => 'Fakturačná adresa',
        'Billing address' => 'Fakturačná adresa',
        'Информация о заказе' => 'Informácie o objednávke',
        'Order details' => 'Informácie o objednávke',
        'Фактический вес' => 'Skutočná hmotnosť',
        'Самовывоз' => 'Osobné vyzdvihnutie',
        'Оплата картой' => 'Platba kartou',
        'Card' => 'Platba kartou',
        'Apple Pay' => 'Apple Pay',
        'Google Pay' => 'Google Pay',
        'Подытог' => 'Medzisúčet',
        'Подытог:' => 'Medzisúčet:',
        'Subtotal' => 'Medzisúčet',
        'Subtotal:' => 'Medzisúčet:',
        'Доставка:' => 'Doprava:',
        'Shipping:' => 'Doprava:',
        'Способ оплаты:' => 'Spôsob platby:',
        'Payment method:' => 'Spôsob platby:',
        'Итого:' => 'Celkom:',
        'Итого' => 'Celkom',
        'Оплатить' => 'Zaplatiť',
        'Pay' => 'Zaplatiť',
        'Pay for order' => 'Zaplatiť objednávku',
        'Отмена' => 'Zrušiť',
        'Cancel' => 'Zrušiť',
        '— ИЛИ —' => '— ALEBO —',
        '&mdash; OR &mdash;' => '&mdash; ALEBO &mdash;',
        'Я прочитал(а) и принимаю' => 'Prečítal(a) som si a súhlasím s',
        'правила и условия' => 'obchodné podmienky',
        'Заказ №' => 'Objednávka č.',
        'Order #' => 'Objednávka č.',
        'Заказ получен' => 'Objednávka prijatá',
        'Заказ принят' => 'Objednávka prijatá',
        'Order received' => 'Objednávka prijatá',
        'Ваш заказ принят. Благодарим вас.' => 'Vaša objednávka bola prijatá. Ďakujeme.',
        'Thank you. Your order has been received.' => 'Vaša objednávka bola prijatá. Ďakujeme.',
        'Войдите в свою учётную запись для просмотра этого заказа.' => 'Prihláste sa do svojho účtu pre zobrazenie tejto objednávky.',
        'Please log in to your account to view this order.' => 'Prihláste sa do svojho účtu pre zobrazenie tejto objednávky.',
        'Состояние заказа &ndash; &laquo;Отменён&raquo; и он не может быть оплачен. Пожалуйста, обратитесь к администрации магазина.' => 'Stav objednávky &ndash; &laquo;Zrušená&raquo; a nie je možné ju uhradiť. Kontaktujte prosím správcu obchodu.',
        'Status:' => 'Stav:',
        'Payment:' => 'Platba:',
    ];

    if ($is_pay) {
        $replace['Оплатить заказ'] = 'Zaplatiť objednávku';
        $replace['Заказ'] = 'Objednávka';
    }

    if ($is_view_order || $is_order_received) {
        $replace['Заказ'] = 'Objednávka';
    }

    return strtr($html, $replace);
}

function ropl_localize_order_item_name($item_name, $item) {
    if (is_admin() || !($item instanceof WC_Order_Item_Product)) {
        return $item_name;
    }

    $order = ropl_context_order();
    if (!$order instanceof WC_Order) {
        return $item_name;
    }

    $lang = gastronom_order_lang($order);
    if ($lang !== 'ru' && $lang !== 'sk') {
        return $item_name;
    }

    $original_name = (string) $item->get_name();
    $localized_name = gastronom_localize_title($original_name, $lang);

    if ($localized_name === $original_name || $localized_name === '') {
        return $item_name;
    }

    $item_name = str_replace($original_name, esc_html($localized_name), $item_name);

    if ($item_name === '') {
        return esc_html($localized_name);
    }

    return $item_name;
}

function ropl_render_forced_order_lang_marker(): void {
    $order = ropl_context_order();
    if (!$order instanceof WC_Order) {
        return;
    }

    $lang = gastronom_order_lang($order);
    if ($lang !== 'ru' && $lang !== 'sk') {
        return;
    }

    echo "<script>window.gastronomForcedOrderLang=" . wp_json_encode($lang) . ";</script>\n";
}

function ropl_maybe_redirect_context_order_lang(): void {
    if (is_admin()) {
        return;
    }
    if ((function_exists('is_checkout_pay_page') && is_checkout_pay_page())
        || (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('view-order'))) {
        return;
    }

    $order = ropl_context_order();
    if (!$order instanceof WC_Order) {
        return;
    }

    $lang = gastronom_order_lang($order);
    if ($lang !== 'ru' && $lang !== 'sk') {
        return;
    }

    $current_lang = isset($_GET['lang']) ? sanitize_key(wp_unslash($_GET['lang'])) : '';
    if ($current_lang === $lang) {
        return;
    }

    $scheme = is_ssl() ? 'https://' : 'http://';
    $host = isset($_SERVER['HTTP_HOST']) ? wp_unslash($_SERVER['HTTP_HOST']) : '';
    $uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
    if ($host === '' || $uri === '') {
        return;
    }

    $target = add_query_arg('lang', $lang, $scheme . $host . $uri);
    wp_safe_redirect($target, 302);
    exit;
}

function ropl_start_order_page_buffer(): void {
    if (is_admin()) {
        return;
    }

    $order = ropl_context_order();
    if (!$order instanceof WC_Order) {
        return;
    }

    $lang = gastronom_order_lang($order);
    if ($lang !== 'ru' && $lang !== 'sk') {
        return;
    }

    ob_start(static function($html) use ($lang) {
        if (!is_string($html) || $html === '') {
            return $html;
        }

        return ropl_normalize_order_page_html($html, $lang);
    });
}

function ropl_render_order_item_actual_weight($item_id, $item, $order, $plain_text): void {
    if ($plain_text) {
        return;
    }
    if (!$item instanceof WC_Order_Item_Product) {
        return;
    }
    if (!$order instanceof WC_Order) {
        return;
    }
    if ($item->get_meta('_gastronom_weight_preorder', true) !== 'yes') {
        return;
    }

    $actual_weight = (float) $item->get_meta('_gastronom_actual_weight_kg', true);
    if ($actual_weight <= 0) {
        return;
    }

    $lang = gastronom_current_lang();
    $label = $lang === 'ru' ? 'Фактический вес' : 'Skutočná hmotnosť';

    echo '<div class="gastronom-order-item-weight" style="margin-top:4px;color:#475467;font-size:13px;">'
        . esc_html($label . ': ' . wc_format_localized_decimal($actual_weight, 2) . ' kg')
        . '</div>';
}

function ropl_order_item_quantity_html($quantity_html, $item) {
    if (is_admin() || !$item instanceof WC_Order_Item_Product) {
        return $quantity_html;
    }
    if ($item->get_meta('_gastronom_weight_preorder', true) !== 'yes') {
        return $quantity_html;
    }

    $order = ropl_context_order();
    if (!$order instanceof WC_Order) {
        return $quantity_html;
    }

    $actual_weight = (float) $item->get_meta('_gastronom_actual_weight_kg', true);
    if ($actual_weight <= 0) {
        return $quantity_html;
    }

    return ' <strong class="product-quantity">' . esc_html(wc_format_localized_decimal($actual_weight, 2) . ' kg') . '</strong>';
}

if (!function_exists('gastronom_get_context_order_for_frontend_language')) {
    function gastronom_get_context_order_for_frontend_language() {
        return ropl_context_order();
    }
}

if (!function_exists('gastronom_normalize_order_page_html')) {
    function gastronom_normalize_order_page_html(string $html, string $lang): string {
        return ropl_normalize_order_page_html($html, $lang);
    }
}

if (!function_exists('gastronom_localize_order_item_name')) {
    function gastronom_localize_order_item_name($item_name, $item) {
        return ropl_localize_order_item_name($item_name, $item);
    }
}

if (!function_exists('gastronom_render_forced_order_lang_marker')) {
    function gastronom_render_forced_order_lang_marker(): void {
        ropl_render_forced_order_lang_marker();
    }
}

if (!function_exists('gastronom_maybe_redirect_context_order_lang')) {
    function gastronom_maybe_redirect_context_order_lang(): void {
        ropl_maybe_redirect_context_order_lang();
    }
}

if (!function_exists('gastronom_start_order_page_buffer')) {
    function gastronom_start_order_page_buffer(): void {
        ropl_start_order_page_buffer();
    }
}

if (!function_exists('gastronom_render_order_item_actual_weight')) {
    function gastronom_render_order_item_actual_weight($item_id, $item, $order, $plain_text): void {
        ropl_render_order_item_actual_weight($item_id, $item, $order, $plain_text);
    }
}

if (!function_exists('gastronom_order_item_quantity_html')) {
    function gastronom_order_item_quantity_html($quantity_html, $item) {
        return ropl_order_item_quantity_html($quantity_html, $item);
    }
}

add_filter('woocommerce_order_item_quantity_html', 'gastronom_order_item_quantity_html', 20, 2);
