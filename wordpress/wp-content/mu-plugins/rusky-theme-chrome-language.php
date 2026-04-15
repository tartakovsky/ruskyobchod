<?php
/**
 * Plugin Name: Rusky Theme Chrome Language
 * Description: Localizes small theme chrome strings on the public storefront.
 */

if (!defined('ABSPATH')) {
    exit;
}

function rtcl_current_lang(): string {
    if (function_exists('gls_server_lang')) {
        return gls_server_lang() === 'ru' ? 'ru' : 'sk';
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

add_filter('gettext', function($translation, $text, $domain) {
    if (is_admin() || $domain !== 'food-grocery-store') {
        return $translation;
    }

    $lang = rtcl_current_lang();
    $map = [
        'ru' => [
            'Login / Register' => 'Вход / Регистрация',
            'My Account' => 'Мой аккаунт',
            'shopping cart' => 'корзина',
            'Open Button' => 'Кнопка Открыть',
            'Close Button' => 'Кнопка Закрыть',
            'Skip to content' => 'Перейти к содержимому',
            'All Categories' => 'Все категории',
        ],
        'sk' => [
            'Login / Register' => 'Prihlásenie / Registrácia',
            'My Account' => 'Môj účet',
            'shopping cart' => 'košík',
            'Open Button' => 'Otvoriť menu',
            'Close Button' => 'Zavrieť menu',
            'Skip to content' => 'Preskočiť na obsah',
            'All Categories' => 'Všetky kategórie',
        ],
    ];

    return $map[$lang][$text] ?? $translation;
}, 120, 3);
