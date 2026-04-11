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
            'Doprava' => 'Доставка',
            'Kontakt' => 'Контакты',
            'Kontakty' => 'Контакты',
            'Prihláste sa do svojho účtu, aby ste mohli pokračovať na platbu.' => 'Пожалуйста, войдите в свою учётную запись, чтобы перейти к оплате.',
            'Používateľské meno alebo e-mail' => 'Имя пользователя или Email',
            'Heslo' => 'Пароль',
            'Zapamätať si ma' => 'Запомнить меня',
            'Prihlásiť sa' => 'Войти',
            'Zabudli ste heslo?' => 'Забыли пароль?',
            'Fakturačná adresa' => 'Платёжный адрес',
            'Informácie o objednávke' => 'Информация о заказе',
            'Skutočná hmotnosť' => 'Фактический вес',
            'Osobné vyzdvihnutie' => 'Самовывоз',
            'Bankový prevod' => 'Банковский перевод',
            'Platba po potvrdení hmotnosti' => 'Оплата после подтверждения веса',
            'Platba kartou' => 'Оплата картой',
            'Medzisúčet' => 'Подытог',
            'Medzisúčet:' => 'Подытог:',
            'Doprava:' => 'Доставка:',
            'Spôsob platby:' => 'Способ оплаты:',
            'Celkom:' => 'Итого:',
            'Celkom' => 'Итого',
            'Zaplatiť' => 'Оплатить',
            'Zrušiť' => 'Отмена',
            '— ALEBO —' => '— ИЛИ —',
            'ALEBO' => 'ИЛИ',
            '&mdash; ALEBO &mdash;' => '&mdash; ИЛИ &mdash;',
            'Prečítal(a) som si a súhlasím s' => 'Я прочитал(а) и принимаю',
            'obchodné podmienky' => 'правила и условия',
            'Objednávka #' => 'Заказ №',
            'Objednávka prijatá' => 'Заказ принят',
            'Vaša objednávka bola prijatá. Ďakujeme.' => 'Ваш заказ принят. Благодарим вас.',
            'Prihláste sa do svojho účtu pre zobrazenie tejto objednávky.' => 'Войдите в свою учётную запись для просмотра этого заказа.',
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
        'Доставка' => 'Doprava',
        'Контакты' => 'Kontakt',
        'Пожалуйста, войдите в свою учётную запись, чтобы перейти к оплате.' => 'Prihláste sa do svojho účtu, aby ste mohli pokračovať na platbu.',
        'Пожалуйста войдите в вашу учетную запись ниже чтобы продожить к платежной форме.' => 'Prihláste sa do svojho účtu, aby ste mohli pokračovať na platbu.',
        'Имя пользователя или Email' => 'Používateľské meno alebo e-mail',
        'Пароль' => 'Heslo',
        'Запомнить меня' => 'Zapamätať si ma',
        'Войти' => 'Prihlásiť sa',
        'Забыли пароль?' => 'Zabudli ste heslo?',
        'Платёжный адрес' => 'Fakturačná adresa',
        'Информация о заказе' => 'Informácie o objednávke',
        'Фактический вес' => 'Skutočná hmotnosť',
        'Самовывоз' => 'Osobné vyzdvihnutie',
        'Банковский перевод' => 'Bankový prevod',
        'Оплата после подтверждения веса' => 'Platba po potvrdení hmotnosti',
        'Оплата картой' => 'Platba kartou',
        'Подытог' => 'Medzisúčet',
        'Подытог:' => 'Medzisúčet:',
        'Доставка:' => 'Doprava:',
        'Способ оплаты:' => 'Spôsob platby:',
        'Итого:' => 'Celkom:',
        'Итого' => 'Celkom',
        'Оплатить' => 'Zaplatiť',
        'Отмена' => 'Zrušiť',
        '— ИЛИ —' => '— ALEBO —',
        'ИЛИ' => 'ALEBO',
        '&mdash; OR &mdash;' => '&mdash; ALEBO &mdash;',
        'Я прочитал(а) и принимаю' => 'Prečítal(a) som si a súhlasím s',
        'правила и условия' => 'obchodné podmienky',
        'Заказ №' => 'Objednávka č.',
        'Заказ получен' => 'Objednávka prijatá',
        'Заказ принят' => 'Objednávka prijatá',
        'Ваш заказ принят. Благодарим вас.' => 'Vaša objednávka bola prijatá. Ďakujeme.',
        'Войдите в свою учётную запись для просмотра этого заказа.' => 'Prihláste sa do svojho účtu pre zobrazenie tejto objednávky.',
        'Состояние заказа &ndash; &laquo;Отменён&raquo; и он не может быть оплачен. Пожалуйста, обратитесь к администрации магазина.' => 'Stav objednávky &ndash; &laquo;Zrušená&raquo; a nie je možné ju uhradiť. Kontaktujte prosím správcu obchodu.',
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
