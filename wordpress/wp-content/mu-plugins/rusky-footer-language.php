<?php
/**
 * Plugin Name: Rusky Footer Language
 * Description: Normalizes footer contact and legal text per storefront language.
 */

if (!defined('ABSPATH')) {
    exit;
}

function rfl_current_lang(): string {
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

function rfl_footer_replacements(string $lang): array {
    if ($lang === 'ru') {
        return [
            'Zámocká 5, 811 01 Bratislava' => 'Замоцка 5, 811 01 Братислава',
            'Zámocká 5' => 'Замоцка 5',
            'Bratislava' => 'Братислава',
            'Vchod z ulice Palisády' => 'Вход со&nbsp;стороны улицы&nbsp;Палисады',
            'Zapísaná v OR OS Bratislava I,' => 'Зарегистрирована в торговом реестре окружного суда Братислава&nbsp;I,',
            'Oddiel: Sro, Vložka č. 182562/B' => 'Раздел s.r.o., № записи 182562/B',
            'Slovenská republika' => 'Словацкая Республика',
        ];
    }

    return [
        'Замоцка 5, 811 01 Братислава' => 'Zámocká 5, 811 01 Bratislava',
        'Замоцка 5' => 'Zámocká 5',
        'Братислава' => 'Bratislava',
        'Вход со&nbsp;стороны улицы&nbsp;Палисады' => 'Vchod z ulice Palisády',
        'Вход со стороны улицы Палисады' => 'Vchod z ulice Palisády',
        'Зарегистрирована в торговом реестре окружного суда Братислава&nbsp;I,' => 'Zapísaná v OR OS Bratislava I,',
        'Зарегистрирована в торговом реестре окружного суда Братислава I,' => 'Zapísaná v OR OS Bratislava I,',
        'Раздел s.r.o., № записи 182562/B' => 'Oddiel: Sro, Vložka č. 182562/B',
        'Словацкая Республика' => 'Slovenská republika',
    ];
}

function rfl_normalize_footer_html(string $html): string {
    if ($html === '') {
        return $html;
    }

    if (strpos($html, 'Zámocká') === false
        && strpos($html, 'Замоцка') === false
        && strpos($html, 'Palisády') === false
        && strpos($html, 'Палисады') === false
        && strpos($html, 'Bratislava') === false
        && strpos($html, 'Братислава') === false
    ) {
        return $html;
    }

    return strtr($html, rfl_footer_replacements(rfl_current_lang()));
}

add_filter('render_block', function($block_content, $block = []) {
    if (!is_string($block_content) || $block_content === '' || is_admin()) {
        return $block_content;
    }

    return rfl_normalize_footer_html($block_content);
}, 120, 2);
