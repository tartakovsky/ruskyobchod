<?php
/**
 * Plugin Name: Gastronom Language Switcher
 * Description: Простой переключатель RU/SK для двуязычных названий товаров + стили сайта
 * Version: 6.24
 * Author: Gastronom
 */

if (!defined('ABSPATH')) exit;

function gls_is_supported_lang(string $lang): bool {
    return $lang === 'ru' || $lang === 'sk';
}

function gls_current_lang_code() {
    if (function_exists('rslc_current_lang')) {
        return rslc_current_lang();
    }

    if (is_admin() && !wp_doing_ajax()) {
        return 'sk';
    }

    if (isset($_GET['lang'])) {
        $lang = sanitize_key(wp_unslash($_GET['lang']));
        if (gls_is_supported_lang($lang)) {
            return $lang;
        }
    }

    if (isset($_COOKIE['gastronom_lang'])) {
        $lang = sanitize_key(wp_unslash($_COOKIE['gastronom_lang']));
        if (gls_is_supported_lang($lang)) {
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
    return gls_is_current_lang_ru() ? gls_brand_name_ru() : gls_brand_name_sk();
}

function gls_brand_description() {
    return gls_is_current_lang_ru()
        ? gls_brand_description_ru()
        : gls_brand_description_sk();
}

function gls_is_current_lang_ru(): bool {
    return gls_current_lang_code() === 'ru';
}

function gls_brand_name_ru(): string {
    return 'Гастроном';
}

function gls_brand_name_sk(): string {
    return 'Gastronom';
}

function gls_brand_description_ru(): string {
    return 'русский магазин продуктов в Братиславе';
}

function gls_brand_description_sk(): string {
    return 'obchod s ruskými potravinami v Bratislave';
}

function gls_localize_bilingual_text(string $text, ?string $lang = null): string {
    $value = trim(html_entity_decode(wp_strip_all_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    if ($value === '') {
        return trim($text);
    }

    $lang = gls_resolve_text_lang($lang);

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

function gls_resolve_text_lang(?string $lang): string {
    return is_string($lang) && gls_is_supported_lang($lang) ? $lang : gls_current_lang_code();
}

function gls_replace_text_pairs(string $value, array $pairs): string {
    return str_replace(array_keys($pairs), array_values($pairs), $value);
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
            'Ok' => 'Ок',
            'No' => 'Нет',
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
            'Ок' => 'Ok',
            'Нет' => 'No',
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

function gls_cookie_notice_source_args(array $options): array {
    $lang = gls_server_lang();

    if ($lang === 'ru') {
        $options['accept_text'] = 'Ок';
        $options['refuse_text'] = 'Нет';
        $options['aria_label'] = 'Уведомление о cookie';

        return $options;
    }

    $options['accept_text'] = 'Ok';
    $options['refuse_text'] = 'Nie';
    $options['aria_label'] = 'Cookie Notice';

    return $options;
}

function gls_apply_phrase_translation(string $translated, string $text, string $lang, callable $translator): string {
    $translated = $translator($translated, $lang);
    $source_translation = $translator($text, $lang);

    return $source_translation === $text ? $translated : $source_translation;
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

function gls_server_locale_code(): string {
    return gls_server_lang() === 'ru' ? 'ru' : 'sk';
}

function gls_server_wp_locale(): string {
    return gls_server_locale_code() === 'ru' ? 'ru_RU' : 'sk_SK';
}

// Keep Stripe/WooPayments aligned with the current server-side page language.
add_filter('wcpay_elements_options', function($options) {
    $options['locale'] = gls_server_locale_code();
    return $options;
});
add_filter('wc_stripe_elements_options', function($options) {
    $options['locale'] = gls_server_locale_code();
    return $options;
});

function gls_server_lang(): string {
    if (function_exists('rslc_current_lang')) {
        return rslc_current_lang();
    }

    $query_lang = isset($_GET['lang']) ? sanitize_key(wp_unslash($_GET['lang'])) : '';
    if (gls_is_supported_lang($query_lang)) {
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
    if (!gls_is_supported_lang($query_lang)) {
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
    if (!gls_is_supported_lang($lang)) {
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

    return gls_server_wp_locale();
}

add_filter('locale', 'gls_frontend_locale', 20);
add_filter('determine_locale', 'gls_frontend_locale', 20);

add_filter('gettext', function($translated, $text, $domain) {
    if (gls_is_sensitive_runtime_context()) {
        return $translated;
    }

    $lang = gls_server_lang();

    if ((function_exists('is_account_page') && is_account_page()) || (function_exists('is_checkout_pay_page') && is_checkout_pay_page())) {
        $translated = gls_apply_phrase_translation($translated, $text, $lang, 'gls_translate_account_checkout_phrase');
    }

    if ((function_exists('is_cart') && is_cart()) || (function_exists('is_checkout') && is_checkout())) {
        $translated = gls_apply_phrase_translation($translated, $text, $lang, 'gls_translate_account_checkout_phrase');
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
        $translated = gls_apply_phrase_translation($translated, $text, $lang, 'gls_translate_theme_chrome_phrase');
    }

    if ($domain === 'woocommerce') {
        $translated = gls_apply_phrase_translation($translated, $text, $lang, 'gls_translate_woocommerce_storefront_phrase');
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

add_filter('render_block', function($block_content, $block = []) {
    if (!is_string($block_content) || $block_content === '') {
        return $block_content;
    }

    return gls_normalize_footer_block_content($block_content);
}, 20, 2);

add_filter('cn_cookie_notice_args', 'gls_cookie_notice_source_args', 20);

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
        return gls_normalize_account_title_text($title, $lang);
    }

    return gls_localize_bilingual_text($title, $lang);
}, 20, 2);

add_filter('pre_get_document_title', function($title) {
    if (is_front_page()) {
        return gls_brand_name() . ' — ' . gls_brand_description();
    }

    $lang = gls_server_lang();
    $title = gls_normalize_common_public_title_text($title, $lang);
    $title = gls_normalize_checkout_public_title_text($title, $lang, false);

    return $title;
}, 20);

function gls_normalize_common_public_title_text(string $title, string $lang): string {
    if ($lang === 'ru') {
        $title = gls_normalize_brand_title_text($title, $lang);
        $title = gls_normalize_account_title_text($title, $lang);
        return $title;
    }

    $title = gls_normalize_brand_title_text($title, $lang);
    $title = gls_normalize_account_title_text($title, $lang);

    return $title;
}

function gls_normalize_brand_title_text(string $title, string $lang): string {
    if ($lang === 'ru') {
        return str_replace('Gastronom', 'Гастроном', $title);
    }

    return str_replace('Гастроном', 'Gastronom', $title);
}

function gls_normalize_account_title_text(string $title, string $lang): string {
    if ($lang === 'ru') {
        return gls_replace_text_pairs($title, [
            'Môj účet' => 'Мой аккаунт',
            'Môj Účet' => 'Мой аккаунт',
            'Моя учётная запись' => 'Мой аккаунт',
            'Мой Аккаунт' => 'Мой аккаунт',
        ]);
    }

    return gls_replace_text_pairs($title, [
        'Мой аккаунт' => 'Môj účet',
        'Мой Аккаунт' => 'Môj účet',
        'Моя учётная запись' => 'Môj účet',
        'Môj Účet' => 'Môj účet',
    ]);
}

function gls_checkout_public_title_pairs(string $lang, bool $include_order_title): array {
    if ($lang === 'ru') {
        return [
            'Objednávka' => 'Оплатить заказ',
        ];
    }

    $pairs = [
        'Оплатить заказ' => 'Zaplatiť objednávku',
    ];

    if ($include_order_title) {
        $pairs['Заказ'] = 'Objednávka';
    }

    return $pairs;
}

function gls_normalize_checkout_public_title_text(string $title, string $lang, bool $include_order_title): string {
    return gls_replace_text_pairs($title, gls_checkout_public_title_pairs($lang, $include_order_title));
}

function gls_normalize_public_title(string $title): string {
    $lang = gls_server_lang();
    $title = gls_normalize_common_public_title_text($title, $lang);
    return gls_normalize_checkout_public_title_text($title, $lang, true);
}

add_filter('wpseo_title', 'gls_normalize_public_title', 20);
add_filter('wpseo_opengraph_title', 'gls_normalize_public_title', 20);
add_filter('wpseo_twitter_title', 'gls_normalize_public_title', 20);
add_filter('rank_math/frontend/title', 'gls_normalize_public_title', 20);
add_filter('rank_math/opengraph/facebook/title', 'gls_normalize_public_title', 20);
add_filter('rank_math/opengraph/twitter/title', 'gls_normalize_public_title', 20);

function gls_skip_link_replacement(string $lang): string {
    return $lang === 'ru'
        ? '<a class="screen-reader-text skip-link" href="#maincontent">Перейти к содержимому</a>'
        : '<a class="screen-reader-text skip-link" href="#maincontent">Preskočiť na obsah</a>';
}

function gls_normalize_skip_link_html(string $html, string $lang): string {
    return (string) preg_replace(
        '~<a[^>]*class="[^"]*skip-link[^"]*"[^>]*href="#maincontent"[^>]*>.*?</a>~su',
        gls_skip_link_replacement($lang),
        $html,
        1
    );
}

function gls_normalize_legal_company_text_html(string $html, string $lang): string {
    if ($lang === 'ru') {
        return strtr($html, [
            'Slovenská republika' => 'Словацкая Республика',
            'Zapísaná v OR OS Bratislava I,' => 'Зарегистрирована в торговом реестре окружного суда Братислава I,',
            'Oddiel: Sro, Vložka č. 182562/B' => 'Раздел s.r.o., № записи 182562/B',
        ]);
    }

    return strtr($html, [
        'Словацкая Республика' => 'Slovenská republika',
        'Зарегистрирована в торговом реестре окружного суда Братислава I,' => 'Zapísaná v OR OS Bratislava I,',
        'Раздел s.r.o., № записи 182562/B' => 'Oddiel: Sro, Vložka č. 182562/B',
    ]);
}

function gls_cookie_notice_button_pairs(string $lang): array {
    if ($lang === 'ru') {
        return [
            '>Ok</button>' => '>Ок</button>',
            'aria-label="Ok"' => 'aria-label="Ок"',
            'aria-label="Nie"' => 'aria-label="Нет"',
        ];
    }

    return [
        '>Ок</button>' => '>Ok</button>',
        'aria-label="Ок"' => 'aria-label="Ok"',
        'aria-label="Нет"' => 'aria-label="Nie"',
    ];
}

function gls_normalize_cookie_notice_button_html(string $html, string $lang): string {
    return gls_replace_text_pairs($html, gls_cookie_notice_button_pairs($lang));
}

function gls_footer_brand_heading_pairs(string $lang): array {
    if ($lang === 'ru') {
        return [
            '>Gastronom</h3>' => '>Гастроном</h3>',
        ];
    }

    return [
        '>Гастроном</h3>' => '>Gastronom</h3>',
    ];
}

function gls_normalize_footer_brand_heading_html(string $html, string $lang): string {
    return gls_replace_text_pairs($html, gls_footer_brand_heading_pairs($lang));
}

function gls_footer_block_needs_brand_normalization(string $content): bool {
    return strpos($content, '>Gastronom</h3>') !== false || strpos($content, '>Гастроном</h3>') !== false;
}

function gls_footer_block_needs_legal_normalization(string $content): bool {
    return strpos($content, 'Slovenská republika') !== false
        || strpos($content, 'Словацкая Республика') !== false
        || strpos($content, 'Zapísaná v OR OS Bratislava I,') !== false
        || strpos($content, 'Зарегистрирована в торговом реестре окружного суда Братислава I,') !== false
        || strpos($content, 'Oddiel: Sro, Vložka č. 182562/B') !== false
        || strpos($content, 'Раздел s.r.o., № записи 182562/B') !== false;
}

function gls_normalize_footer_block_content(string $content): string {
    if (gls_is_sensitive_runtime_context()) {
        return $content;
    }

    $lang = gls_server_lang();

    if (gls_footer_block_needs_brand_normalization($content)) {
        $content = gls_normalize_footer_brand_heading_html($content, $lang);
    }

    if (gls_footer_block_needs_legal_normalization($content)) {
        $content = gls_normalize_legal_company_text_html($content, $lang);
    }

    return $content;
}

function gls_footer_brand_heading_tag_replacement(string $lang): string {
    return $lang === 'ru'
        ? '<h3$1>Гастроном</h3>'
        : '<h3$1>Gastronom</h3>';
}

function gls_normalize_footer_brand_heading_tag_html(string $html, string $lang): string {
    return (string) preg_replace(
        '~<h3([^>]*)>\s*(Гастроном|Gastronom)\s*</h3>~su',
        gls_footer_brand_heading_tag_replacement($lang),
        $html
    );
}

function gls_ok_button_replacement(string $lang): string {
    return $lang === 'ru'
        ? '<button$1>Ок</button>'
        : '<button$1>Ok</button>';
}

function gls_normalize_ok_button_html(string $html, string $lang): string {
    return (string) preg_replace(
        '~<button([^>]*)>\s*(Ок|Ok)\s*</button>~su',
        gls_ok_button_replacement($lang),
        $html
    );
}

function gls_strip_storefront_title_shell_html(string $html): string {
    $html = preg_replace('~<div class="bradcrumbs">.*?</div>\s*~su', '', $html, 1);
    $html = preg_replace('~<h1 class="vw-page-title">.*?</h1>\s*~su', '', $html, 1);

    return (string) $html;
}

function gls_localize_html_text_segment(array $matches, string $lang, bool $strip_tags = false): string {
    $raw = (string) ($matches[2] ?? '');
    $text = $strip_tags ? wp_strip_all_tags($raw) : trim($raw);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    return (string) ($matches[1] ?? '') . esc_html(gls_localize_bilingual_text($text, $lang)) . (string) ($matches[3] ?? '');
}

function gls_localize_html_text_pattern(string $html, string $pattern, string $lang, bool $strip_tags = false): string {
    return (string) preg_replace_callback(
        $pattern,
        static function($matches) use ($lang, $strip_tags) {
            return gls_localize_html_text_segment($matches, $lang, $strip_tags);
        },
        $html
    );
}

function gls_checkout_order_title_h1_replacement(string $lang): string {
    return $lang === 'ru'
        ? '<h1 class="vw-page-title">Оформление заказа</h1>'
        : '<h1 class="vw-page-title">Objednávka</h1>';
}

function gls_checkout_order_title_span_replacement(string $lang): string {
    return $lang === 'ru'
        ? '$1Оформление заказа$2'
        : '$1Objednávka$3';
}

function gls_checkout_order_title_prefix_pairs(): array {
    return [
        '>Гастроном</a> <span> Objednávka' => '>Гастроном</a> <span> Оформление заказа',
    ];
}

function gls_checkout_order_title_span_pattern(string $lang): string {
    return $lang === 'ru'
        ? '~(<div class="bradcrumbs">.*?<span>\s*)Objednávka(\s*</span>)~su'
        : '~(<div class="bradcrumbs">.*?<span>\s*)(Оформление заказа|Заказ)(\s*</span>)~su';
}

function gls_checkout_order_title_h1_pattern(string $lang): string {
    return $lang === 'ru'
        ? '~<h1 class="vw-page-title">\s*Objednávka\s*</h1>~u'
        : '~<h1 class="vw-page-title">\s*(Оформление заказа|Заказ)\s*</h1>~u';
}

function gls_normalize_checkout_order_title_span_html(string $html, string $lang): string {
    return (string) preg_replace(
        gls_checkout_order_title_span_pattern($lang),
        gls_checkout_order_title_span_replacement($lang),
        $html,
        1
    );
}

function gls_normalize_checkout_order_title_shell_html(string $html, string $lang): string {
    if ($lang === 'ru') {
        $html = gls_replace_text_pairs($html, gls_checkout_order_title_prefix_pairs());
        $html = gls_normalize_checkout_order_title_span_html($html, $lang);
        return (string) preg_replace(
            gls_checkout_order_title_h1_pattern($lang),
            gls_checkout_order_title_h1_replacement($lang),
            $html,
            1
        );
    }

    $html = gls_normalize_checkout_order_title_span_html($html, $lang);

    return (string) preg_replace(
        gls_checkout_order_title_h1_pattern($lang),
        gls_checkout_order_title_h1_replacement($lang),
        $html,
        1
    );
}

function gls_normalize_server_rendered_html(string $html, string $lang): string {
    $normalize_empty_cart_shell = static function(string $value) use ($lang): string {
        $value = gls_normalize_skip_link_html($value, $lang);
        $value = gls_normalize_footer_brand_heading_tag_html($value, $lang);
        $value = gls_normalize_ok_button_html($value, $lang);

        $value = gls_normalize_checkout_order_title_shell_html($value, $lang);

        if (strpos($value, 'wc-empty-cart-message') === false) {
            return $value;
        }

        return gls_strip_storefront_title_shell_html($value);
    };

    if ($lang === 'ru') {
        return $normalize_empty_cart_shell(gls_normalize_legal_company_text_html(strtr($html, [
            'Osobne vyzdvihnutie' => 'Самовывоз',
            'GLS doručenie na adresu' => 'GLS доставка на адрес',
            'SK Packeta Pick-up Point (Z-Point, Z-Box)' => 'SK Packeta пункт выдачи (Z-Point, Z-Box)',
            'GLS Balíkomat' => 'GLS Баликомат',
            'Zaplaťte priamym prevodom na náš bankový účet. Objednávka bude spracovaná po prijatí platby.' => 'Оплатите заказ прямым банковским переводом на наш счёт. Заказ будет обработан после поступления оплаты.',
            'Card <img' => 'Оплата картой <img',
        ]), $lang));
    }

    return $normalize_empty_cart_shell(gls_normalize_legal_company_text_html(strtr($html, [
        'Контакты' => 'Kontakt',
        'Забыли свой пароль?' => 'Zabudli ste heslo?',
        'Кнопка Открыть' => 'Tlačidlo Otvoriť',
        'Кнопка Закрыть' => 'Tlačidlo Zavrieť',
        'Прокрутить вверх' => 'Rolovať nahor',
        'Верхнее меню' => 'Horné menu',
        'Подвал' => 'Pätička',
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
    ]), $lang));
}

function gls_normalize_front_page_html(string $html, string $lang): string {
    $html = gls_normalize_skip_link_html($html, $lang);
    $html = gls_strip_storefront_title_shell_html($html);

    $html = gls_localize_html_text_pattern(
        $html,
        '~(<div class="gc-card-name">)(.*?)(</div>)~su',
        $lang,
        true
    );

    $html = preg_replace(
        '~<script>\s*\(function\(\)\{\s*function patchCards\(\).*?\}\)\(\);\s*</script>~su',
        '',
        $html
    );

    return $html;
}

function gls_normalize_storefront_chrome_html(string $html, string $lang): string {
    $html = gls_localize_html_text_pattern(
        $html,
        '~(<li class="drp_dwn_menu[^"]*"[^>]*>\s*<a [^>]*>\s*)([^<]+)(\s*</a>)~su',
        $lang
    );

    $html = gls_localize_html_text_pattern(
        $html,
        '~(<span class="posted_in">.*?<a [^>]*rel="tag"[^>]*>)([^<]+)(</a>)~su',
        $lang
    );

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

function gls_replace_encoded_locale_markers(string $value, string $source_locale, string $target_locale): string {
    return str_replace(
        ['"locale":"' . $source_locale . '"', '%22locale%22%3A%22' . $source_locale . '%22'],
        ['"locale":"' . $target_locale . '"', '%22locale%22%3A%22' . $target_locale . '%22'],
        $value
    );
}

function gls_wcpay_locale_pair(): array {
    $target_locale = gls_server_locale_code();

    return [$target_locale, $target_locale === 'ru' ? 'sk' : 'ru'];
}

function gls_wcpay_wp_locale(): string {
    return gls_server_wp_locale();
}

function gls_is_wcpay_handle(string $handle): bool {
    return strpos($handle, 'wcpay') !== false || strpos($handle, 'WCPAY') !== false;
}

function gls_replace_encoded_locale_markers_in_list(array $items, string $source_locale, string $target_locale): array {
    foreach ($items as $index => $item) {
        $items[$index] = gls_replace_encoded_locale_markers((string) $item, $source_locale, $target_locale);
    }

    return $items;
}

function gls_apply_wcpay_locale_to_script_extra($script, string $source_locale, string $target_locale): array {
    $extra = is_object($script) && isset($script->extra) && is_array($script->extra) ? $script->extra : [];

    if (!empty($extra['data'])) {
        $extra['data'] = gls_replace_encoded_locale_markers((string) $extra['data'], $source_locale, $target_locale);
    }

    if (!empty($extra['before'])) {
        $extra['before'] = gls_replace_encoded_locale_markers_in_list((array) $extra['before'], $source_locale, $target_locale);
    }

    if (!empty($extra['after'])) {
        $extra['after'] = gls_replace_encoded_locale_markers_in_list((array) $extra['after'], $source_locale, $target_locale);
    }

    return $extra;
}

function gls_currency_symbol_override(string $symbol, string $currency): string {
    if ($currency === 'EUR') {
        return '€';
    }

    return $symbol;
}

function gls_filter_wcpay_locale() {
    if (gls_is_sensitive_runtime_context()) {
        return get_locale();
    }

    return gls_wcpay_wp_locale();
}

function gls_filter_woocommerce_currency_symbol(string $symbol, string $currency): string {
    return gls_currency_symbol_override($symbol, $currency);
}

function gls_apply_wcpay_locale_to_footer_scripts(): void {
    if (gls_is_sensitive_runtime_context()) {
        return;
    }

    if (!is_checkout()) {
        return;
    }

    global $wp_scripts;
    [$target_locale, $source_locale] = gls_wcpay_locale_pair();

    foreach ($wp_scripts->registered as $handle => $script) {
        if (!gls_is_wcpay_handle($handle)) {
            continue;
        }

        $wp_scripts->registered[$handle]->extra = gls_apply_wcpay_locale_to_script_extra(
            $script,
            $source_locale,
            $target_locale
        );
    }
}

function gls_enqueue_wcpay_locale_footer_patch(): void {
    add_action('wp_print_footer_scripts', 'gls_apply_wcpay_locale_to_footer_scripts', 1);
}

function gls_filter_template_output_html(string $html): string {
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

    return gls_strip_inactive_language_blocks($html, $lang);
}

function gls_start_template_output_buffer(): void {
    if (gls_is_sensitive_runtime_context()) {
        return;
    }

    ob_start('gls_filter_template_output_html');
}

function gls_filter_script_loader_tag_for_wcpay_locale(string $tag, string $handle): string {
    if (gls_is_sensitive_runtime_context()) {
        return $tag;
    }

    if (gls_is_wcpay_handle($handle)) {
        [$target_locale, $source_locale] = gls_wcpay_locale_pair();
        $tag = gls_replace_encoded_locale_markers($tag, $source_locale, $target_locale);
    }

    return $tag;
}

add_action('template_redirect', 'gls_start_template_output_buffer', 5);

// Override locale for WooPayments scripts according to the current page language.
add_filter('wcpay_locale', 'gls_filter_wcpay_locale');

// Override locale in ALL WooPayments script tags (before Stripe init)
add_filter('script_loader_tag', 'gls_filter_script_loader_tag_for_wcpay_locale', 10, 2);

// Override locale in wp_localize_script data before it's printed
add_action('wp_enqueue_scripts', 'gls_enqueue_wcpay_locale_footer_patch', 999);

// Force EUR symbol to always be € regardless of WP locale (ru_RU shows ₽ by default)
add_filter('woocommerce_currency_symbol', 'gls_filter_woocommerce_currency_symbol', 999, 2);

function gls_asset_version(): string {
    return '6.24';
}

function gls_style_handle(): string {
    return 'gls-style';
}

function gls_style_asset_basename(): string {
    return 'gls-style.css';
}

function gls_style_asset_url(): string {
    return plugin_dir_url(__FILE__) . gls_style_asset_basename();
}

function gls_switcher_link_title(string $lang): string {
    return $lang === 'ru' ? 'Русский' : 'Slovenčina';
}

function gls_switcher_label(string $lang): string {
    return strtoupper($lang);
}

function gls_switcher_button_class_prefix(): string {
    return 'gls-btn gls-btn-';
}

function gls_switcher_button_lang_suffix(string $lang): string {
    return $lang;
}

function gls_switcher_active_class_suffix(bool $is_active): string {
    return $is_active ? ' active' : '';
}

function gls_switcher_button_class(string $lang, string $current_lang): string {
    return gls_switcher_button_class_prefix() . gls_switcher_button_lang_suffix($lang) . gls_switcher_active_class_suffix($current_lang === $lang);
}

function gls_switcher_urls(): array {
    return [
        'ru' => esc_url(gls_switcher_url('ru')),
        'sk' => esc_url(gls_switcher_url('sk')),
    ];
}

function gls_switcher_data_lang(string $lang): string {
    return $lang;
}

function gls_switcher_langs(): array {
    return ['ru', 'sk'];
}

function gls_render_internal_switcher_link(string $lang, string $href, string $class): string {
    return '<a class="' . esc_attr($class) . '" data-lang="' . esc_attr(gls_switcher_link_data_lang_html($lang)) . '" href="' . $href . '" title="' . esc_attr(gls_switcher_link_title_html($lang)) . '">' . gls_switcher_link_label_html($lang) . '</a>';
}

function gls_switcher_link_data_lang_html(string $lang): string {
    return gls_switcher_data_lang($lang);
}

function gls_switcher_link_title_html(string $lang): string {
    return gls_switcher_link_title($lang);
}

function gls_switcher_link_label_html(string $lang): string {
    return esc_html(gls_switcher_label($lang));
}

function gls_switcher_container_open_html(): string {
    return '<div ' . gls_switcher_container_attributes() . '>';
}

function gls_switcher_container_close_html(): string {
    return '</div>';
}

function gls_switcher_container_class(): string {
    return 'gls-switcher';
}

function gls_switcher_container_id(): string {
    return 'gls-switcher';
}

function gls_switcher_container_attributes(): string {
    return 'id="' . gls_switcher_container_id() . '" class="' . gls_switcher_container_class() . '"';
}

function gls_enqueue_scripts() {
    wp_enqueue_style(gls_style_handle(), gls_style_asset_url(), [], gls_asset_version());
}
add_action('wp_enqueue_scripts', 'gls_enqueue_scripts');

function gls_render_internal_switcher_html(string $current_lang): string {
    $langs = gls_switcher_langs();
    $urls = gls_switcher_urls();
    $ru_class = gls_switcher_button_class($langs[0], $current_lang);
    $sk_class = gls_switcher_button_class($langs[1], $current_lang);

    return gls_switcher_container_open_html()
        . gls_render_internal_switcher_link($langs[0], $urls[$langs[0]], $ru_class)
        . gls_render_internal_switcher_link($langs[1], $urls[$langs[1]], $sk_class)
        . gls_switcher_container_close_html();
}

function gls_render_switcher_html(string $current_lang): string {
    if (function_exists('rslc_render_switcher')) {
        return (string) rslc_render_switcher($current_lang);
    }

    return gls_render_internal_switcher_html($current_lang);
}

function gls_output_switcher_html(string $current_lang): void {
    echo gls_render_switcher_html($current_lang);
}

function gls_switcher_current_lang(): string {
    return gls_current_lang_code();
}

function gls_add_switcher() {
    $current_lang = gls_switcher_current_lang();

    gls_output_switcher_html($current_lang);
}
add_action('wp_body_open', 'gls_add_switcher');
