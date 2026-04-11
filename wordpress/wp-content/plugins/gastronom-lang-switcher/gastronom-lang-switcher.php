<?php
/**
 * Plugin Name: Gastronom Language Switcher
 * Description: Простой переключатель RU/SK для двуязычных названий товаров + стили сайта
 * Version: 6.24
 * Author: Gastronom
 */

if (!defined('ABSPATH')) exit;

function gls_current_lang_code() {
    if (function_exists('rslc_current_lang')) {
        return rslc_current_lang();
    }

    if (is_admin() && !wp_doing_ajax()) {
        return 'sk';
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

function gls_is_server_rendered_language_page(): bool {
    if (is_admin()) {
        return false;
    }

    if (function_exists('is_cart') && is_cart()) {
        return true;
    }

    if (function_exists('is_checkout') && is_checkout()) {
        return true;
    }

    if (function_exists('is_account_page') && is_account_page()) {
        return true;
    }

    if (function_exists('is_checkout_pay_page') && is_checkout_pay_page()) {
        return true;
    }

    if (function_exists('is_wc_endpoint_url')) {
        if (is_wc_endpoint_url('view-order') || is_wc_endpoint_url('order-received')) {
            return true;
        }
    }

    return false;
}

function gls_is_server_rendered_storefront_page(): bool {
    if (is_admin()) {
        return false;
    }

    if (is_front_page()) {
        return true;
    }

    if (function_exists('is_product') && is_product()) {
        return true;
    }

    if (function_exists('is_shop') && is_shop()) {
        return true;
    }

    if (function_exists('is_product_category') && is_product_category()) {
        return true;
    }

    if (function_exists('is_product_tag') && is_product_tag()) {
        return true;
    }

    if (is_page()) {
        return true;
    }

    return false;
}

function gls_switcher_url(string $lang): string {
    if (function_exists('rslc_switcher_url')) {
        return rslc_switcher_url($lang);
    }

    $current_url = '';

    if (!empty($_SERVER['HTTP_HOST']) && !empty($_SERVER['REQUEST_URI'])) {
        $scheme = is_ssl() ? 'https://' : 'http://';
        $current_url = $scheme . wp_unslash($_SERVER['HTTP_HOST']) . wp_unslash($_SERVER['REQUEST_URI']);
    } else {
        $current_url = home_url('/');
    }

    return add_query_arg('lang', $lang, $current_url);
}

function gls_brand_name() {
    return gls_current_lang_code() === 'ru' ? 'Гастроном' : 'Gastronom';
}

function gls_brand_description() {
    return gls_current_lang_code() === 'ru'
        ? 'русский магазин продуктов в Братиславе'
        : 'obchod s ruskými potravinami v Bratislave';
}

function gls_localize_bilingual_text(string $text, ?string $lang = null): string {
    $value = trim(html_entity_decode(wp_strip_all_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    if ($value === '') {
        return trim($text);
    }

    $lang = $lang === 'ru' || $lang === 'sk' ? $lang : gls_current_lang_code();

    if (function_exists('gastronom_localize_title')) {
        return gastronom_localize_title($value, $lang);
    }

    foreach ([' / ', '/ ', ' /'] as $separator) {
        if (strpos($value, $separator) === false) {
            continue;
        }

        $parts = explode($separator, $value, 2);
        $left = trim((string) ($parts[0] ?? ''));
        $right = trim((string) ($parts[1] ?? ''));
        if ($left === '' || $right === '') {
            continue;
        }

        $left_cyr = preg_match('/[А-Яа-яЁё]/u', $left) === 1;
        $right_cyr = preg_match('/[А-Яа-яЁё]/u', $right) === 1;

        if ($left_cyr !== $right_cyr) {
            return $lang === 'ru'
                ? ($left_cyr ? $left : $right)
                : ($left_cyr ? $right : $left);
        }

        return $lang === 'ru' ? $right : $left;
    }

    return $value;
}

function gls_translate_static_title($title) {
    $lang = gls_current_lang_code();

    $map = [
        'Dostavka a platba' => [
            'ru' => 'Доставка и оплата',
            'sk' => 'Doprava a platba',
        ],
        'Doprava a platba' => [
            'ru' => 'Доставка и оплата',
            'sk' => 'Doprava a platba',
        ],
        'Kontakty' => [
            'ru' => 'Контакты',
            'sk' => 'Kontakt',
        ],
        'Kontakt' => [
            'ru' => 'Контакты',
            'sk' => 'Kontakt',
        ],
    ];

    if (isset($map[$title][$lang])) {
        return $map[$title][$lang];
    }

    return gls_localize_bilingual_text($title, $lang);
}

function gls_translate_menu_label(string $title, string $lang): string {
    $map = [
        'ru' => [
            'Domov' => 'Главная',
            'Doprava' => 'Доставка',
            'Kontakt' => 'Контакты',
            'Kontakty' => 'Контакты',
            'Контакт' => 'Контакты',
            'Môj účet' => 'Мой аккаунт',
            'Môj Účet' => 'Мой аккаунт',
            'Мой Аккаунт' => 'Мой аккаунт',
            'Моя учётная запись' => 'Мой аккаунт',
        ],
        'sk' => [
            'Главная' => 'Domov',
            'Доставка' => 'Doprava',
            'Контакты' => 'Kontakt',
            'Контакт' => 'Kontakt',
            'Мой аккаунт' => 'Môj účet',
            'Мой Аккаунт' => 'Môj účet',
            'Моя учётная запись' => 'Môj účet',
        ],
    ];

    return $map[$lang][$title] ?? $title;
}

function gls_translate_account_checkout_phrase(string $value, string $lang): string {
    $map = [
        'ru' => [
            'Please log in to your account below to continue to the payment form.' => 'Пожалуйста, войдите в свою учётную запись, чтобы перейти к оплате.',
            'Пожалуйста войдите в вашу учетную запись ниже чтобы продожить к платежной форме.' => 'Пожалуйста, войдите в свою учётную запись, чтобы перейти к оплате.',
            'Cart' => 'Корзина',
            'Checkout' => 'Оформление заказа',
            'Your cart is currently empty.' => 'Ваша корзина пока пуста.',
            'Return to shop' => 'Вернуться в магазин',
            'Username or email' => 'Имя пользователя или Email',
            'Password' => 'Пароль',
            'Remember me' => 'Запомнить меня',
            'Log in' => 'Войти',
            'Lost your password?' => 'Забыли пароль?',
            'Забыли свой пароль?' => 'Забыли пароль?',
            'Register' => 'Регистрация',
            'Registrácia' => 'Регистрация',
            'Login / Register' => 'Вход / Регистрация',
            'Prihlásenie / Registrácia' => 'Вход / Регистрация',
            'Billing address' => 'Платёжный адрес',
            'My account' => 'Мой аккаунт',
            'Order' => 'Заказ',
            'Ship to a different address?' => 'Доставка по другому адресу?',
            'Subtotal' => 'Подытог',
            'Total' => 'Итого',
            'Place order' => 'Оформить заказ',
        ],
        'sk' => [
            'Пожалуйста войдите в вашу учетную запись ниже чтобы продожить к платежной форме.' => 'Prihláste sa do svojho účtu, aby ste mohli pokračovať na platbu.',
            'Пожалуйста, войдите в свою учётную запись, чтобы перейти к оплате.' => 'Prihláste sa do svojho účtu, aby ste mohli pokračovať na platbu.',
            'Корзина' => 'Košík',
            'Оформление заказа' => 'Pokladňa',
            'Ваша корзина пока пуста.' => 'Váš košík je zatiaľ prázdny.',
            'Вернуться в магазин' => 'Späť do obchodu',
            'Имя пользователя или Email' => 'Používateľské meno alebo e-mail',
            'Пароль' => 'Heslo',
            'Запомнить меня' => 'Zapamätať si ma',
            'Войти' => 'Prihlásiť sa',
            'Забыли пароль?' => 'Zabudli ste heslo?',
            'Забыли свой пароль?' => 'Zabudli ste heslo?',
            'Регистрация' => 'Registrácia',
            'Вход / Регистрация' => 'Prihlásenie / Registrácia',
            'Login / Register' => 'Prihlásenie / Registrácia',
            'Платёжный адрес' => 'Fakturačná adresa',
            'Мой аккаунт' => 'Môj účet',
            'Заказ' => 'Objednávka',
            'Обязательно' => 'Povinné',
            'Имя' => 'Meno',
            'Фамилия' => 'Priezvisko',
            'Страна/регион' => 'Krajina/región',
            'Выберите страну/регион…' => 'Vyberte krajinu/región…',
            'Выберите страну/регион&hellip;' => 'Vyberte krajinu/región&hellip;',
            'Адрес' => 'Adresa',
            'Номер дома и название улицы' => 'Číslo domu a názov ulice',
            'Почтовый индекс' => 'PSČ',
            'Населённый пункт' => 'Mesto',
            'Область / район' => 'Kraj / okres',
            '(необязательно)' => '(voliteľné)',
            'Телефон' => 'Telefón',
            'Доставка по другому адресу?' => 'Doručiť na inú adresu?',
            'Подытог' => 'Medzisúčet',
            'Итого' => 'Celkom',
            'Place order' => 'Objednať s povinnosťou platby',
            'Оформить заказ' => 'Objednať s povinnosťou platby',
        ],
    ];

    return $map[$lang][$value] ?? $value;
}

function gls_translate_theme_chrome_phrase(string $value, string $lang): string {
    $map = [
        'ru' => [
            'Footer' => 'Подвал',
            'Top Menu' => 'Верхнее меню',
            'All Categories' => 'Все рубрики',
            'Login / Register' => 'Вход / Регистрация',
            'shopping cart' => 'корзина',
            'Open Button' => 'Кнопка Открыть',
            'Close Button' => 'Кнопка Закрыть',
            'Scroll Up' => 'Прокрутить вверх',
            'Skip to content' => 'Перейти к содержимому',
            'Cookie Notice' => 'Уведомление о cookie',
        ],
        'sk' => [
            'Подвал' => 'Footer',
            'Верхнее меню' => 'Top Menu',
            'Все рубрики' => 'All Categories',
            'Вход / Регистрация' => 'Login / Register',
            'корзина' => 'shopping cart',
            'Кнопка Открыть' => 'Open Button',
            'Кнопка Закрыть' => 'Close Button',
            'Прокрутить вверх' => 'Scroll Up',
            'Перейти к содержимому' => 'Skip to content',
            'Уведомление о cookie' => 'Cookie Notice',
        ],
    ];

    return $map[$lang][$value] ?? $value;
}

function gls_translate_woocommerce_storefront_phrase(string $value, string $lang): string {
    $map = [
        'ru' => [
            'Do košíka' => 'В корзину',
            'Súvisiace produkty' => 'Похожие товары',
            'Na sklade' => 'В наличии',
            'SKU:' => 'Артикул:',
            'Kategória:' => 'Категория:',
            'Množstvo produktu' => 'Количество товара',
        ],
        'sk' => [
            'В корзину' => 'Do košíka',
            'Похожие товары' => 'Súvisiace produkty',
            'В наличии' => 'Na sklade',
            'Артикул:' => 'SKU:',
            'Категория:' => 'Kategória:',
            'Количество товара' => 'Množstvo produktu',
        ],
    ];

    return $map[$lang][$value] ?? $value;
}

add_filter('option_blogname', function($value) {
    if (is_admin() && !wp_doing_ajax()) {
        return $value;
    }

    return gls_brand_name();
}, 20);

add_filter('option_blogdescription', function($value) {
    if (is_admin() && !wp_doing_ajax()) {
        return $value;
    }

    return gls_brand_description();
}, 20);

add_filter('the_title', function($title, $post_id) {
    if (is_admin() || !$post_id) {
        return $title;
    }

    $front_id = (int) get_option('page_on_front');
    if ($front_id > 0 && (int) $post_id === $front_id) {
        return gls_brand_name();
    }

    return gls_translate_static_title($title);
}, 20, 2);

add_filter('document_title_parts', function($parts) {
    if (!is_front_page()) {
        return $parts;
    }

    $parts['title'] = gls_brand_name();
    $parts['tagline'] = gls_brand_description();

    return $parts;
}, 20);

add_filter('document_title_parts', function($parts) {
    if (is_admin()) {
        return $parts;
    }

    $lang = gls_server_lang();

    if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('view-order')) {
        $parts['title'] = $lang === 'ru' ? 'Заказ' : 'Objednávka';
        $parts['site'] = gls_brand_name();
        return $parts;
    }

    if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-received')) {
        $parts['title'] = $lang === 'ru' ? 'Заказ получен' : 'Objednávka prijatá';
        $parts['site'] = gls_brand_name();
        return $parts;
    }

    if (function_exists('is_checkout_pay_page') && is_checkout_pay_page()) {
        $parts['title'] = $lang === 'ru' ? 'Оплатить заказ' : 'Zaplatiť objednávku';
        $parts['site'] = gls_brand_name();
        return $parts;
    }

    if (function_exists('is_checkout') && is_checkout() && !(function_exists('is_checkout_pay_page') && is_checkout_pay_page())) {
        $parts['title'] = $lang === 'ru' ? 'Оформление заказа' : 'Pokladňa';
        $parts['site'] = gls_brand_name();
        return $parts;
    }

    if (function_exists('is_cart') && is_cart()) {
        $parts['title'] = $lang === 'ru' ? 'Корзина' : 'Košík';
        $parts['site'] = gls_brand_name();
        return $parts;
    }

    if (function_exists('is_account_page') && is_account_page()) {
        $parts['title'] = $lang === 'ru' ? 'Мой аккаунт' : 'Môj účet';
        $parts['site'] = gls_brand_name();
        return $parts;
    }

    if ((function_exists('is_tax') && is_tax()) || is_category() || is_tag()) {
        $term = get_queried_object();
        if (is_object($term) && !empty($term->name)) {
            $parts['title'] = gls_localize_bilingual_text((string) $term->name, $lang);
            $parts['site'] = gls_brand_name();
            return $parts;
        }
    }

    if (is_singular()) {
        $parts['title'] = gls_translate_static_title(
            gls_localize_bilingual_text(single_post_title('', false), $lang)
        );
        $parts['site'] = gls_brand_name();
        return $parts;
    }

    return $parts;
}, 30);

add_filter('wpseo_title', function($title) {
    if (!is_front_page()) {
        return $title;
    }

    return gls_brand_name() . ' — ' . gls_brand_description();
}, 20);

// Keep Stripe/WooPayments aligned with the current server-side page language.
add_filter('wcpay_elements_options', function($options) {
    $options['locale'] = gls_server_lang() === 'ru' ? 'ru' : 'sk';
    return $options;
});
add_filter('wc_stripe_elements_options', function($options) {
    $options['locale'] = gls_server_lang() === 'ru' ? 'ru' : 'sk';
    return $options;
});

function gls_server_lang(): string {
    if (function_exists('rslc_current_lang')) {
        return rslc_current_lang();
    }

    $query_lang = isset($_GET['lang']) ? sanitize_key(wp_unslash($_GET['lang'])) : '';
    if ($query_lang === 'ru' || $query_lang === 'sk') {
        return $query_lang;
    }

    $cookie_lang = isset($_COOKIE['gastronom_lang']) ? sanitize_key(wp_unslash($_COOKIE['gastronom_lang'])) : '';
    return $cookie_lang === 'ru' ? 'ru' : 'sk';
}

add_action('init', function() {
    if (is_admin() && !wp_doing_ajax()) {
        return;
    }

    $query_lang = isset($_GET['lang']) ? sanitize_key(wp_unslash($_GET['lang'])) : '';
    if ($query_lang !== 'ru' && $query_lang !== 'sk') {
        return;
    }

    setcookie('gastronom_lang', $query_lang, time() + YEAR_IN_SECONDS, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), false);
    $_COOKIE['gastronom_lang'] = $query_lang;
}, 1);

add_filter('wp_redirect', function($location, $status) {
    if (!is_string($location) || $location === '') {
        return $location;
    }

    $lang = gls_server_lang();
    if ($lang !== 'ru' && $lang !== 'sk') {
        return $location;
    }

    $home_host = wp_parse_url(home_url('/'), PHP_URL_HOST);
    $target_host = wp_parse_url($location, PHP_URL_HOST);

    if (!empty($target_host) && !empty($home_host) && strtolower((string) $target_host) !== strtolower((string) $home_host)) {
        return $location;
    }

    return add_query_arg('lang', $lang, $location);
}, 20, 2);

function gls_is_sensitive_runtime_context(): bool {
    if (is_admin() && !wp_doing_ajax()) {
        return true;
    }

    if (wp_doing_ajax()) {
        return true;
    }

    if ((defined('REST_REQUEST') && REST_REQUEST) || (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST)) {
        return true;
    }

    if (isset($_GET['elementor-preview']) || isset($_GET['action']) && $_GET['action'] === 'elementor') {
        return true;
    }
    
    return false;
}

function gls_frontend_locale(string $locale): string {
    if (gls_is_sensitive_runtime_context()) {
        return $locale;
    }

    return gls_server_lang() === 'ru' ? 'ru_RU' : 'sk_SK';
}

add_filter('locale', 'gls_frontend_locale', 20);
add_filter('determine_locale', 'gls_frontend_locale', 20);

add_filter('gettext', function($translated, $text, $domain) {
    if (gls_is_sensitive_runtime_context()) {
        return $translated;
    }

    $lang = gls_server_lang();

    if ((function_exists('is_account_page') && is_account_page()) || (function_exists('is_checkout_pay_page') && is_checkout_pay_page())) {
        $translated = gls_translate_account_checkout_phrase($translated, $lang);
        $translated = gls_translate_account_checkout_phrase($text, $lang) === $text ? $translated : gls_translate_account_checkout_phrase($text, $lang);
    }

    if ((function_exists('is_cart') && is_cart()) || (function_exists('is_checkout') && is_checkout())) {
        $translated = gls_translate_account_checkout_phrase($translated, $lang);
        $translated = gls_translate_account_checkout_phrase($text, $lang) === $text ? $translated : gls_translate_account_checkout_phrase($text, $lang);
    }

    if ($lang === 'ru') {
        if ($translated === 'Моя учётная запись' || $translated === 'Мой Аккаунт') {
            return 'Мой аккаунт';
        }
    } else {
        if ($translated === 'Môj Účet') {
            return 'Môj účet';
        }
    }

    if ($domain === 'food-grocery-store' || $domain === 'cookie-notice') {
        $translated = gls_translate_theme_chrome_phrase($translated, $lang);
        $translated = gls_translate_theme_chrome_phrase($text, $lang) === $text
            ? $translated
            : gls_translate_theme_chrome_phrase($text, $lang);
    }

    if ($domain === 'woocommerce') {
        $translated = gls_translate_woocommerce_storefront_phrase($translated, $lang);
        $translated = gls_translate_woocommerce_storefront_phrase($text, $lang) === $text
            ? $translated
            : gls_translate_woocommerce_storefront_phrase($text, $lang);
    }

    return $translated;
}, 20, 3);

add_filter('nav_menu_item_title', function($title) {
    $lang = gls_server_lang();

    return gls_translate_menu_label($title, $lang);
}, 20);

add_filter('single_term_title', function($title) {
    if (is_admin()) {
        return $title;
    }

    return gls_localize_bilingual_text((string) $title, gls_server_lang());
}, 20);

add_filter('woocommerce_page_title', function($title) {
    if (is_admin()) {
        return $title;
    }

    return gls_localize_bilingual_text((string) $title, gls_server_lang());
}, 20);

add_filter('woocommerce_get_breadcrumb', function($crumbs) {
    if (is_admin() || !is_array($crumbs)) {
        return $crumbs;
    }

    $lang = gls_server_lang();
    foreach ($crumbs as $index => $crumb) {
        if (!isset($crumb[0])) {
            continue;
        }
        $text = (string) $crumb[0];
        $text = gls_translate_menu_label($text, $lang);
        $text = gls_translate_woocommerce_storefront_phrase($text, $lang);
        $crumbs[$index][0] = gls_localize_bilingual_text($text, $lang);
    }

    return $crumbs;
}, 20);

add_filter('woocommerce_product_get_name', function($name, $product) {
    if (is_admin()) {
        return $name;
    }

    return gls_localize_bilingual_text((string) $name, gls_server_lang());
}, 20, 2);

add_filter('woocommerce_product_variation_get_name', function($name, $product) {
    if (is_admin()) {
        return $name;
    }

    return gls_localize_bilingual_text((string) $name, gls_server_lang());
}, 20, 2);

add_filter('the_title', function($title, $post_id = 0) {
    if (is_admin()) {
        return $title;
    }

    $lang = gls_server_lang();

    if (function_exists('is_checkout_pay_page') && is_checkout_pay_page()) {
        if ($lang === 'sk' && ($title === 'Оплатить заказ' || $title === 'Objednávka')) {
            return 'Zaplatiť objednávku';
        }
        if ($lang === 'ru' && ($title === 'Zaplatiť objednávku' || $title === 'Objednávka')) {
            return 'Оплатить заказ';
        }
    }

    if (function_exists('is_account_page') && is_account_page()) {
        if ($lang === 'ru') {
            if ($title === 'Môj účet' || $title === 'Môj Účet' || $title === 'Моя учётная запись' || $title === 'Мой Аккаунт') {
                return 'Мой аккаунт';
            }
        } else {
            if ($title === 'Мой аккаунт' || $title === 'Мой Аккаунт' || $title === 'Моя учётная запись') {
                return 'Môj účet';
            }
            if ($title === 'Môj Účet') {
                return 'Môj účet';
            }
        }
    }

    return gls_localize_bilingual_text($title, $lang);
}, 20, 2);

add_filter('pre_get_document_title', function($title) {
    if (is_front_page()) {
        return gls_brand_name() . ' — ' . gls_brand_description();
    }

    $lang = gls_server_lang();

    if ($lang === 'ru') {
        $title = str_replace('Gastronom', 'Гастроном', $title);
        $title = str_replace('Môj účet', 'Мой аккаунт', $title);
        $title = str_replace('Môj Účet', 'Мой аккаунт', $title);
        $title = str_replace('Моя учётная запись', 'Мой аккаунт', $title);
    } else {
        $title = str_replace('Гастроном', 'Gastronom', $title);
        $title = str_replace('Мой аккаунт', 'Môj účet', $title);
        $title = str_replace('Мой Аккаунт', 'Môj účet', $title);
        $title = str_replace('Моя учётная запись', 'Môj účet', $title);
        $title = str_replace('Оплатить заказ', 'Zaplatiť objednávku', $title);
    }

    return $title;
}, 20);

function gls_normalize_public_title(string $title): string {
    $lang = gls_server_lang();

    if ($lang === 'ru') {
        $title = str_replace('Gastronom', 'Гастроном', $title);
        $title = str_replace('Môj účet', 'Мой аккаунт', $title);
        $title = str_replace('Môj Účet', 'Мой аккаунт', $title);
        $title = str_replace('Моя учётная запись', 'Мой аккаунт', $title);
        $title = str_replace('Objednávka', 'Оплатить заказ', $title);
    } else {
        $title = str_replace('Гастроном', 'Gastronom', $title);
        $title = str_replace('Мой аккаунт', 'Môj účet', $title);
        $title = str_replace('Мой Аккаунт', 'Môj účet', $title);
        $title = str_replace('Моя учётная запись', 'Môj účet', $title);
        $title = str_replace('Оплатить заказ', 'Zaplatiť objednávku', $title);
        $title = str_replace('Заказ', 'Objednávka', $title);
    }

    return $title;
}

add_filter('wpseo_title', 'gls_normalize_public_title', 20);
add_filter('wpseo_opengraph_title', 'gls_normalize_public_title', 20);
add_filter('wpseo_twitter_title', 'gls_normalize_public_title', 20);
add_filter('rank_math/frontend/title', 'gls_normalize_public_title', 20);
add_filter('rank_math/opengraph/facebook/title', 'gls_normalize_public_title', 20);
add_filter('rank_math/opengraph/twitter/title', 'gls_normalize_public_title', 20);

function gls_normalize_server_rendered_html(string $html, string $lang): string {
    $normalize_empty_cart_shell = static function(string $value) use ($lang): string {
        $value = preg_replace(
            '~<a[^>]*class="[^"]*skip-link[^"]*"[^>]*href="#maincontent"[^>]*>.*?</a>~su',
            $lang === 'ru'
                ? '<a class="screen-reader-text skip-link" href="#maincontent">Перейти к содержимому</a>'
                : '<a class="screen-reader-text skip-link" href="#maincontent">Preskočiť na obsah</a>',
            $value,
            1
        );

        $value = preg_replace(
            '~<h3([^>]*)>\s*(Гастроном|Gastronom)\s*</h3>~su',
            $lang === 'ru'
                ? '<h3$1>Гастроном</h3>'
                : '<h3$1>Gastronom</h3>',
            $value
        );

        $value = preg_replace(
            '~<button([^>]*)>\s*(Ок|Ok)\s*</button>~su',
            $lang === 'ru'
                ? '<button$1>Ок</button>'
                : '<button$1>Ok</button>',
            $value
        );

        if ($lang === 'ru') {
            $value = str_replace(
                '>Гастроном</a> <span> Objednávka',
                '>Гастроном</a> <span> Оформление заказа',
                $value
            );
            $value = preg_replace(
                '~(<div class="bradcrumbs">.*?<span>\s*)Objednávka(\s*</span>)~su',
                '$1Оформление заказа$2',
                $value,
                1
            );
            $value = preg_replace('~<h1 class="vw-page-title">\s*Objednávka\s*</h1>~u', '<h1 class="vw-page-title">Оформление заказа</h1>', $value, 1);
        } else {
            $value = preg_replace(
                '~(<div class="bradcrumbs">.*?<span>\s*)(Оформление заказа|Заказ)(\s*</span>)~su',
                '$1Objednávka$3',
                $value,
                1
            );
            $value = preg_replace('~<h1 class="vw-page-title">\s*(Оформление заказа|Заказ)\s*</h1>~u', '<h1 class="vw-page-title">Objednávka</h1>', $value, 1);
        }

        if (strpos($value, 'wc-empty-cart-message') === false) {
            return $value;
        }

        $value = preg_replace('~<div class="bradcrumbs">.*?</div>\s*~su', '', $value, 1);
        $value = preg_replace('~<h1 class="vw-page-title">.*?</h1>\s*~su', '', $value, 1);

        return (string) $value;
    };

    if ($lang === 'ru') {
        return $normalize_empty_cart_shell(strtr($html, [
            'Môj účet' => 'Мой аккаунт',
            'Môj Účet' => 'Мой аккаунт',
            'My account' => 'Мой аккаунт',
            'Prihlásenie / Registrácia' => 'Вход / Регистрация',
            'Registrácia' => 'Регистрация',
            'Zabudli ste heslo?' => 'Забыли пароль?',
            'košík' => 'корзина',
            'Košík' => 'Корзина',
            'Tlačidlo Otvoriť' => 'Кнопка Открыть',
            'Tlačidlo Zavrieť' => 'Кнопка Закрыть',
            'Rolovať nahor' => 'Прокрутить вверх',
            'Horné menu' => 'Верхнее меню',
            'Pätička' => 'Подвал',
            'Slovenská republika' => 'Словацкая Республика',
            'Zapísaná v OR OS Bratislava I,' => 'Зарегистрирована в торговом реестре окружного суда Братислава I,',
            'Oddiel: Sro, Vložka č. 182562/B' => 'Раздел s.r.o., № записи 182562/B',
            'Nie' => 'Нет',
            'Osobne vyzdvihnutie' => 'Самовывоз',
            'GLS doručenie na adresu' => 'GLS доставка на адрес',
            'SK Packeta Pick-up Point (Z-Point, Z-Box)' => 'SK Packeta пункт выдачи (Z-Point, Z-Box)',
            'GLS Balíkomat' => 'GLS Баликомат',
            'Platba pri doručení' => 'Оплата при получении',
            'Bankový prevod' => 'Банковский перевод',
            'K objednávke bude pripočítaný poplatok za dobierku vo výške 2,00 €.' => 'К заказу будет добавлена комиссия за наложенный платеж в размере 2,00 €.',
            'Zaplaťte priamym prevodom na náš bankový účet. Objednávka bude spracovaná po prijatí platby.' => 'Оплатите заказ прямым банковским переводом на наш счёт. Заказ будет обработан после поступления оплаты.',
            'Card <img' => 'Оплата картой <img',
        ]));
    }

    return $normalize_empty_cart_shell(strtr($html, [
        'Контакты' => 'Kontakt',
        'Мой аккаунт' => 'Môj účet',
        'Мой Аккаунт' => 'Môj účet',
        'Моя учётная запись' => 'Môj účet',
        'Вход / Регистрация' => 'Prihlásenie / Registrácia',
        'Регистрация' => 'Registrácia',
        'Забыли пароль?' => 'Zabudli ste heslo?',
        'Забыли свой пароль?' => 'Zabudli ste heslo?',
        'корзина' => 'košík',
        'Корзина' => 'Košík',
        'Кнопка Открыть' => 'Tlačidlo Otvoriť',
        'Кнопка Закрыть' => 'Tlačidlo Zavrieť',
        'Прокрутить вверх' => 'Rolovať nahor',
        'Верхнее меню' => 'Horné menu',
        'Подвал' => 'Pätička',
        'Словацкая Республика' => 'Slovenská republika',
        'Зарегистрирована в торговом реестре окружного суда Братислава I,' => 'Zapísaná v OR OS Bratislava I,',
        'Раздел s.r.o., № записи 182562/B' => 'Oddiel: Sro, Vložka č. 182562/B',
        'Нет' => 'Nie',
        'Имя' => 'Meno',
        'Фамилия' => 'Priezvisko',
        'Страна/регион' => 'Krajina/región',
        'Выберите страну/регион&hellip;' => 'Vyberte krajinu/región&hellip;',
        'Выберите страну/регион…' => 'Vyberte krajinu/región…',
        'Австрия' => 'Rakúsko',
        'Словакия' => 'Slovensko',
        'Адрес' => 'Adresa',
        'Номер дома и название улицы' => 'Číslo domu a názov ulice',
        'Почтовый индекс' => 'PSČ',
        'Населённый пункт' => 'Mesto',
        'Область / район' => 'Kraj / okres',
        '(необязательно)' => '(voliteľné)',
        'Телефон' => 'Telefón',
        'Доставка по другому адресу?' => 'Doručiť na inú adresu?',
        'Подытог' => 'Medzisúčet',
        'Итого' => 'Celkom',
        '(включая ' => '(vrátane ',
        ' НДС)' => ' DPH)',
        'Поскольку ваш браузер не поддерживает JavaScript или в нем он отключен, просим убедиться в том, что вы нажали кнопку <em>Обновить итог</em> перед регистрацией заказа. Иначе, есть риск неправильного подсчета стоимости.' => 'Keďže váš prehliadač nepodporuje JavaScript alebo je vypnutý, pred odoslaním objednávky sa uistite, že ste klikli na tlačidlo <em>Prepočítať spolu</em>. Inak hrozí nesprávny výpočet ceny.',
        'Обновить итог' => 'Prepočítať spolu',
        'Я прочитал(а) и принимаю ' => 'Prečítal(a) som si a súhlasím s ',
        'правила и условия' => 'obchodnými podmienkami',
        'обязательно' => 'povinné',
        'необязательно' => 'voliteľné',
        'Обновить страну/регион' => 'Aktualizovať krajinu/región',
        'Примечание к заказу' => 'Poznámka k objednávke',
        'Примечания к заказу, например, указания по доставке.' => 'Poznámky k objednávke, napríklad pokyny k doručeniu.',
        '"i18n_required_text":"\u043e\u0431\u044f\u0437\u0430\u0442\u0435\u043b\u044c\u043d\u043e"' => '"i18n_required_text":"povinné"',
        '"i18n_optional_text":"\u043d\u0435\u043e\u0431\u044f\u0437\u0430\u0442\u0435\u043b\u044c\u043d\u043e"' => '"i18n_optional_text":"voliteľné"',
        '</a> сайта</span>' => '</a></span>',
        'Card <img' => 'Platba kartou <img',
    ]));
}

function gls_normalize_front_page_html(string $html, string $lang): string {
    $html = preg_replace(
        '~<a[^>]*class="[^"]*skip-link[^"]*"[^>]*href="#maincontent"[^>]*>.*?</a>~su',
        $lang === 'ru'
            ? '<a class="screen-reader-text skip-link" href="#maincontent">Перейти к содержимому</a>'
            : '<a class="screen-reader-text skip-link" href="#maincontent">Preskočiť na obsah</a>',
        $html,
        1
    );

    $html = preg_replace('~<div class="bradcrumbs">.*?</div>\s*~su', '', $html, 1);
    $html = preg_replace('~<h1 class="vw-page-title">.*?</h1>\s*~su', '', $html, 1);

    $html = preg_replace_callback(
        '~(<div class="gc-card-name">)(.*?)(</div>)~su',
        static function($matches) use ($lang) {
            $text = html_entity_decode(wp_strip_all_tags((string) ($matches[2] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            return (string) ($matches[1] ?? '') . esc_html(gls_localize_bilingual_text($text, $lang)) . (string) ($matches[3] ?? '');
        },
        $html
    );

    $html = preg_replace(
        '~<script>\s*\(function\(\)\{\s*function patchCards\(\).*?\}\)\(\);\s*</script>~su',
        '',
        $html
    );

    return $html;
}

function gls_normalize_storefront_chrome_html(string $html, string $lang): string {
    $html = preg_replace_callback(
        '~(<li class="drp_dwn_menu[^"]*"[^>]*>\s*<a [^>]*>\s*)([^<]+)(\s*</a>)~su',
        static function($matches) use ($lang) {
            $text = html_entity_decode(trim((string) ($matches[2] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            return (string) ($matches[1] ?? '') . esc_html(gls_localize_bilingual_text($text, $lang)) . (string) ($matches[3] ?? '');
        },
        $html
    );

    $html = preg_replace_callback(
        '~(<span class="posted_in">.*?<a [^>]*rel="tag"[^>]*>)([^<]+)(</a>)~su',
        static function($matches) use ($lang) {
            $text = html_entity_decode(trim((string) ($matches[2] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            return (string) ($matches[1] ?? '') . esc_html(gls_localize_bilingual_text($text, $lang)) . (string) ($matches[3] ?? '');
        },
        $html
    );

    if ($lang === 'ru') {
        $html = str_replace('>Gastronom</h3>', '>Гастроном</h3>', $html);
        $html = str_replace('Slovenská republika', 'Словацкая Республика', $html);
        $html = str_replace('Zapísaná v OR OS Bratislava I,', 'Зарегистрирована в торговом реестре окружного суда Братислава I,', $html);
        $html = str_replace('Oddiel: Sro, Vložka č. 182562/B', 'Раздел s.r.o., № записи 182562/B', $html);
        $html = str_replace('>Ok</button>', '>Ок</button>', $html);
        $html = str_replace('aria-label="Ok"', 'aria-label="Ок"', $html);
        $html = str_replace('aria-label="Nie"', 'aria-label="Нет"', $html);
    } else {
        $html = str_replace('>Гастроном</h3>', '>Gastronom</h3>', $html);
        $html = str_replace('Словацкая Республика', 'Slovenská republika', $html);
        $html = str_replace('Зарегистрирована в торговом реестре окружного суда Братислава I,', 'Zapísaná v OR OS Bratislava I,', $html);
        $html = str_replace('Раздел s.r.o., № записи 182562/B', 'Oddiel: Sro, Vložka č. 182562/B', $html);
        $html = str_replace('>Ок</button>', '>Ok</button>', $html);
        $html = str_replace('aria-label="Ок"', 'aria-label="Ok"', $html);
        $html = str_replace('aria-label="Нет"', 'aria-label="Nie"', $html);
    }

    return $html;
}

function gls_localize_feed_link_titles(string $html, string $lang): string {
    return preg_replace_callback(
        '~(<link\s+rel="alternate"\s+type="application/rss\+xml"\s+title=")([^"]*)(")~iu',
        static function($matches) use ($lang) {
            $title = html_entity_decode((string) ($matches[2] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');

            $map = [
                'ru' => [
                    'RSS kanál: Gastronom »' => 'Гастроном » Лента',
                    'RSS kanál komentárov webu Gastronom »' => 'Гастроном » Лента комментариев',
                    'Feed Gastronom »' => 'Гастроном » Лента',
                    'Comments Feed Gastronom »' => 'Гастроном » Лента комментариев',
                ],
                'sk' => [
                    'Гастроном » Лента' => 'RSS kanál: Gastronom »',
                    'Гастроном » Лента комментариев' => 'RSS kanál komentárov webu Gastronom »',
                    'Feed Gastronom »' => 'RSS kanál: Gastronom »',
                    'Comments Feed Gastronom »' => 'RSS kanál komentárov webu Gastronom »',
                ],
            ];

            $localized = $map[$lang][$title] ?? gls_localize_bilingual_text($title, $lang);

            return (string) ($matches[1] ?? '') . esc_attr($localized) . (string) ($matches[3] ?? '');
        },
        $html
    );
}

function gls_strip_inactive_language_blocks(string $html, string $lang): string {
    if (strpos($html, 'gls-content-') === false) {
        return $html;
    }

    $inactive = $lang === 'ru' ? 'gls-content-sk' : 'gls-content-ru';

    if (!class_exists('DOMDocument')) {
        return $html;
    }

    $internal_errors = libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);

    if (!$loaded) {
        libxml_clear_errors();
        libxml_use_internal_errors($internal_errors);
        return $html;
    }

    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query(sprintf(
        '//*[contains(concat(" ", normalize-space(@class), " "), " %s ")]',
        $inactive
    ));

    if ($nodes instanceof DOMNodeList) {
        $to_remove = [];
        foreach ($nodes as $node) {
            $to_remove[] = $node;
        }

        foreach ($to_remove as $node) {
            if ($node->parentNode) {
                $node->parentNode->removeChild($node);
            }
        }
    }

    $style_nodes = $xpath->query('//style[contains(., ".gls-content-sk") or contains(., ".gls-content-ru")]');
    if ($style_nodes instanceof DOMNodeList) {
        $to_remove = [];
        foreach ($style_nodes as $node) {
            $to_remove[] = $node;
        }

        foreach ($to_remove as $node) {
            if ($node->parentNode) {
                $node->parentNode->removeChild($node);
            }
        }
    }

    $result = $dom->saveHTML();
    $result = preg_replace('/<\?xml[^>]*\?>\s*/u', '', (string) $result);
    $result = str_replace('<p></p>', '', (string) $result);

    libxml_clear_errors();
    libxml_use_internal_errors($internal_errors);

    return (string) $result;
}

add_action('template_redirect', function() {
    if (gls_is_sensitive_runtime_context()) {
        return;
    }

    ob_start(static function($html) {
        $lang = gls_server_lang();

        $html = gls_localize_feed_link_titles($html, $lang);

        if (gls_is_server_rendered_language_page()) {
            $html = gls_normalize_server_rendered_html($html, $lang);
        } else {
            $html = gls_normalize_storefront_chrome_html($html, $lang);
        }

        if (is_front_page()) {
            $html = gls_normalize_front_page_html($html, $lang);
        }

        $html = gls_strip_inactive_language_blocks($html, $lang);

        return $html;
    });
}, 5);

// Override locale for WooPayments scripts according to the current page language.
add_filter('wcpay_locale', function() {
    if (gls_is_sensitive_runtime_context()) {
        return get_locale();
    }

    return gls_server_lang() === 'ru' ? 'ru_RU' : 'sk_SK';
});

// Override locale in ALL WooPayments script tags (before Stripe init)
add_filter('script_loader_tag', function($tag, $handle) {
    if (gls_is_sensitive_runtime_context()) {
        return $tag;
    }

    if (strpos($handle, 'wcpay') !== false || strpos($handle, 'WCPAY') !== false) {
        if (gls_server_lang() === 'ru') {
            $tag = str_replace('"locale":"sk"', '"locale":"ru"', $tag);
        } else {
            $tag = str_replace('"locale":"ru"', '"locale":"sk"', $tag);
        }
    }
    return $tag;
}, 10, 2);

// Override locale in wp_localize_script data before it's printed
add_action('wp_enqueue_scripts', function() {
    add_action('wp_print_footer_scripts', function() {
        if (gls_is_sensitive_runtime_context()) {
            return;
        }

        if (!is_checkout()) return;
        global $wp_scripts;
        $target_locale = gls_server_lang() === 'ru' ? 'ru' : 'sk';
        $source_locale = $target_locale === 'ru' ? 'sk' : 'ru';
        // Replace locale in ALL registered scripts containing wcpay
        foreach ($wp_scripts->registered as $handle => $script) {
            if (strpos($handle, 'wcpay') === false) continue;
            // Replace in localized data (wp_localize_script)
            if (!empty($script->extra['data'])) {
                $wp_scripts->registered[$handle]->extra['data'] = str_replace(
                    ['"locale":"' . $source_locale . '"', '%22locale%22%3A%22' . $source_locale . '%22'],
                    ['"locale":"' . $target_locale . '"', '%22locale%22%3A%22' . $target_locale . '%22'],
                    $script->extra['data']
                );
            }
            // Replace in inline scripts (wp_add_inline_script)
            if (!empty($script->extra['before'])) {
                foreach ($script->extra['before'] as $i => $code) {
                    $wp_scripts->registered[$handle]->extra['before'][$i] = str_replace(
                        ['"locale":"' . $source_locale . '"', '%22locale%22%3A%22' . $source_locale . '%22'],
                        ['"locale":"' . $target_locale . '"', '%22locale%22%3A%22' . $target_locale . '%22'],
                        $code
                    );
                }
            }
            if (!empty($script->extra['after'])) {
                foreach ($script->extra['after'] as $i => $code) {
                    $wp_scripts->registered[$handle]->extra['after'][$i] = str_replace(
                        ['"locale":"' . $source_locale . '"', '%22locale%22%3A%22' . $source_locale . '%22'],
                        ['"locale":"' . $target_locale . '"', '%22locale%22%3A%22' . $target_locale . '%22'],
                        $code
                    );
                }
            }
        }
    }, 1);
}, 999);

// Force EUR symbol to always be € regardless of WP locale (ru_RU shows ₽ by default)
add_filter('woocommerce_currency_symbol', function($symbol, $currency) {
    if ($currency === 'EUR') {
        return '€';
    }
    return $symbol;
}, 999, 2);

function gls_enqueue_scripts() {
    wp_enqueue_style('gls-style', plugin_dir_url(__FILE__) . 'gls-style.css', [], '6.24');
}
add_action('wp_enqueue_scripts', 'gls_enqueue_scripts');

function gls_add_switcher() {
    if (function_exists('rslc_render_switcher')) {
        echo rslc_render_switcher(gls_current_lang_code());
        return;
    }

    $current_lang = gls_current_lang_code();
    $ru_url = esc_url(gls_switcher_url('ru'));
    $sk_url = esc_url(gls_switcher_url('sk'));
    $ru_class = 'gls-btn gls-btn-ru' . ($current_lang === 'ru' ? ' active' : '');
    $sk_class = 'gls-btn gls-btn-sk' . ($current_lang === 'sk' ? ' active' : '');

    echo '<div id="gls-switcher" class="gls-switcher">'
        . '<a class="' . esc_attr($ru_class) . '" data-lang="ru" href="' . $ru_url . '" title="Русский">RU</a>'
        . '<a class="' . esc_attr($sk_class) . '" data-lang="sk" href="' . $sk_url . '" title="Slovenčina">SK</a>'
        . '</div>';
}
add_action('wp_body_open', 'gls_add_switcher');
